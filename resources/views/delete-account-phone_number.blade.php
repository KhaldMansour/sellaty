<!DOCTYPE html>
<html>
<head>
    <title>Request Account Deletion</title>
</head>
<body>
    <h1>{{ config('app.name') }}</h1>
    <h1>Request Account Deletion</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <p>Enter your phone number to receive a One-Time Password (OTP) to confirm deletion.</p>

    <form method="POST" action="{{ route('account.delete.send.otp') }}">
        @csrf
        <div>
            <label for="phone_number">Phone Number:</label>
            <input type="text"
                name="phone_number"
                id="phone_number"
                value="{{ old('phone_number') }}">
        </div>
        <button type="submit">Send OTP</button>
    </form>

    <hr>
    <h3>Data Deletion Policy</h3>
    <ul>
        <li>Deleted: Name, phone number, profile info, activity logs</li>
        <li>Retained: Transaction history for 180 days (for legal compliance)</li>
        <li>Processing time: Within 2 business days</li>
    </ul>
</body>
</html>
