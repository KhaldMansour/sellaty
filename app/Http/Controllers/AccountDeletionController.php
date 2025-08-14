<?php

namespace App\Http\Controllers;

use App\Factories\OtpSenderFactory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountDeletionController extends Controller
{
    public function showPhoneForm()
    {
        return view('delete-account-phone_number');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        $existingOtp = DB::table('account_deletion_otps')
            ->where('phone_number', $request->phone_number)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existingOtp) {
            return back()->withErrors([
                'error' => 'An OTP has already been sent. Please wait until it expires before requesting a new one.'
            ]);
        }

        $otp = rand(100000, 999999);

        DB::table('account_deletion_otps')->insert([
            'phone_number' => $request->phone_number,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otpDriver = config('services.otp.driver', 'appsenders');
        $otpSender = OtpSenderFactory::create($otpDriver);
        $otpSender->sendOtp($request->phone_number, $otp);

        return redirect()->route('account.delete.otp.form')->with('phone_number', $request->phone_number);
    }

    public function showOtpForm(Request $request)
    {
        return view('verify-deletion-otp', ['phone_number' => session('phone_number')]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp' => 'required|numeric',
        ]);

        $otpData = DB::table('account_deletion_otps')
            ->where('phone_number', $request->phone_number)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpData || Carbon::parse($otpData->expires_at)->isPast()) {
            return back()->withErrors(['error' => 'Invalid or expired OTP.']);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if ($user) {
            $user->delete();
        }

        DB::table('account_deletion_otps')
            ->where('phone_number', $request->phone_number)
            ->delete();

        return redirect()->route('account.delete.phone.form')->with('success', 'Your account has been deleted.');
    }
}
