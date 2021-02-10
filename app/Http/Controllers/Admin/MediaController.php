+<?php

    namespace App\Http\Controllers\Admin;

    use App\Enum\EnumMediaTypes;
    use App\Helpers\ValidateJsonLangKey;
    use App\Http\Controllers\Controller;
    use App\Models\Media;
    use Exception;
    use Illuminate\Validation\Rule;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
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
                        $category->withTrashed()->with([
                            'category' => function ($middleCategory) {
                                $middleCategory->withTrashed()->with([
                                    'category' => function ($parentCategory) {
                                        $parentCategory->withTrashed();
                                    }
                                ]);
                            }
                        ]);
                    }
                ])
                ->whereHas('category', function ($category) use ($request) {
                    if ($request->search) {
                        $category->where('name', 'LIKE', "%{$request->search}%");
                    }
                })
                ->orderBy('deleted_at');

            if ($request->search) {
                $medias = Media::withTrashed()
                    ->with([
                        'category' => function ($category) {
                            $category->withTrashed()->with([
                                'category' => function ($middleCategory) {
                                    $middleCategory->withTrashed()->with([
                                        'category' => function ($parentCategory) {
                                            $parentCategory->withTrashed();
                                        }
                                    ]);
                                }
                            ]);
                        }
                    ]);

                $media1st = (clone $medias)
                    ->whereHas('category', function ($category) use ($request) {
                        return $category->where('name', 'LIKE', "%{$request->search}%");
                    })->orderBy('deleted_at');

                $media2nd = (clone $medias)
                    ->whereHas('category', function ($category) use ($request) {
                        return $category->whereHas('category', function ($category2nd) use ($request) {
                            $category2nd->where('name', 'LIKE', "%{$request->search}%");
                        });
                    })->orderBy('deleted_at');

                $media3rd = (clone $medias)
                    ->whereHas('category', function ($category) use ($request) {
                        return $category->whereHas('category', function ($category2nd) use ($request) {
                            return $category2nd->whereHas('category', function ($category3rd) use ($request) {
                                $category3rd->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                    })->orderBy('deleted_at');

                    $data = $media1st->get()->merge($media2nd->get())->merge($media3rd->get());
                    return [
                        "current_page" => 1,
                        "data" => $data,
                        "from" => 1,
                        "per_page" => count($data),
                        "prev_page_url" => null,
                        "to" => count($data),
                        "total" => count($data)
                    ];
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
            DB::beginTransaction();
            try {
                ValidateJsonLangKey::validate($request->media);

                if ($request->type == EnumMediaTypes::VIDEO) {

                    $languages = explode(",", env("LANGUAGES"));
                    $media = json_decode($request->media, 1);

                    $videosBaseLang = explode(",", $media[$languages[0]]);
                    $arrayBaseLength = count($videosBaseLang);
                    $allVideos = [];

                    foreach ($languages as $lang) {
                        // validate array length for all languages
                        if (count(explode(",", $media[$lang])) !== $arrayBaseLength) {
                            throw new Exception("A mesma quantidade de vídeos deve ser preenchida para todas as linguagens ($lang)");
                        }
                        $allVideos[$lang] = explode(",", $media[$lang]);
                    }


                    foreach ($videosBaseLang as $k => $video) {
                        $item = '{';
                        foreach ($languages as $lang) {
                            $item .= '"' . $lang . '":"' . trim($allVideos[$lang][$k]) . '",';
                        }
                        $item = substr($item, 0, -1);
                        $item .= "}";

                        Media::create([
                            'category_id' => $request->category_id,
                            'media' => $item,
                            'type' => EnumMediaTypes::VIDEO,
                        ]);
                    }
                }

                //TODO: file upload
                if ($request->type == EnumMediaTypes::PDF) {
                }
                DB::commit();
                return response([
                    'message' => trans('messages.created_success')
                ], Response::HTTP_OK);
            } catch (Exception $e) {
                DB::rollBack();
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
