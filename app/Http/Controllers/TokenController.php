<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json(['token' => $token], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function impersonate(Request $request)
    {
        $request->validate(['userId' => 'required|exists:users,id']);

        $user = User::findOrFail($request->input('userId'));

        // Clean up any previous impersonation tokens for this user
        $user->tokens()->where('name', 'impersonate')->delete();

        $token = $user->createToken('impersonate')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function unimpersonate(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Unimpersonated']);
    }
}
