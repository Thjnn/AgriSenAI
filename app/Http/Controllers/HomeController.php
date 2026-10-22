<?php

namespace App\Http\Controllers;

use App\Models\Lot;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'recent' => Lot::where('status', 'published')->with(['photos', 'creator'])->latest('published_at')->take(3)->get(),
        ]);
    }
}
