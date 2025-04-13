<?php

namespace App\Http\Controllers\API\V1;

use App\Factories\OtpSenderFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OtpService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otpService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Register a new user",
     *     description="Registers a new user with the provided credentials",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterUserRequestSchema")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/UserSchema")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input - Validation errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponseSchema")
     *     ),
     * )
     */

    public function register(RegisterUserRequest $request)
    {
        $user = User::create($request->validated());

        return $this->success(['user' => $user], 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login with phone number and OTP",
     *     description="Logs in a user with the provided phone number and OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequestSchema")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ...")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input - Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid OTP or phone number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized access")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $isValid = $this->otpService->validateOtp($request->phone_number, $request->otp);

        if (!$isValid) {
            return $this->failure('Invalid OTP or OTP expired.', 400);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        try {
            if (!$token = JWTAuth::fromUser($user)) {
                return $this->failure('Unauthorized', 401);
            }
        } catch (JWTException $e) {
            return $this->failure('Could not create token', 500);
        }

        return $this->success(['token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/resend-otp",
     *     summary="Resend OTP to the phone number",
     *     description="Resends the OTP to the provided phone number",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone_number", type="string", example="+201000000000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="Otp has been sent.")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP has already been sent, please wait",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP has already been sent. Please wait."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/|exists:users,phone_number',
        ]);

        $phoneNumber = $request->input('phone_number');

        if (!$this->otpService->shouldSendOtp($phoneNumber)) {
            return $this->failure('OTP has already been sent. Please wait.', 400);
        }

        $otpSender = OtpSenderFactory::create('whatsapp');
        $this->otpService->setOtpSender($otpSender);
        $this->otpService->sendOtp($phoneNumber);

        return $this->success(['message' => 'OTP has been sent.'], 200);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User logged out']);
    }
}
