<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('municipal_admin_profiles', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();

            // Datos propios (puedes ampliar cuando quieras)
            $table->string('phone')->nullable();
            $table->string('id_document')->nullable();
            $table->string('avatar_path')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique('user_id');            // un usuario solo puede ser admin de un municipio
            $table->index(['municipality_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('municipal_admin_profiles');
    }
};
