<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('slug', 140)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $countries = [
            'Tanzania', 'Kenya', 'Uganda', 'Rwanda', 'Ethiopia', 'Seychelles',
            'Mauritius', 'South Africa', 'Botswana', 'Zambia', 'Zimbabwe', 'Egypt',
        ];

        foreach ($countries as $index => $name) {
            DB::table('countries')->insertOrIgnore([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
