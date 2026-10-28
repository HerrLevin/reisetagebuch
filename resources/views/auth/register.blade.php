@extends('layouts.auth')

@section('title', __('auth.register.title'))

@section('content')
    <h2 class="mb-2 text-center text-2xl font-semibold">{{ __('auth.register.title') }}</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="invite" value="{{ old('invite', $invite) }}">

        <fieldset class="fieldset">
            <label class="fieldset-label" for="name">{{ __('auth.register.name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="input w-full @error('name') input-error @enderror" required autofocus autocomplete="name">
            @error('name')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="fieldset mt-4">
            <label class="fieldset-label" for="username">{{ __('auth.register.username') }}</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}"
                   class="input w-full @error('username') input-error @enderror" required autocomplete="username">
            @error('username')<p class="text-error text-sm">{{ $message }}</p>@enderror
            <p class="text-sm opacity-65">{{ __('auth.register.username_immutable') }}</p>
        </fieldset>

        <fieldset class="fieldset mt-4">
            <label class="fieldset-label" for="email">{{ __('auth.register.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input w-full @error('email') input-error @enderror" required autocomplete="email">
            @error('email')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="fieldset mt-4">
            <label class="fieldset-label" for="password">{{ __('auth.register.password') }}</label>
            <input id="password" type="password" name="password"
                   class="input w-full @error('password') input-error @enderror" required autocomplete="new-password">
            @error('password')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="fieldset mt-4">
            <label class="fieldset-label" for="password_confirmation">{{ __('auth.register.confirm_password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="input w-full" required autocomplete="new-password">
            @error('invite')<p class="text-error text-sm">{{ $message }}</p>@enderror
        </fieldset>

        <div class="mt-4 flex items-center justify-end">
            <a href="{{ route('login') }}" class="link">{{ __('auth.register.have_account') }}</a>
            <button type="submit" class="btn btn-primary ms-4">{{ __('auth.register.title') }}</button>
        </div>
    </form>
@endsection
