<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parking_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parking_zone_type_id')->constrained('parking_zone_types')->cascadeOnDelete();

            $table->string('name', 160);
            $table->string('slug', 180);
            $table->text('description')->nullable();

            $table->unsignedInteger('capacity')->nullable(); // máximo de vehículos/plazas
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');

            // Geometría en GeoJSON (Polygon/MultiPolygon) WGS84
            $table->json('geometry');

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['municipality_id', 'slug']);
            $table->index(['municipality_id', 'status']);
            $table->index('parking_zone_type_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('parking_zones');
    }
};
