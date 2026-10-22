<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeLot;
use App\Models\Lot;
use App\Models\LotPhoto;
use App\Services\AuditChain;
use App\Services\LotWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/** Man hinh cua nhan vien diem thu gom: tao lo -> anh -> AI -> xac nhan -> cong bo. */
class LotController extends Controller
{
    public function __construct(private readonly LotWorkflow $workflow) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');

        $lots = Lot::with('creator')
            ->withCount('photos')
            ->unless($user->isAdmin(), fn ($q) => $q->whereHas('creator', fn ($c) => $c->where('org_name', $user->org_name)))
            ->when(array_key_exists((string) $status, Lot::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('lots.index', compact('lots', 'status'));
    }

    public function create()
    {
        return view('lots.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier' => ['required', 'string', 'max:150'],
            'variety' => ['nullable', 'string', 'max:100'],
            'purpose' => ['required', Rule::in(array_keys(config('agrisenai.muc_dich_ban')))],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'supplier.required' => 'Nhập nhà vườn / nguồn cung.',
            'purpose.required' => 'Chọn mục đích bán — AI cần biết để không coi vỏ xanh là lỗi.',
            'weight_kg.gt' => 'Khối lượng phải lớn hơn 0.',
            'harvest_date.before_or_equal' => 'Ngày thu hoạch không được ở tương lai.',
        ]);

        $lot = $this->workflow->create($data, $request->user());

        return redirect()->route('lots.show', $lot)->with('ok', "Đã tạo lô {$lot->code}. Bước tiếp: chụp ảnh.");
    }

    public function show(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);

        $lot->load(['photos', 'latestSuggestion', 'auditLogs.actor', 'creator']);

        return view('lots.show', [
            'lot' => $lot,
            'labels' => $lot->currentLabels(),
            'sugg' => $lot->latestSuggestion,
            'verify' => $lot->isPublished() ? app(AuditChain::class)->verify($lot) : null,
        ]);
    }

    public function storePhoto(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);

        $request->validate([
            'anh' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'view' => ['required', Rule::in(array_keys(LotPhoto::VIEWS))],
        ], [
            'anh.required' => 'Chọn hoặc chụp một ảnh.',
            'anh.mimes' => 'Chỉ nhận ảnh JPG, PNG hoặc WEBP.',
            'anh.max' => 'Ảnh không được lớn hơn 10 MB.',
        ]);

        $photo = $this->workflow->addPhoto($lot, $request->file('anh')->getRealPath(), $request->input('view'), $request->user());

        return $photo->isUsable()
            ? back()->with('ok', 'Đã thêm ảnh.')
            : back()->with('warn', 'Ảnh đã lưu nhưng KHÔNG đạt chất lượng nên AI sẽ bỏ qua: '.implode(' ', $photo->quality['issues']));
    }

    public function analyze(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);
        abort_if($lot->isPublished(), 422, 'Lô đã công bố.');

        if (! $lot->photos()->get()->contains->isUsable()) {
            return back()->with('warn', 'Cần ít nhất một ảnh đạt chất lượng trước khi gọi AI.');
        }

        $lot->update(['status' => 'analyzing']);
        AnalyzeLot::dispatchAfterResponse($lot->id);

        return back()->with('ok', 'AI đang phân tích (thường 10–30 giây). Bạn có thể tự nhập, không cần chờ.');
    }

    /** Trang show poll endpoint nay khi lo dang `analyzing`. */
    public function analysisStatus(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);

        return response()->json(['status' => $lot->status]);
    }

    public function confirm(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);

        $this->workflow->confirm($lot, (array) $request->input('items', []), $request->user());

        return redirect()->route('lots.show', $lot)->with('ok', 'Đã xác nhận hồ sơ. Kiểm tra lại rồi bấm Công bố.');
    }

    public function publish(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);

        $this->workflow->publish($lot, $request->user());

        return redirect()->route('lots.show', $lot)->with('ok', 'Đã công bố. In tem QR dán lên thùng hàng.');
    }

    /** Tem QR 50x30 mm de in dan thung. */
    public function label(Request $request, Lot $lot)
    {
        $this->authorizeLot($request, $lot);
        abort_unless($lot->isPublished(), 404);

        $qr = QrCode::format('svg')->size(220)->margin(0)->generate(route('public.show', $lot));

        return view('lots.label', compact('lot', 'qr'));
    }

    private function authorizeLot(Request $request, Lot $lot): void
    {
        abort_unless($request->user()->canAccessLot($lot), 403);
    }
}
