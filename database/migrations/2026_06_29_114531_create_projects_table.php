<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {

            $table->id();

            // MDA responsible for the project
            $table->foreignId('mda_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Development Partner (optional)
            $table->foreignId('development_partner_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Basic Information
            $table->string('project_title');
            $table->string('project_code')->nullable();

            // Classification
            $table->string('sector');
            $table->string('lga');
            $table->string('ward')->nullable();

            // Financial Information
            $table->decimal('budget', 18, 2)->default(0);
            $table->string('funding_source')->nullable();

            // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Project Status
            $table->enum('status', [
                'Planning',
                'Ongoing',
                'Completed',
                'Suspended',
                'Cancelled'
            ])->default('Planning');

            // Percentage Progress
            $table->integer('progress')->default(0);

            // GPS Coordinates
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Description
            $table->longText('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};