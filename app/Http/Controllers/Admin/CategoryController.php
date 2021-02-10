<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumColors;
use App\Helpers\ValidateJsonLangKey;
use App\Http\Controllers\Controller;
use App\Models\Admin\Category;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('order');

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

            if ($request->order !== $category->order && $request->order > 0) {
                $this->sortCategories($category, $request->order);
            }

            unset($request->order);

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
        $categories = Category::with([
            'categories' => function($categories){
                $categories->with('categories');
            }
        ])
            ->orderBy('order')
            ->get();

            $return = [];

            foreach ($categories as $category) {
                $return[] = [
                'value' => $category->id,
                'text' => $category->name->{auth()->user()->language},
            ];

            foreach ($category->categories as $category2nd) {
                $return[] = [
                    'value' => $category2nd->id,
                    'text' => $category->name->{auth()->user()->language}
                              . ' > ' .$category2nd->name->{auth()->user()->language},
                ];
            }
        }

        return $return;
    }

    public function groupedList()
    {
        $categories = Category::with([
            'categories' => function($categories){
                $categories->with('categories');
            }
        ])
            ->orderBy('order')
            ->get();

        $return = [];

        foreach ($categories as $category) {
            $return[] = [
                'value' => $category->id,
                'text' => $category->name->{auth()->user()->language},
            ];

            foreach ($category->categories as $category2nd) {
                $return[] = [
                    'value' => $category2nd->id,
                    'text' => $category->name->{auth()->user()->language}
                              . ' > ' .$category2nd->name->{auth()->user()->language},
                ];

                foreach ($category2nd->categories as $category3rd) {
                    $return[] = [
                        'value' => $category3rd->id,
                        'text' => $category->name->{auth()->user()->language}
                                  . ' > ' .$category2nd->name->{auth()->user()->language}
                                  . ' > ' .$category3rd->name->{auth()->user()->language},
                    ];
                }
            }
        }

        return $return;
    }

    public function updateOrder(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $category = Category::findOrFail($id);

            if ($request->order !== $category->order && $request->order > 0) {
                $this->sortCategories($category, $request->order);
            }

            DB::commit();
            return response([
                'message' => 'Ordem da Categoria atualizada com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
        private function sortCategories($category, $newOrder)
        {
            try {
                $arr = [$category->order, $newOrder];
                sort($arr);

                $categories = Category::whereBetween('order', $arr)
                    ->where('id', '<>', $category->id)
                    ->get();

                foreach ($categories as $DBcategory) {
                    if ($DBcategory->order > $category->order) {
                        $DBcategory->order--;
                        $DBcategory->save();
                        continue;
                    }

                    $DBcategory->order++;
                    $DBcategory->save();
                    continue;
                }

                $category->order = $newOrder;
                $category->save();
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
            }
    }
}
