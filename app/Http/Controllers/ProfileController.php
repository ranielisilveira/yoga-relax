@@ -0,0 +1,71 @@
<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateJsonLangKey;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|min:6',
            'password' => 'required|confirmed|min:6',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->user()->id);

            if (!Hash::check($request->current_password, $user->password)) {
                throw new Exception(trans('messages.auth.current_password_not_match'));
            }

            if (Hash::check($request->password, $user->password)) {
                throw new Exception(trans('messages.auth.password_same_as_current'));
            }

            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();
            return response([
                'message' => trans('messages.updated_success')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollback();
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {

            if ($request->language) {
                ValidateJsonLangKey::valid($request->language);
            }

            $user = User::findOrFail($request->user()->id);
            $user->name = $request->name ?? $user->name;
            $user->language = $request->language ?? $user->language;
            $user->save();

            DB::commit();
            return response([
                'message' => trans('messages.updated_success'),
                'user' => $user
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollback();
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
