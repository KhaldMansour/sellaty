<h1>Verify OTP</h1>

@if($errors->any())
    <p style="color: red;">{{ $errors->first() }}</p>
@endif

<form method="POST" action="{{ route('account.delete.verify.otp') }}">
    @csrf
    <input type="hidden" name="phone_number" value="{{ $phone_number }}">
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button type="submit">Verify & Delete Account</button>
</form>
