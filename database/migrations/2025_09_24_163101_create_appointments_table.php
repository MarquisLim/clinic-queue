<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();

            $table->dateTime('slot_start');                       // начало слота
            $table->unsignedSmallInteger('slot_len_min')->nullable(); // на момент записи

            $table->enum('status', [
                'pending',     // записан
                'checked_in',  // отметился у регистратуры
                'in_progress', // прием начат
                'done',        // прием окончен
                'cancelled',   // отменён
            ])->default('pending');

            // Талон и детали
            $table->string('ticket_no')->nullable();
            $table->boolean('late_cancel')->default(false);
            $table->text('complaint')->nullable();      // жалоба/описание (опц.)

            // Фактические времена
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            $table->timestamps();

            $table->unique(['doctor_id','slot_start']);

            $table->index(['doctor_id','status']);
            $table->index(['patient_id','status']);
            $table->index(['specialty_id','slot_start']);
            $table->index('slot_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
