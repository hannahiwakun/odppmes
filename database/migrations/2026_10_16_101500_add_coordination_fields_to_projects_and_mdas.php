<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('nature')->nullable();
            $table->string('geographical_coverage')->nullable();
            $table->string('initiative_status')->nullable();
            $table->text('objectives')->nullable();
            $table->text('key_activities')->nullable();
            $table->string('state_priority')->nullable();
            $table->string('funding_institution')->nullable();
            $table->string('assistance_nature')->nullable();
            $table->string('funding_arrangement')->nullable();
            $table->date('agreement_date')->nullable();
            $table->string('funding_currency', 10)->nullable();
            $table->decimal('approved_value', 18, 2)->nullable();
            $table->decimal('partner_contribution', 18, 2)->nullable();
            $table->decimal('federal_contribution', 18, 2)->nullable();
            $table->decimal('state_contribution', 18, 2)->nullable();
            $table->decimal('other_contributions', 18, 2)->nullable();
            $table->decimal('counterpart_required', 18, 2)->nullable();
            $table->decimal('counterpart_paid', 18, 2)->nullable();
            $table->decimal('funds_drawn', 18, 2)->nullable();
            $table->decimal('cumulative_expenditure', 18, 2)->nullable();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->date('financial_as_of')->nullable();
            $table->string('financial_source')->nullable();
            $table->string('direct_beneficiaries')->nullable();
            $table->string('indirect_beneficiaries')->nullable();
            $table->string('beneficiary_categories')->nullable();
            $table->boolean('beneficiaries_are_estimates')->default(false);
            $table->text('expected_outcomes')->nullable();
            $table->string('programme_officer')->nullable();
            $table->string('programme_officer_designation')->nullable();
            $table->string('programme_officer_phone', 50)->nullable();
            $table->string('programme_officer_email')->nullable();
            $table->string('verification_status')->nullable();
            $table->date('return_submitted_on')->nullable();
        });

        Schema::table('mdas', function (Blueprint $table) {
            $table->string('focal_name')->nullable();
            $table->string('focal_designation')->nullable();
            $table->string('focal_department')->nullable();
            $table->string('focal_phone', 50)->nullable();
            $table->string('focal_email')->nullable();
            $table->string('focal_supervisor')->nullable();
            $table->string('alternate_name')->nullable();
            $table->string('alternate_phone', 50)->nullable();
            $table->string('alternate_email')->nullable();
        });

        Schema::create('project_mda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mda_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'mda_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_mda');

        Schema::table('mdas', function (Blueprint $table) {
            $table->dropColumn([
                'focal_name',
                'focal_designation',
                'focal_department',
                'focal_phone',
                'focal_email',
                'focal_supervisor',
                'alternate_name',
                'alternate_phone',
                'alternate_email',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'nature',
                'geographical_coverage',
                'initiative_status',
                'objectives',
                'key_activities',
                'state_priority',
                'funding_institution',
                'assistance_nature',
                'funding_arrangement',
                'agreement_date',
                'funding_currency',
                'approved_value',
                'partner_contribution',
                'federal_contribution',
                'state_contribution',
                'other_contributions',
                'counterpart_required',
                'counterpart_paid',
                'funds_drawn',
                'cumulative_expenditure',
                'exchange_rate',
                'financial_as_of',
                'financial_source',
                'direct_beneficiaries',
                'indirect_beneficiaries',
                'beneficiary_categories',
                'beneficiaries_are_estimates',
                'expected_outcomes',
                'programme_officer',
                'programme_officer_designation',
                'programme_officer_phone',
                'programme_officer_email',
                'verification_status',
                'return_submitted_on',
            ]);
        });
    }
};
