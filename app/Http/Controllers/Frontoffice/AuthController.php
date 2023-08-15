<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required|string|max:255|unique:users",
            "password" => "required|string|min:8",
            "name" => "required|string|max:255",
            "phone" => "required|string|max:13",
            "address" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $new_user = User::create([
            "username" => $request->username,
            "name" => $request->name,
            "password" => Hash::make($request->password)
        ]);

        Customer::create([
            "user_id" => $new_user->id,
            "phone" => $request->phone,
            "address" => $request->address
        ]);

        return response()->json(['message' => 'Registration success.']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if(!Auth::attempt($request->only('username', 'password'))){
            return response()->json(['message' => 'Unauthorized.'], 401);
        };

        $user = User::where('username', $request->username)->firstOrFail();
        $customer = Customer::where('user_id', $user->id)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login success.', 'access_token' => $token, 'customer' => $customer]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout success.']);
    }
}
