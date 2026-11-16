<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_workflow_events', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 100);
            $table->unsignedBigInteger('content_id');
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['content_type', 'content_id', 'created_at'], 'cms_workflow_content_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_workflow_events');
    }
};
