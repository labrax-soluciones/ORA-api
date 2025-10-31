<?php

use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // Dueño del vehículo
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Atributos del vehículo
            $table->string('brand')->nullable();   // marca
            $table->string('model')->nullable();   // modelo
            $table->string('color')->nullable();   // color (texto libre o hex si prefieres)
            $table->string('license_plate');       // matrícula (no única global)

            // Estado
            $table->string('status')->default(VehicleStatus::Active->value);

            // Datos opcionales
            $table->year('year')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Búsquedas frecuentes
            $table->index(['license_plate']);
            $table->index(['user_id', 'status']);

            // Evita duplicados de una misma matrícula dentro del mismo usuario,
            // pero permite que otros usuarios registren la misma matrícula.
            $table->unique(['user_id', 'license_plate']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicles');
    }
};
