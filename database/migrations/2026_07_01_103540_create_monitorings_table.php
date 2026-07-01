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
    Schema::create('monitorings', function (Blueprint $table) {

        $table->id();

        $table->foreignId('project_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->date('monitoring_date');

        $table->integer('progress_before')->default(0);

        $table->integer('progress_after')->default(0);

        $table->enum('status', [
            'Planning',
            'Ongoing',
            'Completed',
            'Suspended',
            'Cancelled'
        ]);

        $table->text('findings')->nullable();

        $table->text('challenges')->nullable();

        $table->text('recommendations')->nullable();

        $table->string('photo')->nullable();

        $table->decimal('latitude',10,7)->nullable();

        $table->decimal('longitude',10,7)->nullable();

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitorings');
    }
};
