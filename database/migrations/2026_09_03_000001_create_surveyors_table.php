<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel surveyors — entity utama modul Surveyor.
 *
 * Lifecycle: is_active (boolean), soft deletes, ulid.
 * NPWP HO attach via vat_id FK ke vats.id (module spine-vat). Kalau
 * surveyor tanpa NPWP HO, vat_id = null. Branch NPWP pakai branches.vat_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveyors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ulid', 26)->nullable()->unique();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->foreignId('vat_id')->nullable()->constrained('vats')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveyors');
    }
};
