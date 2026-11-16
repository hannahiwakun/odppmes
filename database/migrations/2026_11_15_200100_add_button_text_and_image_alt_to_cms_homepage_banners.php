<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_homepage_banners', function (Blueprint $table) {
            $table->string('image_alt', 255)
                ->nullable()
                ->after('image_media_id');
            $table->string('button_text', 120)
                ->nullable()
                ->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('cms_homepage_banners', function (Blueprint $table) {
            $table->dropColumn(['image_alt', 'button_text']);
        });
    }
};
