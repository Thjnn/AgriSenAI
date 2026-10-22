<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Services\AuditChain;
use Illuminate\Http\Request;
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

    public function show(Lot $lot, AuditChain $audit)
    {
        abort_unless($lot->isPublished(), 404);

        $lot->load(['photos', 'auditLogs.actor']);
        $published = $lot->auditLogs->where('action', 'published')->last();

        return view('public.show', [
            'lot' => $lot,
            'verify' => $audit->verify($lot),
            // Hien thi theo ban DA CONG BO (nam trong chuoi bam), khong theo DB hien tai.
            'labels' => $published->data()['labels'] ?? [],
            'published' => $published,
        ]);
    }

    public function qr(Lot $lot)
    {
        abort_unless($lot->isPublished(), 404);

        $svg = QrCode::format('svg')->size(300)->margin(1)->generate(route('public.show', $lot));

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
