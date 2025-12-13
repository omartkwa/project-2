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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');       // المستأجر
            $table->unsignedBigInteger('apartment_id');  // الشقة

            $table->tinyInteger('rating');               // 1 - 5
            $table->text('comment')->nullable();        // تعليق اختياري

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');

            // منع تكرار تقييم نفس المستأجر لنفس الشقة
            $table->unique(['user_id', 'apartment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
