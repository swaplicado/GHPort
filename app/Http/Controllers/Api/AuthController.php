<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
    
            $credentials = request(['username', 'password']);
    
            if (!Auth::attempt($credentials)) {
                return response()->json([
                                        'status' => 'unauthorized',
                                        'message' => 'Credenciales incorrectas'
                                    ], 401);
            }
    
            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
    
            $token->expires_at = Carbon::now()->addDays(365);
    
            $token->save();
    
            return response()->json([
                        'status' => 'success',
                        'access_token' => $tokenResult->accessToken,
                        'token_type' => 'Bearer',
                        'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                    ]);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['status' => 'success', 'message' => 'Successfully logged out']);
    }

     /**
     * Método para validar si el token aún es válido
     */
    public function isTokenValid(Request $request)
    {
        // Obtener el token de autorización desde el header
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader) {
            return response()->json(['message' => 'No se proporcionó un token.'], 401);
        }

        // Extraer el token Bearer
        $token = str_replace('Bearer ', '', $authorizationHeader);

        // Decodificar el token JWT (opcional si quieres ver los datos del token)
        $tokenId = $this->getTokenIdFromJWT($token);

        if (!$tokenId) {
            return response()->json(['message' => 'Token no válido.'], 401);
        }

        // Buscar el token en la base de datos
        $passportToken = Token::find($tokenId);

        if (!$passportToken) {
            return response()->json(['message' => 'Token no encontrado.'], 401);
        }

        // Verificar si el token ha expirado
        if ($passportToken->expires_at < Carbon::now()) {
            return response()->json(['message' => 'Token expirado.'], 401);
        }

        // Verificar si el token ha sido revocado
        if ($passportToken->revoked) {
            return response()->json(['message' => 'Token revocado.'], 401);
        }

        // Si todo está bien, el token es válido
        return response()->json(['message' => 'Token válido.'], 200);
    }

    /**
     * Método para obtener el ID del token desde el JWT (si estás usando JWT)
     */
    private function getTokenIdFromJWT($token)
    {
        try {
            // Usamos una función de Laravel Passport para decodificar el token JWT
            $jwtParts = explode('.', $token);
            $payload = json_decode(base64_decode($jwtParts[1]), true);

            return $payload['jti'] ?? null; // 'jti' es el ID del token en el payload
        } catch (\Exception $e) {
            return null;
        }
    }

    public function loginBridge(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = request(['username', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                                    'status' => 'Unauthorized',
                                    'error' => 'Credenciales incorrectas'
                                ], 401);
        }

        $user = $request->user();
        $client = new Client([
            'base_uri' => 'http://192.168.1.251:8000',
            'timeout' => 30.0,
        ]);
        $credentials = [
            "username" => $request->username,
            "password" => $request->username
        ];

        try {
            $response = $client->request('POST', 'login/', [
                'json' => $credentials,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);
            $jsonString = $response->getBody()->getContents();
            $data = json_decode($jsonString);

            return response()->json([
                'status' => 'success',
                'token' => $data->token,
                'token_type' => 'Token',
                'user' => $data->user,
                'area_holder' => $data->area_holder
            ], 200);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorString = $errorResponse->getBody()->getContents();
                $errorData = json_decode($errorString);
                
                return response()->json([
                    'status' => 'error',
                    'error' => $errorData->error
                ], 500);

            } else {
                // Maneja el caso donde no hay respuesta del servidor
                return response()->json([
                    'status' => 'error',
                    'error' => 'Ocurrio un error inesperado en el servidor',
                    'data' => $e
                ], 500);
            }
        }
    }

    public function logoutBridge()
    {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
