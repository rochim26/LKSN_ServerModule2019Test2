<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\LoginToken;
use Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'invalid login'
            ], 401);
        }

        $user = User::findOrFail(Auth::user()->id);

        $token = LoginToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => bcrypt($user->id)]
        );

        return response()->json([
            'token' => $token->token,
            'member' => 'member'
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'alpha|between:2,20',
            'last_name' => 'alpha|between:2,20',
            'username' => 'regex:/^[a-zA-Z0-9._]*$/|between:5,12|unique:users,username',
            'password' => 'between:5,12'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'invalid field'
            ], 422);
        }

        $request->merge(['password' => bcrypt($request->password)]);

        $user = User::create($request->all());

        $token = LoginToken::updateOrCreate(
            ['user_id' => $user->id],
            ['token' => bcrypt($user->id)]
        );

        return response()->json([
            'token' => $token->token,
            'role' => 'member'
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = LoginToken::whereToken($request->bearerToken())->first();
        $token->delete();

        Auth::logout();

        return response()->json([
            'message' => 'logout success'
        ], 200);
    }
}
