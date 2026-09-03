<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('surveyors', 'province_id')) {
            Schema::table('surveyors', function (Blueprint $table) {
                $table->unsignedBigInteger('province_id')->nullable()->after('address');
            });
        }
        if (! Schema::hasColumn('surveyors', 'regency_id')) {
            Schema::table('surveyors', function (Blueprint $table) {
                $table->unsignedBigInteger('regency_id')->nullable()->after('province_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('surveyors', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'regency_id']);
        });
    }
};
