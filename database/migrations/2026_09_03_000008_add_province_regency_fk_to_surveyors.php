<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveyors', function (Blueprint $table) {
            if (Schema::hasColumn('surveyors', 'province_id') && ! $this->hasFk('surveyors', 'province_id')) {
                $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
            }
            if (Schema::hasColumn('surveyors', 'regency_id') && ! $this->hasFk('surveyors', 'regency_id')) {
                $table->foreign('regency_id')->references('id')->on('regencies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveyors', function (Blueprint $table) {
            $this->dropFkIfExists($table, 'surveyors', 'province_id');
            $this->dropFkIfExists($table, 'surveyors', 'regency_id');
        });
    }

    private function hasFk(string $table, string $column): bool
    {
        $conn = Schema::getConnection();
        $fk = $conn->select(
            "SELECT 1 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = ? 
               AND COLUMN_NAME = ? 
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );
        return ! empty($fk);
    }

    private function dropFkIfExists(Blueprint $table, string $tableName, string $column): void
    {
        $conn = Schema::getConnection();
        $fk = $conn->select(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = ? 
               AND COLUMN_NAME = ? 
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$tableName, $column]
        );
        if (! empty($fk)) {
            $table->dropForeign($fk[0]->CONSTRAINT_NAME);
        }
    }
};