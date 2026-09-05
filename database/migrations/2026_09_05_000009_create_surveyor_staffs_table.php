<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveyor_staffs', function (Blueprint $table) {
            $table->id();

            // Profil staff terhubung 1:1 ke user login (realname/jabatan di sini).
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Surveyor HO (type=surveyor) atau branch tempat staff bekerja.
            $table->foreignId('surveyor_id')
                ->constrained('surveyors')
                ->cascadeOnDelete();

            $table->string('realname');      // nama asli untuk optionlist/dropdown
            $table->string('jabatan', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveyor_staffs');
    }
};