<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Datos básicos (nullable para no romper seeds/usuarios existentes)
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('id_document')->nullable()->after('phone'); // DNI/NIE
            $table->string('avatar_path')->nullable()->after('id_document');

            // Si más adelante quieres unicidad en documento:
            // $table->unique('id_document');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            // Quitar índices primero si los añadiste
            // $table->dropUnique(['id_document']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'id_document',
                'avatar_path',
            ]);
        });
    }
};
