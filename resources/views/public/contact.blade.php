@extends('layouts.public')

@section('title', 'Contact Us — DPMO')

@section('content')
@include('public.partials.page-hero', [
    'title' => 'Contact us',
    'lede' => 'Send an enquiry to the Development Partners Management Office. Official address, telephone and email will be shown here when the Office confirms them.',
])

<section class="public-section">
    <div class="public-wrap public-grid public-grid-2">
        <div>
            <article class="public-card">
                <h3>Office address</h3>
                <p>{{ $officeContact->address ?? $site['contact']['address_note'] }}</p>
            </article>
            <article class="public-card" style="margin-top:0.75rem;">
                <h3>Telephone</h3>
                <p>{{ $officeContact->phone ?? $site['contact']['phone_note'] }}</p>
            </article>
            <article class="public-card" style="margin-top:0.75rem;">
                <h3>Email</h3>
                <p>{{ $officeContact->email ?? $site['contact']['email_note'] }}</p>
            </article>
            <article class="public-card" style="margin-top:0.75rem;">
                <h3>Hours</h3>
                <p>{{ $officeContact->hours ?? $site['contact']['hours_note'] }}</p>
            </article>
        </div>

        <div>
            @if (session('success'))
                <div class="public-alert public-alert-ok" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="public-alert public-alert-bad" role="alert">Please correct the highlighted fields and send the form again.</div>
            @endif

            <form method="POST" action="{{ route('public.contact.submit') }}" class="public-form public-card" novalidate>
                @csrf
                <div class="sr-only-hp" aria-hidden="true" inert>
                    <input type="text"
                           name="website"
                           value=""
                           tabindex="-1"
                           autocomplete="off"
                           aria-hidden="true">
                </div>

                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')<p class="public-error">{{ $message }}</p>@enderror

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email')<p class="public-error">{{ $message }}</p>@enderror

                <label for="organisation">Organisation <span style="font-weight:500;color:var(--odp-muted);">(optional)</span></label>
                <input type="text" id="organisation" name="organisation" value="{{ old('organisation') }}" maxlength="255">
                @error('organisation')<p class="public-error">{{ $message }}</p>@enderror

                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255">
                @error('subject')<p class="public-error">{{ $message }}</p>@enderror

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required minlength="20" maxlength="2000">{{ old('message') }}</textarea>
                @error('message')<p class="public-error">{{ $message }}</p>@enderror

                <button type="submit" class="public-btn public-btn-primary">Send enquiry</button>
            </form>
        </div>
    </div>
</section>
@endsection
