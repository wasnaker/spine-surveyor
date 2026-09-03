<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel branches — kantor cabang / site / pabrik milik Customer.
 *
 * vat_id nullable FK ke vats.id (NPWP cabang). Boleh null karena
 * cabang bisa tanpa NPWP sendiri, atau berbagi NPWP HO yang tercatat
 * di customers.parent_vat_number.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ulid', 26)->nullable()->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone', 32)->nullable();
            $table->foreignId('vat_id')->nullable()->constrained('vats')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
