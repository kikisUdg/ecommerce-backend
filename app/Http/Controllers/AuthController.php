<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a User.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            // Devuelve errores de validación y status 400
            return response()->json($validator->errors()->toArray(), 400);
        }

        $user = new User();
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json($user, 201);
    }

    /**
     * Login and get JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Usa explícitamente el guard 'api' (JWT)
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    /*
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    */
    
    /**
     * Get the authenticated User.
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }
    /*
    public function me()
    {
        return response()->json(auth()->user());
    }
    */
    /**
     * Logout (invalidate token).
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    /*
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    */
    /**
     * Refresh token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
    /*
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    */
    /**
     * Standard token response.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            "user" => [
                "name" => auth('api')->user()->name . ' ' . auth('api')->user()->surname, 
                "email" => auth('api')->user()->email,
            ],
        ]);
    }
    /*
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);
    }
    */
}