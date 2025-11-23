<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerifiedMail;                 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;      
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // POST /api/auth/login_admin
    public function login_admin(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $claims = [
            'email'     => $request->email,
            'password'  => $request->password,
            'type_user' => 1, // 1 = admin (Metronic)
        ];

        if (! $token = Auth::guard('api')->attempt($claims)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, Auth::guard('api')->user());
    }

    // POST /api/auth/login_ecommerce
    public function login_ecommerce(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $claims = [
            'email'     => $request->email,
            'password'  => $request->password,
            'type_user' => 2, // 2 = ecommerce
        ];

        if (! $token = Auth::guard('api')->attempt($claims)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, Auth::guard('api')->user());
    }

    // ✅ POST /api/auth/register
    public function register(Request $request)
    {
        $request->validate([
            'name'      => ['required','string','max:100'],
            'surname'   => ['nullable','string','max:100'],
            'email'     => ['required','email','unique:users,email'],
            'password'  => ['required','string','min:6'],
            'phone'     => ['nullable','string','max:30'],
            'avatar'    => ['nullable','string','max:255'],
            'type_user' => ['nullable','integer'],
        ]);

        // 1️⃣ Generar uniqid para verificación
        $uniq = uniqid();

        // 2️⃣ Crear usuario (sin login automático)
        $user = User::create([
            'name'      => $request->name,
            'surname'   => $request->surname,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'uniqid'    => $uniq,
            'avatar'    => $request->avatar,
            'type_user' => $request->type_user ?? 2, // por defecto ecommerce
            'password'  => Hash::make($request->password),
        ]);

        // 3️⃣ Enviar correo de verificación (protegido con try/catch)
        try {
            Mail::to($user->email)->send(new VerifiedMail($user));
        } catch (\Throwable $e) {
            Log::error('Error enviando correo de verificación', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'error'   => $e->getMessage(),
            ]);

            // La cuenta ya existe, pero avisamos del fallo de envío
            return response()->json([
                'message' => 'Tu cuenta fue creada, pero hubo un problema al enviar el correo de verificación. Intenta más tarde o contacta soporte.',
            ], 201);
        }

        // 4️⃣ Respuesta OK
        return response()->json([
            'message' => 'Registro exitoso. Te enviamos un correo para activar tu cuenta. Revisa tu bandeja de entrada y spam.',
        ], 201);
    }

    // 🔒 GET /api/auth/me
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }

    // 🔒 POST /api/auth/logout
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    // 🔒 POST /api/auth/refresh
    public function refresh()
    {
        return $this->respondWithToken(
            Auth::guard('api')->refresh(),
            Auth::guard('api')->user()
        );
    }

    // Helper de respuesta JWT
    protected function respondWithToken($token, $user = null, $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60,
            'user'         => $user ?? Auth::guard('api')->user(),
        ], $status);
    }
}