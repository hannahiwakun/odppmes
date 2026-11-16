<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            // User who performed the action
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Example: Created, Updated, Deleted
            $table->string('action');

            // Example: Project, Monitoring, User
            $table->string('module');

            // Description of what happened
            $table->text('description')->nullable();

            // User IP address
            $table->string('ip_address')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};