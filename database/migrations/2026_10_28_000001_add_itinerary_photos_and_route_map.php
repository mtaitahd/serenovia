<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('tour_packages', function (Blueprint $table): void { $table->json('itinerary_days')->nullable(); $table->boolean('route_map_enabled')->default(false); }); }
    public function down(): void { Schema::table('tour_packages', function (Blueprint $table): void { $table->dropColumn(['itinerary_days','route_map_enabled']); }); }
};
