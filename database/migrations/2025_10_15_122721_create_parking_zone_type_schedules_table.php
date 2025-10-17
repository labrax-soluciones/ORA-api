<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parking_zone_type_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parking_zone_type_id')
                ->constrained('parking_zone_types')
                ->cascadeOnDelete();

            // 1 = lunes … 7 = domingo
            $table->unsignedTinyInteger('day_of_week'); // 1..7
            $table->time('start_time');
            $table->time('end_time');

            $table->string('timezone', 64)->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->json('settings')->nullable();

            $table->timestamps();

            // Índice compuesto con nombre corto (antes se pasaba de 64 chars)
            $table->index(['parking_zone_type_id', 'day_of_week'], 'pzts_type_dow_idx');

            // Opcional: evita duplicar tramos idénticos para el mismo tipo/día
            $table->unique(
                ['parking_zone_type_id', 'day_of_week', 'start_time', 'end_time'],
                'pzts_type_day_start_end_uniq'
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('parking_zone_type_schedules');
    }
};
