<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_gen_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('canonical_key', 120)->index();
            $table->string('name');
            $table->string('country_code', 2);
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $this->approval($table);
        });

        Schema::create('tour_gen_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_id')->constrained('tour_gen_locations')->restrictOnDelete();
            $table->string('canonical_key', 120)->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $this->approval($table);
        });

        Schema::create('tour_gen_route_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_location_id')->constrained('tour_gen_locations')->restrictOnDelete();
            $table->foreignId('to_location_id')->constrained('tour_gen_locations')->restrictOnDelete();
            $table->string('transport_mode', 60);
            $table->unsignedSmallInteger('min_travel_minutes')->nullable();
            $table->unsignedSmallInteger('max_travel_minutes')->nullable();
            $table->json('constraints')->nullable();
            $table->index(['from_location_id', 'to_location_id', 'status'], 'tour_gen_route_lookup');
            $this->approval($table);
        });

        Schema::create('tour_gen_day_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_tour_package_id')->nullable()->constrained('tour_packages')->nullOnDelete();
            $table->string('source_day_key')->nullable();
            foreach (['start', 'end', 'overnight'] as $role) {
                $table->foreignId($role.'_location_id')->nullable($role === 'overnight')
                    ->constrained('tour_gen_locations')->restrictOnDelete();
            }
            $table->string('tour_type', 60);
            $table->string('position_rule', 30)->default('any');
            $table->json('day_payload');
            $table->index(['tour_type', 'start_location_id', 'status'], 'tour_gen_block_lookup');
            $this->approval($table);
        });

        foreach (['activities' => 'activity', 'routes' => 'route_rule'] as $relation => $reference) {
            Schema::create('tour_gen_day_block_'.$relation, function (Blueprint $table) use ($relation, $reference): void {
                $table->id();
                $table->foreignId('day_block_id')->constrained('tour_gen_day_blocks')->cascadeOnDelete();
                $target = $reference === 'activity' ? 'tour_gen_activities' : 'tour_gen_route_rules';
                $table->foreignId($reference.'_id')->constrained($target)->restrictOnDelete();
                $table->unsignedSmallInteger('position');
                $table->unique(['day_block_id', 'position'], 'tour_gen_'.$relation.'_position');
                $table->timestamps();
            });
        }

        Schema::create('tour_gen_accommodation_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_id')->constrained('tour_gen_locations')->restrictOnDelete();
            $table->foreignId('accommodation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tier', 30);
            $table->json('constraints')->nullable();
            $table->index(['location_id', 'tier', 'status'], 'tour_gen_stay_lookup');
            $this->approval($table);
        });
    }

    public function down(): void
    {
        foreach (['accommodation_rules', 'day_block_routes', 'day_block_activities', 'day_blocks', 'route_rules', 'activities', 'locations'] as $table) {
            Schema::dropIfExists('tour_gen_'.$table);
        }
    }

    private function approval(Blueprint $table): void
    {
        $table->string('status', 24)->default('pending');
        $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('approved_at')->nullable();
        $table->timestamps();
    }
};
