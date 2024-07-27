<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Res;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="AuthToken",
 *     type="object",
 *     @OA\Property(property="token", type="string")
 * )
 */

class AuthController extends Controller
{
    use Res;

    /**
     * @OA\Post(
     *      path="/api/v1/register",
     *      summary="Register a new user",
     *      description="Registers a new user with the provided details and returns a JWT token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "email", "password"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully",
     *          @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Validation errors")
     *          )
     *      )
     * )
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => ['string', 'required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendRes('error', false, $validator->errors(), 400);
        }

        $userData = $validator->validated();
        $userData['password'] = Hash::make($userData['password']);

        $user = User::create($userData);

        $token = JWTAuth::fromUser($user);
        return $this->respondWithToken($token, true, 'User registered successfully', $user, 201);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/login",
     *      summary="Log in a user",
     *      description="Logs in a user with provided credentials and returns a JWT token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login successful",
     *          @OA\JsonContent(ref="#/components/schemas/AuthToken")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Validation errors")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Invalid credentials",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Invalid Credentials")
     *          )
     *      )
     * )
     */
    public function login(Request $request)
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendRes('error', false, $validator->errors(), 400);
        }

        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->sendRes('error', false, 'Invalid Credentials', 401);
        }

        $user = Auth::user();
        return $this->respondWithToken($token, true, 'Login successful', $user, 200);
    }

    private function respondWithToken($token, $success, $message, $user, $status)
    {
        return $this->sendRes('success', $success, [
            'token' => $token,
            'user' => $user
        ], $status);
    }
}
