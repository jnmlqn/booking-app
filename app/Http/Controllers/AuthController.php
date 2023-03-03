<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponser;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * @param  Request  $request
     * 
     * @return  Response
     */
    public function login(Request $request): Response
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'password' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponse(
                'Validation error',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $validator->errors()->getMessages()
            );
        }

        $credentials = request(['email', 'password']);
        $token = auth()->attempt($credentials);

        if (!$token) {
            return response()->json(
                [
                    'error' => 'Unauthorized'
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->respondWithToken($token);
    }

    /**
     * @return  Response
     */
    public function me(): Response
    {
        return $this->apiResponse(
            'User data fetched Successfully',
            Response::HTTP_OK,
            json_decode(json_encode(auth()->user()), true)
        );
    }

    /**
     * @return  Response
     */
    public function logout(): Response
    {
        auth()->logout();

        return $this->apiResponse('Successfully logged out');
    }

    /**
     * @return  Response
     */
    public function refresh(): Response
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * @param  string $token
     *
     * @return  Response
     */
    protected function respondWithToken(string $token): Response
    {
        return $this->apiResponse(
            'Authorization successful',
            Response::HTTP_OK,
            [
                'name' => auth()->user()->name,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        );
    }
}