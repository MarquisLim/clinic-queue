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
            try {
                $table->dropUnique(['doctor_id', 'slot_start']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropUnique(['doctor_id', 'slot_start', 'active_status']);
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appt_slot_key_active_unique');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('slot_key_active');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->unique(['doctor_id','slot_start'], 'appointments_doctor_id_slot_start_unique');
        });
    }
};
