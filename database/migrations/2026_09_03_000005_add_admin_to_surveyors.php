<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('surveyors', 'admin')) {
            Schema::table('surveyors', function (Blueprint $table) {
                $table->unsignedBigInteger('admin')->nullable()->after('parent');
            });
        }
    }

    public function down(): void
    {
        Schema::table('surveyors', function (Blueprint $table) {
            $table->dropColumn('admin');
        });
    }
};
