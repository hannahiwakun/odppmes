<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_enquiries', function (Blueprint $table) {
            $table->id();
            $table->timestamp('received_at');
            $table->string('name');
            $table->string('email');
            $table->string('organisation')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('source_ip', 45)->nullable();
            $table->string('source_file')->nullable();
            $table->char('source_hash', 64)->unique();
            $table->string('status', 32)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('received_at');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_enquiries');
    }
};
