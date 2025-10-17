<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parking_zone_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();

            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('color_hex', 9)->default('#1976d2'); // #RRGGBB o #RRGGBBAA
            $table->unsignedInteger('max_stay_minutes')->nullable(); // null = sin límite
            $table->enum('outside_schedule_policy', ['unlimited', 'forbidden', 'same_as_inside'])->default('unlimited');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['municipality_id', 'slug']);
            $table->index(['municipality_id', 'status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('parking_zone_types');
    }
};
