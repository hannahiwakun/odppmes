<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_news_articles', function (Blueprint $table) {
            $table->foreignId('featured_image_media_id')
                ->nullable()
                ->after('excerpt')
                ->constrained('cms_media_assets')
                ->nullOnDelete();
            $table->string('featured_image_alt', 255)
                ->nullable()
                ->after('featured_image_media_id');
            $table->string('category', 100)
                ->default('Announcements')
                ->after('featured_image_alt');
            $table->index('category');
        });

        DB::table('cms_news_articles')
            ->whereNull('category')
            ->orWhere('category', '')
            ->update(['category' => 'Announcements']);
    }

    public function down(): void
    {
        Schema::table('cms_news_articles', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropConstrainedForeignId('featured_image_media_id');
            $table->dropColumn(['featured_image_alt', 'category']);
        });
    }
};
