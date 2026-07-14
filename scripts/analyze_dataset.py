#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Khao sat dataset anh nong san cho AgriSenAi.

Chay lai duoc nhieu lan, khong sua doi du lieu goc (chi doc).

Cach dung:
    python scripts/analyze_dataset.py \
        --dataset cam="dataset/Orange Dataset" \
        --dataset xoai="dataset/Mango_Dataset" \
        --out reports

Ket qua:
    reports/dataset_report.md      - bao cao tong hop (tieng Viet)
    reports/preview_<ten>.png      - luoi anh mau de xem bang mat
"""
from __future__ import annotations

import argparse
import collections
import hashlib
import json
import random
import sys
from dataclasses import dataclass, field
from pathlib import Path

try:
    from PIL import Image, ImageDraw, ImageFont
except ImportError:
    sys.exit("Thieu Pillow. Cai bang: pip install Pillow")

IMAGE_EXTS = {".jpg", ".jpeg", ".png", ".webp", ".bmp", ".gif", ".tif", ".tiff"}
LABEL_EXTS = {".csv", ".json", ".txt", ".xml", ".yaml", ".yml"}

# Kich thuoc luoi anh mau
GRID_COLS = 6
THUMB = 220
PAD = 6
CAPTION_H = 22


@dataclass
class ImageInfo:
    path: Path
    group: str              # nhom = thu muc con truc tiep chua anh (vd: train/fresh)
    width: int = 0
    height: int = 0
    fmt: str = ""
    mode: str = ""
    size_bytes: int = 0
    md5: str = ""
    dhash: str = ""
    bg_lum: float = -1.0     # do sang nen (4 goc), -1 = chua tinh
    error: str = ""

    @property
    def ext_mismatch(self) -> bool:
        """Duoi file khong khop noi dung that (vd: .png nhung ben trong la JPEG)."""
        if not self.fmt:
            return False
        ext = self.path.suffix.lower().lstrip(".")
        alias = {"jpg": "jpeg", "tif": "tiff"}
        return alias.get(ext, ext) != self.fmt.lower()


@dataclass
class DatasetReport:
    name: str
    root: Path
    images: list[ImageInfo] = field(default_factory=list)
    label_files: list[Path] = field(default_factory=list)
    corrupt: list[ImageInfo] = field(default_factory=list)
    exact_dupes: dict[str, list[Path]] = field(default_factory=dict)
    near_dupes: dict[str, list[Path]] = field(default_factory=dict)
    yolo_summary: dict = field(default_factory=dict)


# ---------------------------------------------------------------- ham tien ich

def dhash(img: Image.Image, hash_size: int = 8) -> str:
    """Perceptual hash (difference hash) de tim anh gan trung nhau."""
    small = img.convert("L").resize((hash_size + 1, hash_size), Image.LANCZOS)
    px = list(small.tobytes())
    bits = []
    for row in range(hash_size):
        base = row * (hash_size + 1)
        for col in range(hash_size):
            bits.append(px[base + col] < px[base + col + 1])
    return "".join("1" if b else "0" for b in bits)


def human_size(n: int) -> str:
    for unit in ("B", "KB", "MB", "GB"):
        if n < 1024:
            return f"{n:.1f} {unit}"
        n /= 1024
    return f"{n:.1f} TB"


def scan_images(root: Path) -> tuple[list[ImageInfo], list[Path]]:
    """Duyet toan bo cay thu muc, doc metadata tung anh, gom file nhan."""
    images: list[ImageInfo] = []
    labels: list[Path] = []

    for p in sorted(root.rglob("*")):
        if not p.is_file():
            continue
        ext = p.suffix.lower()

        if ext in LABEL_EXTS:
            labels.append(p)
            continue
        if ext not in IMAGE_EXTS:
            continue

        # Nhom = duong dan tuong doi cua thu muc cha
        group = p.parent.relative_to(root).as_posix() or "."
        info = ImageInfo(path=p, group=group, size_bytes=p.stat().st_size)

        try:
            raw = p.read_bytes()
            info.md5 = hashlib.md5(raw).hexdigest()
            with Image.open(p) as im:
                im.verify()                     # phat hien file hong
            with Image.open(p) as im:           # verify() lam hong con tro -> mo lai
                info.width, info.height = im.size
                info.fmt = im.format or ""
                info.mode = im.mode
                info.dhash = dhash(im)
                info.bg_lum = bg_luminance(im)
        except Exception as exc:                # noqa: BLE001
            info.error = f"{type(exc).__name__}: {exc}"

        images.append(info)

    return images, labels


def bg_luminance(img: Image.Image) -> float:
    """Do sang trung binh cua 4 goc anh — dai dien cho NEN.

    Dung de phat hien 'ro ri nen' (background leak): neu do sang nen tuong quan
    chat voi nhan lop, model se hoc nen thay vi hoc qua.
    """
    g = img.convert("L")
    w, h = g.size
    k = max(2, min(w, h) // 20)
    px: list[int] = []
    for x0, y0 in ((0, 0), (w - k, 0), (0, h - k), (w - k, h - k)):
        px += list(g.crop((x0, y0, x0 + k, y0 + k)).tobytes())
    return sum(px) / len(px)


def find_dupes(images: list[ImageInfo]) -> tuple[dict, dict]:
    """Trung lap tuyet doi (md5) va gan trung (dhash)."""
    by_md5 = collections.defaultdict(list)
    by_dhash = collections.defaultdict(list)
    for i in images:
        if i.error:
            continue
        by_md5[i.md5].append(i.path)
        by_dhash[i.dhash].append(i.path)

    exact = {k: v for k, v in by_md5.items() if len(v) > 1}
    # gan trung nhung khong phai trung tuyet doi
    exact_paths = {p for v in exact.values() for p in v}
    near = {
        k: v for k, v in by_dhash.items()
        if len(v) > 1 and not set(v) <= exact_paths
    }
    return exact, near


def summarize_yolo(label_files: list[Path]) -> dict:
    """Neu nhan la YOLO (dong: <class> <cx> <cy> <w> <h>), thong ke class + so box."""
    txts = [p for p in label_files if p.suffix.lower() == ".txt"]
    if not txts:
        return {}

    class_count = collections.Counter()
    boxes_per_image = []
    box_areas = []
    max_area_per_image = []      # box LON NHAT moi anh -> qua to bao nhieu trong khung
    parsed, malformed = 0, 0

    for p in txts:
        try:
            lines = [l.strip() for l in p.read_text(errors="replace").splitlines() if l.strip()]
        except Exception:  # noqa: BLE001
            malformed += 1
            continue
        n = 0
        areas_here: list[float] = []
        for line in lines:
            parts = line.split()
            if len(parts) != 5:
                malformed += 1
                continue
            try:
                cid = int(float(parts[0]))
                _, _, w, h = (float(x) for x in parts[1:])
            except ValueError:
                malformed += 1
                continue
            class_count[cid] += 1
            box_areas.append(w * h)
            areas_here.append(w * h)
            n += 1
        boxes_per_image.append(n)
        if areas_here:
            max_area_per_image.append(max(areas_here))
        parsed += 1

    if not parsed:
        return {}

    box_areas.sort()
    max_area_per_image.sort()

    def pct(q: float) -> float:
        if not box_areas:
            return 0.0
        return box_areas[min(int(q * len(box_areas)), len(box_areas) - 1)]

    n_max = len(max_area_per_image) or 1
    med_max = max_area_per_image[n_max // 2] if max_area_per_image else 0.0
    ge10 = sum(1 for a in max_area_per_image if a >= 0.10)
    ge20 = sum(1 for a in max_area_per_image if a >= 0.20)

    return {
        "qua_lon_nhat_trung_vi": round(med_max * 100, 2),
        "qua_lon_nhat_max": round((max_area_per_image[-1] if max_area_per_image else 0) * 100, 2),
        "so_anh_qua_chiem_tren_10pc": ge10,
        "so_anh_qua_chiem_tren_20pc": ge20,
        "ti_le_anh_qua_tren_20pc": round(ge20 / n_max * 100, 2),
        "so_file_nhan": len(txts),
        "so_file_doc_duoc": parsed,
        "so_dong_loi": malformed,
        "tong_so_box": sum(class_count.values()),
        "class_ids": dict(sorted(class_count.items())),
        "box_moi_anh_tb": round(sum(boxes_per_image) / len(boxes_per_image), 2),
        "box_moi_anh_max": max(boxes_per_image) if boxes_per_image else 0,
        # dien tich box / dien tich anh, theo ti le
        "dien_tich_box_p10": round(pct(0.10) * 100, 2),
        "dien_tich_box_p50": round(pct(0.50) * 100, 2),
        "dien_tich_box_p90": round(pct(0.90) * 100, 2),
    }


def make_preview(rep: DatasetReport, out_png: Path, per_group: int = 3, seed: int = 42) -> None:
    """Xuat luoi anh mau: lay <per_group> anh ngau nhien moi nhom."""
    rng = random.Random(seed)
    by_group = collections.defaultdict(list)
    for i in rep.images:
        if not i.error:
            by_group[i.group].append(i)

    picked: list[ImageInfo] = []
    for group in sorted(by_group):
        pool = by_group[group]
        picked.extend(rng.sample(pool, min(per_group, len(pool))))

    if not picked:
        return

    cols = min(GRID_COLS, len(picked))
    rows = (len(picked) + cols - 1) // cols
    cell_w = THUMB + PAD
    cell_h = THUMB + PAD + CAPTION_H
    canvas = Image.new("RGB", (cols * cell_w + PAD, rows * cell_h + PAD), "white")
    draw = ImageDraw.Draw(canvas)
    try:
        font = ImageFont.truetype("arial.ttf", 11)
    except OSError:
        font = ImageFont.load_default()

    for idx, info in enumerate(picked):
        r, c = divmod(idx, cols)
        x = PAD + c * cell_w
        y = PAD + r * cell_h
        try:
            with Image.open(info.path) as im:
                im = im.convert("RGB")
                im.thumbnail((THUMB, THUMB), Image.LANCZOS)
                canvas.paste(im, (x + (THUMB - im.width) // 2, y + (THUMB - im.height) // 2))
        except Exception:  # noqa: BLE001
            draw.rectangle([x, y, x + THUMB, y + THUMB], outline="red")

        caption = f"{info.group} | {info.path.name}"
        if len(caption) > 40:
            caption = caption[:37] + "..."
        draw.text((x, y + THUMB + 4), caption, fill="black", font=font)
        draw.text((x, y + THUMB + 13), f"{info.width}x{info.height}", fill="gray", font=font)

    out_png.parent.mkdir(parents=True, exist_ok=True)
    canvas.save(out_png, quality=90)
    print(f"  -> luoi anh mau: {out_png}")


# ---------------------------------------------------------------- bao cao

def render_tree(rep: DatasetReport) -> list[str]:
    """Cay thu muc kem so luong anh moi nhom."""
    counts = collections.Counter(i.group for i in rep.images)
    lines = [f"{rep.root.as_posix()}/"]
    seen: set[str] = set()
    for group in sorted(counts):
        if group == ".":
            lines.append(f"  |- (goc)  ({counts[group]} anh)")
            continue
        parts = group.split("/")
        # in ca cac thu muc cha (chung khong chua anh truc tiep nen khong co trong counts)
        for depth, part in enumerate(parts):
            prefix = "/".join(parts[:depth + 1])
            if prefix in seen:
                continue
            seen.add(prefix)
            n = counts.get(prefix)
            suffix = f"  ({n} anh)" if n else ""
            lines.append(f"{'  ' * (depth + 1)}|- {part}/{suffix}")
    return lines


def render_report(rep: DatasetReport) -> list[str]:
    L: list[str] = []
    ok = [i for i in rep.images if not i.error]
    total_bytes = sum(i.size_bytes for i in rep.images)

    L.append(f"## Dataset `{rep.name}`")
    L.append("")
    L.append(f"- **Duong dan:** `{rep.root.as_posix()}`")
    L.append(f"- **Tong so anh:** {len(rep.images)}")
    L.append(f"- **Anh doc duoc:** {len(ok)} | **Anh loi/hong:** {len(rep.corrupt)}")
    L.append(f"- **Tong dung luong:** {human_size(total_bytes)}")
    L.append("")

    L.append("### Cay thu muc")
    L.append("")
    L.append("```")
    L.extend(render_tree(rep))
    L.append("```")
    L.append("")

    # Bang so luong theo nhom
    counts = collections.Counter(i.group for i in rep.images)
    L.append("### So luong anh moi nhom")
    L.append("")
    L.append("| Nhom (thu muc) | So anh | Ti le |")
    L.append("|---|---:|---:|")
    for group, n in sorted(counts.items(), key=lambda kv: -kv[1]):
        L.append(f"| `{group}` | {n} | {n / max(len(rep.images), 1) * 100:.1f}% |")
    L.append("")

    # Kich thuoc
    if ok:
        dims = collections.Counter((i.width, i.height) for i in ok)
        widths = sorted(i.width for i in ok)
        heights = sorted(i.height for i in ok)
        L.append("### Kich thuoc anh")
        L.append("")
        L.append(f"- Rong: min {widths[0]}, trung vi {widths[len(widths)//2]}, max {widths[-1]}")
        L.append(f"- Cao:  min {heights[0]}, trung vi {heights[len(heights)//2]}, max {heights[-1]}")
        L.append(f"- So kich thuoc khac nhau: {len(dims)}")
        L.append("")
        L.append("| Kich thuoc | So anh |")
        L.append("|---|---:|")
        for (w, h), n in dims.most_common(8):
            L.append(f"| {w}x{h} | {n} |")
        L.append("")

        # Dinh dang + mode
        fmts = collections.Counter(i.fmt for i in ok)
        modes = collections.Counter(i.mode for i in ok)
        sizes = sorted(i.size_bytes for i in ok)
        L.append("### Dinh dang & dung luong")
        L.append("")
        L.append(f"- Dinh dang: {', '.join(f'{k} ({v})' for k, v in fmts.most_common())}")
        L.append(f"- Che do mau: {', '.join(f'{k} ({v})' for k, v in modes.most_common())}")
        L.append(f"- Dung luong file: min {human_size(sizes[0])}, "
                 f"trung vi {human_size(sizes[len(sizes)//2])}, max {human_size(sizes[-1])}")
        L.append("")

    # Ro ri nen / dinh dang theo nhom  -> phat hien confound
    if ok:
        L.append("### Kiem tra RO RI NEN (background leak) theo nhom")
        L.append("")
        L.append("*Neu dinh dang, kich thuoc hoac do sang nen tuong quan chat voi nhan lop,")
        L.append("model se hoc NEN thay vi hoc QUA. Day la loi chi mang tinh chan doan.*")
        L.append("")
        L.append("| Nhom | n | Dinh dang | So kich thuoc | Do sang nen (trung vi) | Sai duoi file |")
        L.append("|---|---:|---|---:|---:|---:|")
        by_group: dict[str, list[ImageInfo]] = collections.defaultdict(list)
        for i in ok:
            by_group[i.group].append(i)
        for group in sorted(by_group):
            g = by_group[group]
            fmts = collections.Counter(i.fmt for i in g)
            fmt_s = ", ".join(f"{k} ({v})" for k, v in fmts.most_common())
            n_sizes = len({(i.width, i.height) for i in g})
            lums = sorted(i.bg_lum for i in g if i.bg_lum >= 0)
            med = lums[len(lums) // 2] if lums else 0.0
            n_mm = sum(1 for i in g if i.ext_mismatch)
            L.append(f"| `{group}` | {len(g)} | {fmt_s} | {n_sizes} | {med:.0f}/255 | {n_mm} |")
        L.append("")

    # Anh loi
    L.append("### Anh loi / hong")
    L.append("")
    if rep.corrupt:
        for i in rep.corrupt[:20]:
            L.append(f"- `{i.path.name}` ({i.group}) — {i.error}")
        if len(rep.corrupt) > 20:
            L.append(f"- ... va {len(rep.corrupt) - 20} anh khac")
    else:
        L.append("Khong co anh loi.")
    L.append("")

    # Trung lap
    L.append("### Trung lap")
    L.append("")
    n_exact = sum(len(v) - 1 for v in rep.exact_dupes.values())
    n_near = sum(len(v) - 1 for v in rep.near_dupes.values())
    L.append(f"- **Trung tuyet doi (md5):** {len(rep.exact_dupes)} nhom, {n_exact} anh thua")
    L.append(f"- **Gan trung (perceptual hash):** {len(rep.near_dupes)} nhom, {n_near} anh thua")
    L.append("")
    if rep.exact_dupes:
        L.append("Vi du nhom trung tuyet doi:")
        L.append("")
        for paths in list(rep.exact_dupes.values())[:5]:
            names = ", ".join(f"`{p.parent.name}/{p.name}`" for p in paths[:4])
            L.append(f"- {names}")
        L.append("")

    # File nhan
    L.append("### File nhan di kem")
    L.append("")
    if rep.label_files:
        by_ext = collections.Counter(p.suffix.lower() for p in rep.label_files)
        L.append(f"- Tim thay {len(rep.label_files)} file nhan: "
                 f"{', '.join(f'{k} ({v})' for k, v in by_ext.most_common())}")
        L.append("")
        for p in rep.label_files[:3]:
            L.append(f"Vi du `{p.name}`:")
            L.append("")
            L.append("```")
            try:
                head = p.read_text(errors="replace").splitlines()[:5]
                L.extend(head or ["(rong)"])
            except Exception as exc:  # noqa: BLE001
                L.append(f"(khong doc duoc: {exc})")
            L.append("```")
            L.append("")
    else:
        L.append("**Khong tim thay file nhan nao.** Nhan chi nam o TEN THU MUC.")
        L.append("")

    # YOLO
    if rep.yolo_summary:
        y = rep.yolo_summary
        L.append("### Phan tich nhan YOLO")
        L.append("")
        L.append(f"- So file nhan: {y['so_file_nhan']} (doc duoc {y['so_file_doc_duoc']}, "
                 f"dong loi {y['so_dong_loi']})")
        L.append(f"- Tong so bounding box: {y['tong_so_box']}")
        L.append(f"- **Class ID xuat hien:** {y['class_ids']}  "
                 f"<- chi ra so lop that su co trong nhan")
        L.append(f"- So box moi anh: trung binh {y['box_moi_anh_tb']}, cao nhat {y['box_moi_anh_max']}")
        L.append(f"- Dien tich box / dien tich anh: "
                 f"P10 {y['dien_tich_box_p10']}%, "
                 f"P50 {y['dien_tich_box_p50']}%, "
                 f"P90 {y['dien_tich_box_p90']}%")
        L.append("")
        L.append("**Qua chiem bao nhieu khung hinh?** (quyet dinh co cham duoc khuyet tat be mat khong)")
        L.append("")
        L.append(f"- Qua LON NHAT moi anh, trung vi: **{y['qua_lon_nhat_trung_vi']}%** khung hinh "
                 f"(lon nhat toan bo: {y['qua_lon_nhat_max']}%)")
        L.append(f"- So anh co qua chiem >= 10% khung: {y['so_anh_qua_chiem_tren_10pc']}")
        L.append(f"- So anh co qua chiem >= 20% khung: {y['so_anh_qua_chiem_tren_20pc']} "
                 f"({y['ti_le_anh_qua_tren_20pc']}%)")
        L.append("")

    L.append(f"### Anh mau")
    L.append("")
    L.append(f"Xem `preview_{rep.name}.png` trong cung thu muc `reports/`.")
    L.append("")
    L.append("---")
    L.append("")
    return L


# ---------------------------------------------------------------- main

def analyze(name: str, root: Path) -> DatasetReport:
    print(f"[{name}] dang quet {root} ...")
    images, labels = scan_images(root)
    rep = DatasetReport(name=name, root=root, images=images, label_files=labels)
    rep.corrupt = [i for i in images if i.error]
    rep.exact_dupes, rep.near_dupes = find_dupes(images)
    rep.yolo_summary = summarize_yolo(labels)
    print(f"  {len(images)} anh, {len(labels)} file nhan, {len(rep.corrupt)} anh loi")
    return rep


def main() -> int:
    ap = argparse.ArgumentParser(description="Khao sat dataset anh nong san AgriSenAi")
    ap.add_argument("--dataset", action="append", required=True, metavar="TEN=DUONG_DAN",
                    help="Vi du: --dataset cam='dataset/Orange Dataset'")
    ap.add_argument("--out", default="reports", help="Thu muc xuat ket qua")
    ap.add_argument("--per-group", type=int, default=3,
                    help="So anh mau lay moi nhom cho luoi preview")
    args = ap.parse_args()

    out_dir = Path(args.out)
    out_dir.mkdir(parents=True, exist_ok=True)

    reports: list[DatasetReport] = []
    for spec in args.dataset:
        if "=" not in spec:
            sys.exit(f"Sai dinh dang --dataset: {spec!r} (can TEN=DUONG_DAN)")
        name, _, path = spec.partition("=")
        root = Path(path)
        if not root.is_dir():
            sys.exit(f"Khong thay thu muc: {root}")
        rep = analyze(name.strip(), root)
        make_preview(rep, out_dir / f"preview_{rep.name}.png", per_group=args.per_group)
        reports.append(rep)

    # Bao cao markdown
    md: list[str] = [
        "# Bao cao khao sat dataset — AgriSenAi",
        "",
        "*Sinh tu dong boi `scripts/analyze_dataset.py`. Khong sua tay.*",
        "",
        "## Tom tat",
        "",
        "| Dataset | So anh | Nhom | File nhan | Anh loi | Trung lap |",
        "|---|---:|---:|---:|---:|---:|",
    ]
    for r in reports:
        n_groups = len({i.group for i in r.images})
        n_dupe = sum(len(v) - 1 for v in r.exact_dupes.values())
        md.append(f"| `{r.name}` | {len(r.images)} | {n_groups} | {len(r.label_files)} | "
                  f"{len(r.corrupt)} | {n_dupe} |")
    md.append("")
    md.append("---")
    md.append("")
    for r in reports:
        md.extend(render_report(r))

    report_path = out_dir / "dataset_report.md"
    report_path.write_text("\n".join(md), encoding="utf-8")
    print(f"\nBao cao: {report_path}")

    # Ban JSON de buoc sau doc lai
    json_path = out_dir / "dataset_report.json"
    json_path.write_text(json.dumps({
        r.name: {
            "root": r.root.as_posix(),
            "tong_anh": len(r.images),
            "nhom": dict(collections.Counter(i.group for i in r.images)),
            "anh_loi": len(r.corrupt),
            "trung_tuyet_doi": sum(len(v) - 1 for v in r.exact_dupes.values()),
            "gan_trung": sum(len(v) - 1 for v in r.near_dupes.values()),
            "yolo": r.yolo_summary,
        } for r in reports
    }, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"JSON:    {json_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
