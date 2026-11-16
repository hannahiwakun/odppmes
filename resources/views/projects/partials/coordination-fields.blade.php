@php
    $project = $project ?? null;
    $idPrefix = $project ? 'project-edit' : 'project';
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedMdas = collect(old('participating_mda_ids', $project ? $project?->participatingMdas->pluck('id')->all() : []))->map(fn ($id) => (string) $id);
    $selectedVerification = (string) old('verification_status', $project?->verification_status ?? '');
@endphp
<input type="hidden" name="coordination_form" value="1">

<div class="mt-8 border-t border-gray-200 pt-6">
    <h3 class="mb-1 text-lg font-bold text-green-900">Development register</h3>
    <p class="mb-4 text-sm text-gray-600">
        These details come from the MDA return. Leave a box empty when the ministry has not provided it yet.
        An empty box is kept as unknown. It is not treated as zero.
    </p>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-nature" class="mb-2 block font-semibold">
                Nature of initiative <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-nature"
                   name="nature"
                   value="{{ old('nature', $project?->nature ?? '') }}"
                   placeholder="Project, programme, grant, loan, technical assistance"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-coverage" class="mb-2 block font-semibold">
                Geographical coverage <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-coverage"
                   name="geographical_coverage"
                   value="{{ old('geographical_coverage', $project?->geographical_coverage ?? '') }}"
                   placeholder="State-wide, or name the LGAs and communities"
                   class="{{ $fieldClass }}">
        </div>
        <div class="md:col-span-2">
            <label for="{{ $idPrefix }}-participating-mdas" class="mb-2 block font-semibold">
                Other participating MDAs <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <select id="{{ $idPrefix }}-participating-mdas"
                    name="participating_mda_ids[]"
                    multiple
                    class="{{ $fieldClass }} min-h-32">
                @foreach($mdas as $mdaOption)
                    <option value="{{ $mdaOption->id }}" {{ $selectedMdas->contains((string) $mdaOption->id) ? 'selected' : '' }}>
                        {{ $mdaOption->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Hold Ctrl to select more than one. The lead MDA above is already recorded.</p>
        </div>
        <div class="md:col-span-2">
            <label for="{{ $idPrefix }}-objectives" class="mb-2 block font-semibold">
                Objectives <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="{{ $idPrefix }}-objectives"
                      name="objectives"
                      rows="3"
                      class="{{ $fieldClass }}">{{ old('objectives', $project?->objectives ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="{{ $idPrefix }}-activities" class="mb-2 block font-semibold">
                Key activities <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="{{ $idPrefix }}-activities"
                      name="key_activities"
                      rows="3"
                      class="{{ $fieldClass }}">{{ old('key_activities', $project?->key_activities ?? '') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label for="{{ $idPrefix }}-state-priority" class="mb-2 block font-semibold">
                State priority this supports <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-state-priority"
                   name="state_priority"
                   value="{{ old('state_priority', $project?->state_priority ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
    </div>

    <h3 class="mb-4 mt-8 text-lg font-bold text-green-900">Money</h3>
    <p class="mb-4 text-sm text-gray-600">Approved value, money released, and the state's own share are different figures. Record each one.</p>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-funding-institution" class="mb-2 block font-semibold">
                Funding institution, if different from the partner <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-funding-institution"
                   name="funding_institution"
                   value="{{ old('funding_institution', $project?->funding_institution ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-assistance-nature" class="mb-2 block font-semibold">
                Nature of assistance <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-assistance-nature"
                   name="assistance_nature"
                   value="{{ old('assistance_nature', $project?->assistance_nature ?? '') }}"
                   placeholder="Grant, loan, technical assistance"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-funding-arrangement" class="mb-2 block font-semibold">
                Funding arrangement <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-funding-arrangement"
                   name="funding_arrangement"
                   value="{{ old('funding_arrangement', $project?->funding_arrangement ?? '') }}"
                   placeholder="Grant agreement, financing agreement, MoU"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-agreement-date" class="mb-2 block font-semibold">
                Agreement date <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="date"
                   id="{{ $idPrefix }}-agreement-date"
                   name="agreement_date"
                   value="{{ old('agreement_date', isset($project) && $project?->agreement_date ? \Illuminate\Support\Carbon::parse($project?->agreement_date)->toDateString() : '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-funding-currency" class="mb-2 block font-semibold">
                Currency <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-funding-currency"
                   name="funding_currency"
                   value="{{ old('funding_currency', $project?->funding_currency ?? '') }}"
                   placeholder="NGN or USD"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-exchange-rate" class="mb-2 block font-semibold">
                Exchange rate, if a naira total is needed <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="number"
                   step="0.000001"
                   min="0"
                   id="{{ $idPrefix }}-exchange-rate"
                   name="exchange_rate"
                   value="{{ old('exchange_rate', $project?->exchange_rate ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        @foreach([
            'approved_value' => 'Total approved value',
            'partner_contribution' => 'Development partner contribution',
            'federal_contribution' => 'Federal Government contribution',
            'state_contribution' => 'Ondo State contribution',
            'other_contributions' => 'Other contributions',
            'counterpart_required' => 'Counterpart funding required',
            'counterpart_paid' => 'Counterpart funding paid',
            'funds_drawn' => 'Funds drawn down',
            'cumulative_expenditure' => 'Cumulative expenditure',
        ] as $field => $label)
            <div>
                <label for="{{ $idPrefix }}-{{ str_replace('_', '-', $field) }}" class="mb-2 block font-semibold">
                    {{ $label }} <span class="text-sm font-normal text-gray-500">Optional</span>
                </label>
                <input type="number"
                       step="0.01"
                       min="0"
                       id="{{ $idPrefix }}-{{ str_replace('_', '-', $field) }}"
                       name="{{ $field }}"
                       value="{{ old($field, $project?->{$field} ?? '') }}"
                       class="{{ $fieldClass }}">
            </div>
        @endforeach
        <div>
            <label for="{{ $idPrefix }}-financial-as-of" class="mb-2 block font-semibold">
                Date of these figures <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="date"
                   id="{{ $idPrefix }}-financial-as-of"
                   name="financial_as_of"
                   value="{{ old('financial_as_of', isset($project) && $project?->financial_as_of ? \Illuminate\Support\Carbon::parse($project?->financial_as_of)->toDateString() : '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-financial-source" class="mb-2 block font-semibold">
                Source of the financial figures <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-financial-source"
                   name="financial_source"
                   value="{{ old('financial_source', $project?->financial_source ?? '') }}"
                   placeholder="MDA finance unit or programme report"
                   class="{{ $fieldClass }}">
        </div>
    </div>

    <h3 class="mb-4 mt-8 text-lg font-bold text-green-900">Who should benefit</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-direct-beneficiaries" class="mb-2 block font-semibold">
                Direct beneficiaries <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-direct-beneficiaries"
                   name="direct_beneficiaries"
                   value="{{ old('direct_beneficiaries', $project?->direct_beneficiaries ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-indirect-beneficiaries" class="mb-2 block font-semibold">
                Indirect beneficiaries <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-indirect-beneficiaries"
                   name="indirect_beneficiaries"
                   value="{{ old('indirect_beneficiaries', $project?->indirect_beneficiaries ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-beneficiary-categories" class="mb-2 block font-semibold">
                Beneficiary groups <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-beneficiary-categories"
                   name="beneficiary_categories"
                   value="{{ old('beneficiary_categories', $project?->beneficiary_categories ?? '') }}"
                   placeholder="Women, youths, farmers, students"
                   class="{{ $fieldClass }}">
        </div>
        <div class="flex items-end">
            <div class="mb-2 flex items-center gap-2">
                <input type="checkbox"
                       id="{{ $idPrefix }}-beneficiaries-are-estimates"
                       name="beneficiaries_are_estimates"
                       value="1"
                       class="rounded border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2"
                       {{ old('beneficiaries_are_estimates', $project?->beneficiaries_are_estimates ?? false) ? 'checked' : '' }}>
                <label for="{{ $idPrefix }}-beneficiaries-are-estimates">
                    These beneficiary figures are estimates <span class="text-sm font-normal text-gray-500">Optional</span>
                </label>
            </div>
        </div>
        <div class="md:col-span-2">
            <label for="{{ $idPrefix }}-expected-outcomes" class="mb-2 block font-semibold">
                Expected outcomes <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="{{ $idPrefix }}-expected-outcomes"
                      name="expected_outcomes"
                      rows="3"
                      class="{{ $fieldClass }}">{{ old('expected_outcomes', $project?->expected_outcomes ?? '') }}</textarea>
        </div>
    </div>

    <h3 class="mb-4 mt-8 text-lg font-bold text-green-900">Officer and check</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-programme-officer" class="mb-2 block font-semibold">
                Programme officer <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-programme-officer"
                   name="programme_officer"
                   value="{{ old('programme_officer', $project?->programme_officer ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-programme-designation" class="mb-2 block font-semibold">
                Designation <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-programme-designation"
                   name="programme_officer_designation"
                   value="{{ old('programme_officer_designation', $project?->programme_officer_designation ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-programme-phone" class="mb-2 block font-semibold">
                Official phone <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="text"
                   id="{{ $idPrefix }}-programme-phone"
                   name="programme_officer_phone"
                   value="{{ old('programme_officer_phone', $project?->programme_officer_phone ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-programme-email" class="mb-2 block font-semibold">
                Official email <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="email"
                   id="{{ $idPrefix }}-programme-email"
                   name="programme_officer_email"
                   value="{{ old('programme_officer_email', $project?->programme_officer_email ?? '') }}"
                   class="{{ $fieldClass }}">
        </div>
        <div>
            <label for="{{ $idPrefix }}-verification-status" class="mb-2 block font-semibold">
                How sure is this record? <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isMEO())
                <select id="{{ $idPrefix }}-verification-status" name="verification_status" class="{{ $fieldClass }}">
                    <option value="" @selected($selectedVerification === '')>Not yet reviewed</option>
                    @foreach(['Reported','Partially verified','Verified','Awaiting clarification'] as $check)
                        <option value="{{ $check }}" @selected($selectedVerification === $check)>{{ $check }}</option>
                    @endforeach
                </select>
            @else
                <p id="{{ $idPrefix }}-verification-status" class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-gray-900">
                    {{ $selectedVerification !== '' ? $selectedVerification : 'Not yet reviewed' }}
                </p>
                <p class="mt-1 text-sm text-gray-600">Verification is set by the Development Partners Office. MDA Officers cannot mark a project as Verified.</p>
            @endif
        </div>
        <div>
            <label for="{{ $idPrefix }}-return-submitted-on" class="mb-2 block font-semibold">
                Date the MDA submitted this <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <input type="date"
                   id="{{ $idPrefix }}-return-submitted-on"
                   name="return_submitted_on"
                   value="{{ old('return_submitted_on', isset($project) && $project?->return_submitted_on ? \Illuminate\Support\Carbon::parse($project?->return_submitted_on)->toDateString() : '') }}"
                   class="{{ $fieldClass }}">
        </div>
    </div>
</div>
