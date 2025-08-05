<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number'); // Store the phone number
            $table->string('otp'); // Store the OTP
            $table->timestamp('expires_at'); // Store when the OTP expires
            $table->timestamps(); // Created and updated timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('otps'); // Drop the table if we roll back the migration
    }
}
