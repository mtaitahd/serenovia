<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table): void {
            $table->json('package_details')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('tour_packages', fn (Blueprint $table) => $table->dropColumn('package_details'));
    }
};
