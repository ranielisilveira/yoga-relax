<?php

namespace App\Http\Controllers;

use App\Models\Admin\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function topMenu()
    {
        return Category::whereHas('categories')
            ->with('categories')
            ->orderBy('name')
            ->get();
    }

    public function category($id)
    {
        return Category::where('id', $id)
            ->with([
                'categories' => function ($categories) {
                    return $categories->with(['medias']);
                }
            ])
            ->orderBy('name')
            ->first();
    }

    public function categories($id)
    {
        return Category::whereHas('category', function ($category) use ($id) {
            return $category->where('id', $id);
        })
            ->with('category')
            ->orderBy('name')
            ->get();
    }
}
