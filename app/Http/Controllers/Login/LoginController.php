<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class loginController extends Controller
{
    protected $reglasLogin = [
        'email'     => 'required|string|max:60',
        'password'  => 'required|string|max:60',
    ];

    protected $reglasRegister = [
        'name'      => 'required|string|max:60',
        'email'     => 'required|string|max:60',
        'password'  => 'required|string|max:60',
    ];

    public function login(Request $request)
    {
        $validacion = Validator::make($request->all(), $this->reglasLogin);

        if ($validacion->fails())
            return response()->json([
                'msg' => 'Error en las validaciones',
                'data' => $validacion->errors(),
                'status' => '422'
            ], 422);

        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json([
                'msg' => 'Usuario no encontrado',
                'data' => 'error',
                'status' => '404'
            ], 404);

        if (!Hash::check($request->password, $user->password))
            return response()->json([
                'msg' => 'Contraseña incorrecta',
                'data' => 'error',
                'status' => '401'
            ], 401);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
    }

    public function register(Request $request)
    {
        $validacion = Validator::make($request->all(), $this->reglasRegister);

        if ($validacion->fails())
            return response()->json([
                'msg' => 'Error en las validaciones',
                'data' => $validacion->errors(),
                'status' => '422'
            ], 422);

        $user = User::where('email', $request->email)->first();

        if ($user)
            return response()->json([
                'msg' => 'Usuario ya existente',
                'data' => $user,
                'status' => '422'
            ], 422);
        
        $userNew = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (!$userNew)
            return response()->json([
                'msg' => 'Error al crear el usuario',
                'data' => $userNew,
                'status' => '500'
            ], 500);

        return response()->json([
            'msg' => 'Usuario creado',
            'data' => $userNew,
            'status' => '201'
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'msg' => 'Sesión cerrada',
            'status' => 'success'
        ], 200);
    }

    public function validar(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json([
                'msg' => 'Token no enviado',
                'data' => null,
                'status' => 404
            ], 404);
        }

        $id = $request->id;
        $token = PersonalAccessToken::findToken($accessToken);

        if (!$token || $token->revoked) {
            return response()->json([
                'msg' => 'token no encontrado o revocado',
                'data' => false,
                'status' => 401
            ], 401);
        }

        $consu = DB::table('personal_access_tokens')
            ->where('tokenable_id', $id)
            ->where('token', $token)
            ->first();

        if (!$consu)
            response()->json([
                'msg' => 'El token no es valido',
                'data' => false,
                'status' => 422
            ], 422);

        return response()->json([
            'msg' => 'Token valido',
            'data' => true,
            'status' => 200
        ], 200);
    }
}
