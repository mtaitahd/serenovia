<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tour_package_tour_category', function (Blueprint $table): void {
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['tour_package_id', 'tour_category_id']);
        });

        Schema::create('accommodations', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tier', 30)->default('mid_range');
            $table->string('location', 180)->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('published');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'tier']);
        });

        Schema::create('tour_package_accommodations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('tier', 30);
            $table->timestamps();
            $table->unique(['tour_package_id', 'day_number', 'tier'], 'tour_day_tier_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_package_accommodations');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('tour_package_tour_category');
        Schema::dropIfExists('tour_categories');
    }
};
