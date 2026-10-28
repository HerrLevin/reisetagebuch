@extends('layouts.auth')

@section('title', __('auth.login.title'))

@section('content')
    <h2 class="mb-2 text-center text-2xl font-semibold">{{ __('auth.login.title') }}</h2>

    @if (request('loggedOut') === 'true')
        <div class="alert alert-info my-4">{{ __('auth.logged_out') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <fieldset class="fieldset">
            <label class="fieldset-label" for="email">{{ __('auth.login.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input w-full @error('email') input-error @enderror" required autofocus autocomplete="username">
            @error('email')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="fieldset mt-4">
            <label class="fieldset-label" for="password">{{ __('auth.login.password') }}</label>
            <input id="password" type="password" name="password"
                   class="input w-full @error('password') input-error @enderror" required autocomplete="current-password">
            @error('password')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <div class="mt-4 flex items-center justify-between">
            <label class="fieldset-label">
                <input type="checkbox" name="remember" value="1" class="checkbox">
                {{ __('auth.login.remember_me') }}
            </label>
            <a href="/forgot-password" class="link">{{ __('auth.login.forgot_password') }}</a>
        </div>

        <button type="submit" class="btn btn-primary mt-12 w-full">{{ __('auth.login.title') }}</button>
    </form>

    <div class="mt-4 text-center">
        @if (config('app.registration') || config('app.invite.enabled'))
            {{ __('auth.login.no_account') }}
            <a href="{{ route('register') }}" class="link">{{ __('auth.register.title') }}</a>
        @else
            {{ __('auth.login.no_register') }}
        @endif
    </div>
@endsection
