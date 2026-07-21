<?php
namespace App\Http\Controllers;

use App\Models\AiQualityAssessment;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::certified()
            ->with(['primaryImage', 'category', 'farmer.farmerProfile', 'latestAssessment'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->category_id, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->min_score, fn ($q, $s) => $q->whereHas(
                'latestAssessment', fn ($a) => $a->where('quality_score', '>=', $s)
            ))
            ->when($request->sort === 'score_desc', fn ($q) => $q->orderByDesc(
                AiQualityAssessment::select('quality_score')
                    ->whereColumn('product_id', 'products.id')
                    ->latest('assessed_at')->limit(1)
            ))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->latest('products.id')
            ->paginate(12)
            ->withQueryString();

        return view('marketplace.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
