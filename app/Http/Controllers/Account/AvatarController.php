<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\Accounts\ReaderAvatar;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function show(Request $request, ReaderAvatar $avatars)
    {
        return response()->json(['photo' => $avatars->read($request->user())]);
    }

    public function store(Request $request, ReaderAvatar $avatars)
    {
        $data = $request->validate(['photo' => ['required', 'string', 'max:2796204']]);
        $avatars->replace($request->user(), $data['photo']);
        return response()->json(['user' => $request->user()->fresh()->profile()]);
    }

    public function destroy(Request $request, ReaderAvatar $avatars)
    {
        $avatars->replace($request->user(), null);
        return response()->json(['user' => $request->user()->fresh()->profile()]);
    }
}
