+<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumMediaTypes;
use App\Helpers\ValidateJsonLangKey;
use App\Http\Controllers\Controller;
use App\Models\Media;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $medias = Media::withTrashed()
            ->with([
                'category' => function ($category) {
                    return $category->with('category');
                }
            ])->orderBy('deleted_at');

        if ($request->search) {
            $medias->where('media', 'LIKE', "%{$request->search}%");
        }

        return $medias->paginate((int)($request->length ?? 10));
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
            'type' => [
                'required',
                Rule::in(array_keys(EnumMediaTypes::TYPES_ARRAY))
            ]
        ], []);

        if ($request->type == EnumMediaTypes::PDF) {
            $this->validate($request, ['media' => 'required|file|mimes:pdf,doc,jpeg,png']);
        }

        if ($request->type == EnumMediaTypes::VIDEO || $request->type == EnumMediaTypes::TEXT) {
            $this->validate($request, ['media' => 'required|json']);
        }

        try {
            ValidateJsonLangKey::validate($request->media);

            if ($request->type == EnumMediaTypes::VIDEO) {
                $request['media'] = str_replace([
                    "https://vimeo.com/",
                    "http://vimeo.com/",
                    "https://player.vimeo.com/",
                    "http://player.vimeo.com/",
                ], '', $request->media);
            }

            //TODO: file upload
            if ($request->type == EnumMediaTypes::PDF) {
            }

            Media::create($request->all());

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
            return Media::with(['category'])->findOrFail($id);
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
            'type' => [
                'required',
                Rule::in(array_keys(EnumMediaTypes::TYPES_ARRAY))
            ]
        ], []);

        if ($request->type == EnumMediaTypes::PDF) {
            $this->validate($request, ['media' => 'required|file|mimes:pdf,doc,jpeg,png']);
        }

        if ($request->type == EnumMediaTypes::VIDEO || $request->type == EnumMediaTypes::TEXT) {
            $this->validate($request, ['media' => 'required|json']);
        }

        try {
            ValidateJsonLangKey::validate($request->media);

            if ($request->type == EnumMediaTypes::VIDEO) {
                $request['media'] = str_replace([
                    "https://vimeo.com/",
                    "http://vimeo.com/",
                    "https://player.vimeo.com/",
                    "http://player.vimeo.com/",
                ], '', $request->media);
            }

            //TODO: file upload
            if ($request->type == EnumMediaTypes::PDF) {
            }

            $media = Media::findOrFail($id);
            $media->update($request->all());

            return response([
                'message' => trans('messages.updated_success'),
                'medias' => $this->index(new Request),
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
            $media = Media::findOrFail($id);
            $media->delete();

            return response([
                'message' => trans('messages.deleted_success'),
                'medias' => $this->index(new Request),
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

            $media = Media::withTrashed()->findOrFail($id);
            $media->restore();

            return response([
                'message' => trans('messages.restore_success'),
                'medias' => $this->index(new Request),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function types()
    {
        return EnumMediaTypes::TYPES;
    }
}
