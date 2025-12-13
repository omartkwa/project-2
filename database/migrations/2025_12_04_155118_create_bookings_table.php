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
        Schema::create('bookings', function (Blueprint $table) {
             $table->id();

            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // طالب الحجز

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');

       
            $table->timestamps();

            $table->index(['apartment_id', 'start_date', 'end_date', 'status']);
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
