<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename kolom `parent` -> `parent_id` (surveyors).
 * Konvensi: kolom FK relasi selalu suffix _id; nama kolom tidak boleh
 * sama dengan nama method relasi (parent()) — attribute menimpa relasi
 * saat serialisasi, jadi ->with('parent') tak pernah muncul.
 *
 * Constraint ikut di-drop lalu dibuat ulang dengan nama baru:
 *   - FK       surveyors_parent_foreign       -> surveyors_parent_id_foreign
 *   - UNIQUE   surveyors_parent_code_unique   -> surveyors_parent_id_code_unique
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('surveyors', 'parent') || Schema::hasColumn('surveyors', 'parent_id')) {
            return;
        }

        Schema::table('surveyors', function (Blueprint $table) {
            $table->dropForeign('surveyors_parent_foreign');
            $table->dropUnique('surveyors_parent_code_unique');
            $table->renameColumn('parent', 'parent_id');
        });

        Schema::table('surveyors', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('surveyors')->nullOnDelete();
            $table->unique(['parent_id', 'code'], 'surveyors_parent_id_code_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('surveyors', 'parent_id') || Schema::hasColumn('surveyors', 'parent')) {
            return;
        }

        Schema::table('surveyors', function (Blueprint $table) {
            $table->dropForeign('surveyors_parent_id_foreign');
            $table->dropUnique('surveyors_parent_id_code_unique');
            $table->renameColumn('parent_id', 'parent');
        });

        Schema::table('surveyors', function (Blueprint $table) {
            $table->foreign('parent')->references('id')->on('surveyors')->nullOnDelete();
            $table->unique(['parent', 'code']);
        });
    }
};
