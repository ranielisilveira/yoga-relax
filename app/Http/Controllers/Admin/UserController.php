<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['code'])
            ->orderByDesc('created_at')
            ->orderBy('name');

        if ($request->search) {
            $users->where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('email', 'LIKE', "%{$request->search}%");
        }

        return $users->paginate((int)($request->length ?? 10));
    }
}
