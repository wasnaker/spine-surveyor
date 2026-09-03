<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename kolom `admin` -> `admin_id` (surveyors).
 * Nama kolom lama bentrok dengan relasi admin() — attribute (int) menimpa
 * relasi saat serialisasi. Dengan admin_id, relasi admin() normal kembali.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('surveyors', 'admin') && ! Schema::hasColumn('surveyors', 'admin_id')) {
            Schema::table('surveyors', function (Blueprint $table) {
                $table->renameColumn('admin', 'admin_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('surveyors', 'admin_id') && ! Schema::hasColumn('surveyors', 'admin')) {
            Schema::table('surveyors', function (Blueprint $table) {
                $table->renameColumn('admin_id', 'admin');
            });
        }
    }
};
