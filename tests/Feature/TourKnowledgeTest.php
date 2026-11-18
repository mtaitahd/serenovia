<?php

namespace Tests\Feature;

use App\Support\TourGenerator\RouteCompatibilityValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class TourKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_schema_can_be_rolled_back_and_reapplied_in_isolation(): void
    {
        $tourColumns = Schema::getColumnListing('tour_packages');
        $migration = require database_path('migrations/2026_11_14_100001_create_tour_generator_knowledge.php');

        $migration->down();
        $this->assertFalse(Schema::hasTable('tour_gen_day_blocks'));
        $migration->up();
        $this->assertTrue(Schema::hasTable('tour_gen_day_block_routes'));
        $this->assertSame($tourColumns, Schema::getColumnListing('tour_packages'));
    }

    public function test_a_verified_route_can_be_used_but_reversed_legs_and_incompatible_stays_cannot(): void
    {
        $day = [
            'status' => 'approved',
            'start_location_id' => 1,
            'end_location_id' => 2,
            'overnight_location_id' => 2,
            'position_rule' => 'first',
            'day_payload' => ['title' => 'Forest walk'],
            'routes' => [['status' => 'approved', 'from_location_id' => 1, 'to_location_id' => 2]],
            'activities' => [['status' => 'approved', 'location_id' => 2]],
            'stays' => [['status' => 'approved', 'location_id' => 2]],
        ];
        $validator = new RouteCompatibilityValidator;

        $this->assertSame([], $validator->errors([$day], 1, 1));

        $day['routes'][0]['from_location_id'] = 2;
        $day['routes'][0]['to_location_id'] = 1;
        $day['stays'][0]['location_id'] = 1;
        $errors = $validator->errors([$day], 1, 1);
        $this->assertContains('Day 1: the route legs do not connect in the approved direction.', $errors);
        $this->assertContains('Day 1: an accommodation is incompatible with the overnight stop.', $errors);
    }
}
