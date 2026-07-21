<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\TraceabilityService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TraceController extends Controller
{
    public function __construct(private readonly TraceabilityService $svc)
    {
    }

    public function show(Product $product)
    {
        $product->load(['farmer.farmerProfile', 'category', 'primaryImage', 'traceLogs']);

        $assessment = $product->assessments()->latest('assessed_at')->first();
        abort_if(! $assessment, 404, 'Sản phẩm chưa được AI đánh giá');

        $verification = $this->svc->verify($assessment);

        return view('trace.show', compact('product', 'assessment', 'verification'));
    }

    public function qr(Product $product)
    {
        $svg = QrCode::format('svg')->size(300)->margin(1)
            ->generate(route('trace.show', $product->qr_code));

        return response($svg)->header('Content-Type', 'image/svg+xml');
    }
}
