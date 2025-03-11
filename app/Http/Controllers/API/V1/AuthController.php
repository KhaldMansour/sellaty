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

    public function register(RegisterUserRequest $request)
    {
        $user = User::create($request->validated());

        $otpSender = OtpSenderFactory::create('whatsapp');
        $this->otpService->setOtpSender($otpSender);
        $this->otpService->generateOtp($request->phone_number);

        return $this->success(['user' => $user], 'User registered successfully', 201);
    }


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

        return response()->json(['message' => 'OTP has been sent.'], 200);
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
