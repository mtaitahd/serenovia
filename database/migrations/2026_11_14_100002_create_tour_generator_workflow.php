<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_gen_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename');
            $table->string('sha256', 64);
            $table->string('status', 24);
            $table->text('error')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
        Schema::create('tour_gen_import_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('tour_gen_import_batches')->cascadeOnDelete();
            $table->string('source_locator');
            $table->json('raw_payload');
            $table->json('mapped_payload');
            $table->json('validation_messages')->nullable();
            $table->string('status', 24);
            $table->timestamps();
        });
        Schema::table('tour_gen_day_blocks', function (Blueprint $table) {
            $table->foreignId('source_record_id')->nullable()->constrained('tour_gen_import_records')->restrictOnDelete();
        });
        Schema::create('tour_gen_generation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('filters');
            $table->unsignedInteger('requested_count');
            $table->string('status', 24);
            $table->json('metrics')->nullable();
            $table->timestamps();
        });
        Schema::create('tour_gen_run_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('tour_gen_generation_runs')->restrictOnDelete();
            $table->foreignId('tour_package_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('fingerprint', 64)->unique();
            $table->json('source_snapshot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_gen_run_results');
        Schema::dropIfExists('tour_gen_generation_runs');
        Schema::table('tour_gen_day_blocks', fn (Blueprint $table) => $table->dropConstrainedForeignId('source_record_id'));
        Schema::dropIfExists('tour_gen_import_records');
        Schema::dropIfExists('tour_gen_import_batches');
    }
};
