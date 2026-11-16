<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')
                ->constrained('monitorings')
                ->restrictOnDelete();
            $table->string('file_path');
            $table->string('file_type', 16);
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('caption')->nullable();
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_evidence');
    }
};
