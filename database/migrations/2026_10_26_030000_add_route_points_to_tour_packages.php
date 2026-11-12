<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('starting_point', 255)->nullable()->after('destination_id');
            $table->string('ending_point', 255)->nullable()->after('starting_point');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn(['starting_point', 'ending_point']);
        });
    }
};
