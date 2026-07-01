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
    Schema::create('development_partners', function (Blueprint $table) {

        $table->id();

        $table->string('partner_name');
        $table->string('acronym')->nullable();

        $table->string('partner_type');

        $table->string('contact_person')->nullable();

        $table->string('email')->nullable();

        $table->string('phone')->nullable();

        $table->string('website')->nullable();

        $table->string('country')->default('Nigeria');

        $table->text('address')->nullable();

        $table->boolean('status')->default(true);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('development_partners');
    }
};
