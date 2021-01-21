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
        return Category::with([
            'category',
            'categories'
        ])
            ->orderBy('name')->paginate((int)($request->length ?? 10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|json|unique:categories,name',
            'color' => [
                'required',
                Rule::in(EnumColors::COLORS_ARRAY)
            ]
        ], []);

        try {

            ValidateJsonLangKey::validate($request->name);

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
        $this->validate($request, [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|json|unique:categories,name,' . $id . ',id',
            'color' => [
                'required',
                Rule::in(EnumColors::COLORS_ARRAY)
            ]
        ], []);

        try {

            ValidateJsonLangKey::validate($request->name);

            $category = Category::findOrFail($id);
            $category->update($request->all());

            return response([
                'message' => trans('messages.updated_success')
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
                throw new Exception("Não é possível excluir, existem categorias filhas relacionadas a este item.");
            }

            $category->delete();

            return response([
                'message' => trans('messages.deleted_success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
