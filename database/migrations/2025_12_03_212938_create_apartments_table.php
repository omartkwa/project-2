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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->string('state');               // عنوان الشقة
            $table->string('city');               // عنوان الشقة
            $table->string('street');               // عنوان الشقة
            $table->string('building_number')->nullable();
            $table->unsignedInteger('rooms')->default(1);
            $table->unsignedInteger('floor')->nullable();
            $table->Integer('area')->nullable(); 
            $table->boolean('has_furnish')->nullable();
            $table->integer('price')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
