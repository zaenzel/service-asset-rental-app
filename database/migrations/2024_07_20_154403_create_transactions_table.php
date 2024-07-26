<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign Key to users table
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Foreign Key to products table
            $table->string('slug')->unique();
            $table->dateTime('start_booking_date');
            $table->dateTime('end_booking_date');
            $table->unsignedInteger('price_per_hour')->default(0);
            $table->unsignedInteger('booking_duration')->default(0);
            $table->bigInteger('price_total')->default(0);
            $table->enum('status',  [
                \App\Models\Transaction::STATUS_PENDING,
                \App\Models\Transaction::STATUS_PROCESS,
                \App\Models\Transaction::STATUS_REJECT,
                \App\Models\Transaction::STATUS_APPROVE
            ])->default(\App\Models\Transaction::STATUS_PENDING);
            $table->enum('payment_status', [
                \App\Models\Transaction::PAYMENT_STATUS_NOT_YET_PAID,
                \App\Models\Transaction::PAYMENT_STATUS_DP,
                \App\Models\Transaction::PAYMENT_STATUS_LUNAS
            ])->default(\App\Models\Transaction::PAYMENT_STATUS_NOT_YET_PAID);
            $table->unsignedInteger('payment_total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};


// public function setBookingDurationAttribute($value)
    // {
    //     $startBookingDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->start_booking_date);
    //     $endBookingDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->end_booking_date);
    //     $totalHours = $startBookingDate->diffInHours($endBookingDate);

    //     $this->attributes['booking-_duration'] = $totalHours;
    // }