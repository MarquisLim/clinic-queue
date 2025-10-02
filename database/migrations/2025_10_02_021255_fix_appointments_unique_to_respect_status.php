<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // имя индекса видно в ошибке: appointments_doctor_id_slot_start_unique
            $table->dropUnique('appointments_doctor_id_slot_start_unique');
        });


        DB::statement("
            ALTER TABLE `appointments`
            ADD COLUMN `active_status` TINYINT(1)
            AS (CASE WHEN `status` IN ('pending','checked_in','in_progress') THEN 1 ELSE 0 END)
            STORED
            AFTER `status`
        ");

        Schema::table('appointments', function (Blueprint $table) {
            $table->unique(['doctor_id','slot_start','active_status'], 'appt_doctor_slot_active_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appt_doctor_slot_active_unique');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('active_status');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->unique(['doctor_id','slot_start'], 'appointments_doctor_id_slot_start_unique');
        });
    }
};
