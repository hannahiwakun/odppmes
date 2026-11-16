<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <p class="mb-5 text-sm text-gray-700">Sign in with the email address and password issued to you.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full border-gray-300 focus:border-green-700 focus:ring-green-700"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="remember_me" class="inline-flex min-h-11 items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-700 focus:ring-green-700" name="remember">
                <span class="text-sm text-gray-700">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="dpmo-auth-link min-h-11 inline-flex items-center text-sm" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit" class="dpmo-auth-submit sm:w-auto sm:min-w-[8rem]">
                {{ __('Log in') }}
            </button>
        </div>
    </form>
</x-guest-layout>
