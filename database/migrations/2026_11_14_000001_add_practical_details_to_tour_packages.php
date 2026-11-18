<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('tour_code', 40)->nullable()->unique();
            $table->json('practical_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropUnique(['tour_code']);
            $table->dropColumn(['tour_code', 'practical_details']);
        });
    }
};
