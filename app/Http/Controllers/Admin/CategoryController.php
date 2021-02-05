<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumColors;
use App\Helpers\ValidateJsonLangKey;
use App\Http\Controllers\Controller;
use App\Models\Admin\Category;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::withTrashed()
            ->with([
                'category',
                'categories'
            ])
            ->withCount('categories')
            ->orderBy('deleted_at')
            ->orderBy('name');

        if ($request->search) {
            $categories->where('name', 'LIKE', "%{$request->search}%");
        }

        return $categories->paginate((int)($request->length ?? 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['category_id'] = $request->category_id == 'null' ?  null : $request->category_id;
        $this->validate($request, [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|json|unique:categories,name',
            'file' => 'nullable|file|mimes:png,jpeg,jpg,gif',
            'color' => [
                'required',
                Rule::in(EnumColors::COLORS_ARRAY)
            ]
        ], []);

        try {

            ValidateJsonLangKey::validate($request->name);

            if ($request->hasFile('file')) {
                $request['image'] = $request->file('file')->storePublicly('categories');
            }

            Category::create($request->all());

            return response([
                'message' => trans('messages.created_success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return Category::with([
                'category',
                'categories'
            ])->findOrFail($id);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request['category_id'] = $request->category_id == 'null' ?  null : $request->category_id;
        $this->validate($request, [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|json|unique:categories,name,' . $id . ',id',
            'file' => 'nullable|file|mimes:png,jpeg,jpg,gif',
            'color' => [
                'required',
                Rule::in(EnumColors::COLORS_ARRAY)
            ]
        ], []);

        try {

            ValidateJsonLangKey::validate($request->name);

            if ($request->hasFile('file')) {
                $request['image'] = $request->file('file')->store('categories');
            }

            $category = Category::findOrFail($id);
            $category->update($request->all());

            return response([
                'message' => trans('messages.updated_success'),
                'categories' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $category = Category::findOrFail($id);

            if ($category->categories->count()) {
                throw new Exception(trans('messages.category_delete_not_allowed'));
            }

            if ($category->medias->count()) {
                throw new Exception(trans('messages.category_delete_not_allowed'));
            }

            $category->forceDelete();

            return response([
                'message' => trans('messages.deleted_success'),
                'categories' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {

            $category = Category::withTrashed()->findOrFail($id);
            $category->restore();

            return response([
                'message' => trans('messages.restore_success'),
                'categories' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function colors()
    {
        $enumColors = EnumColors::COLORS_ARRAY;
        $colors = [];

        foreach ($enumColors as $color) {
            $colors[] = [
                'value' => $color,
                'text' => $color,
            ];
        }

        return $colors;
    }

    public function arrayList()
    {
        $DBcategories = Category::orderBy('name')->get();
        $categories = [];

        foreach ($DBcategories as $category) {
            $categories[] = [
                'value' => $category->id,
                'text' => $category->name->{auth()->user()->language},
            ];
        }

        return $categories;
    }

    public function groupedList()
    {
        $DBcategories = Category::with('category')
            ->whereHas('category')
            ->orderBy('name')
            ->get()
            ->sortBy('category.name')
            ->sortBy('name');
        $categories = [];

        foreach ($DBcategories as $category) {
            $parentCategory = $category->category
                ? $category->category->name->{auth()->user()->language} . ' > '
                : '';

            $categories[] = [
                'value' => $category->id,
                'text' => $parentCategory . $category->name->{auth()->user()->language},
            ];
        }

        return $categories;
    }
}
