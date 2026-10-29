<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subjects are never deleted (offerings and grades point to them): "Delete" in Enrollment > Subjects sets
 * subjstatus to 0 (Inactive). Existing subjects stay Active (1).
 * Run only this file on the schedule database:
 *   php artisan migrate --database=schedule --path=database/migrations/sched/2026_10_29_120000_add_subjstatus_to_subjects_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subjects', 'subjstatus')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->tinyInteger('subjstatus')->default(1)->comment('1 = Active, 0 = Inactive (subjects are never deleted)');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subjects', 'subjstatus')) {
            Schema::table('subjects', fn (Blueprint $table) => $table->dropColumn('subjstatus'));
        }
    }
};
