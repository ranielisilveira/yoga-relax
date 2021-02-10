<?php

namespace App\Http\Controllers;

use App\Models\Admin\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function topMenu()
    {
        return Category::doesnthave('category')
            ->with('categories')
            ->orderBy('order')
            ->get();
    }

    public function category($id)
    {
        return Category::where('id', $id)
            ->with([
                'categories' => function ($categories) {
                    return $categories->with([
                        'categories' => function ($categories) {
                            $categories->with('medias')->orderBy('order');
                        },
                        'medias'
                    ])->orderBy('order');
                }
            ])
            ->orderBy('order')
            ->first();
    }

    public function categories($id)
    {
        return Category::whereHas('category', function ($category) use ($id) {
            return $category->where('id', $id);
        })
            ->with('category')
            ->orderBy('order')
            ->get();
    }
}
