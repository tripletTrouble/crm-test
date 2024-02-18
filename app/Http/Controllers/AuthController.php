<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'max:255'],
            'password' => ['required']
        ]);

        $user = User::where('name', $credentials['username'])->first();
        
        if ($user && Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('')->plainTextToken
            ]);
        }

        throw ValidationException::withMessages([
            'username' => ['The provided credential are not valid']
        ]);
    }
}
