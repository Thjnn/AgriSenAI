<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Lot;
use App\Services\AuditChain;
use App\Services\LotWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/** Trang ho so cong khai — nguoi mua quet QR, khong can dang nhap, chi doc. */
class PublicLotController extends Controller
{
    public function lookup(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('ma')));

        if ($code === '') {
            return view('public.lookup');
        }

        return Lot::where('code', $code)->where('status', 'published')->exists()
            ? redirect()->route('public.show', $code)
            : view('public.lookup', ['code' => $code, 'notFound' => true]);
    }

    public function show(Request $request, Lot $lot, AuditChain $audit)
    {
        abort_unless($lot->isPublished(), 404);

        // Dem luot xem cua khach (nguoi mua); nhan vien dang nhap tu xem thi khong tinh.
        if (! $request->user()) {
            DB::table('lot_scans')->insert(['lot_id' => $lot->id, 'from_qr' => $request->boolean('qr'), 'created_at' => now()]);
        }

        $lot->load(['photos', 'auditLogs.actor', 'creator']);
        $published = $lot->auditLogs->where('action', 'published')->last();

        return view('public.show', [
            'lot' => $lot,
            'verify' => $audit->verify($lot),
            // Hien thi theo ban DA CONG BO (nam trong chuoi bam), khong theo DB hien tai.
            'labels' => $published->data()['labels'] ?? [],
            'published' => $published,
            'feedback' => $lot->auditLogs->where('action', 'buyer_feedback')->values(),
            'correction' => ($c = $lot->correction())
                ? $c + ['new_published' => Lot::where('code', $c['new_code'])->where('status', 'published')->exists()]
                : null,
        ]);
    }

    public function qr(Lot $lot)
    {
        abort_unless($lot->isPublished(), 404);

        $svg = QrCode::format('svg')->size(300)->margin(1)->generate(route('public.show', ['lot' => $lot, 'qr' => 1]));

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }

    public function feedback(Request $request, Lot $lot, LotWorkflow $workflow)
    {
        abort_unless($lot->isPublished(), 404);

        $data = $request->validate([
            'result' => ['required', Rule::in(array_keys(AuditLog::FEEDBACK))],
            'note' => ['nullable', 'required_if:result,khong_dung', 'string', 'max:500'],
            'receiver' => ['nullable', 'string', 'max:100'],
            'anh' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'result.required' => 'Chọn hàng nhận đúng hay không đúng hồ sơ.',
            'note.required_if' => 'Ghi rõ điểm không đúng để điểm thu gom xử lý.',
            'anh.max' => 'Ảnh không được lớn hơn 10 MB.',
        ]);

        $workflow->addBuyerFeedback($lot, $data['result'], $data['note'] ?? null, $data['receiver'] ?? null, $request->file('anh')?->getRealPath());

        return back()->with('ok', 'Đã ghi phản hồi vào nhật ký hồ sơ. Cảm ơn bạn!');
    }
}
