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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->date('date');                         // день
            $table->time('start_time');                   // начало рабочего окна
            $table->time('end_time');                     // конец рабочего окна
            $table->unsignedSmallInteger('slot_len_min'); // длина слота
            $table->json('breaks')->nullable();           // [{start:"12:00",end:"12:30"}]
            $table->boolean('is_closed')->default(false); // закрытие дня
            $table->text('closed_reason')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id','date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
