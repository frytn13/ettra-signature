<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductAttributeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $colors = Color::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
            ->orderBy('name')
            ->get();

        $sizes = Size::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.product-attributes.index', compact('colors', 'sizes', 'search'));
    }
}
