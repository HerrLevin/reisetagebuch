@extends('layouts.auth')

@section('title', __('auth.authorize.title'))

@section('content')
    <h2 class="mb-4 text-center text-2xl font-semibold">{{ __('auth.authorize.title') }}</h2>

    {{-- The client name is chosen by whoever registered the app, so also show where the user is sent. --}}
    <p>{!! __('auth.authorize.message', ['client' => '<strong>'.e($client->name).'</strong>', 'user' => '<strong>@'.e($user->username).'</strong>']) !!}</p>
    @if (count($scopes))
        <p class="mt-4">{{ __('auth.authorize.scopes') }}</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($scopes as $scope)
                <li>{{ $scope->description }}</li>
            @endforeach
        </ul>
    @endif
    <p class="mt-2 text-sm opacity-65">{{ __('auth.authorize.redirect', ['redirect' => $request->redirect_uri]) }}</p>
    @if ($client->client_uri)
        {{-- Logo deliberately not shown: loading it would leak the user's IP to the client's server. --}}
        <p class="mt-2 flex flex-wrap gap-4 text-sm">
            <a href="{{ $client->client_uri }}" class="link" target="_blank" rel="noopener noreferrer">{{ __('auth.authorize.website') }}</a>
            <a href="{{ $client->policy_uri }}" class="link" target="_blank" rel="noopener noreferrer">{{ __('auth.authorize.privacy_policy') }}</a>
            <a href="{{ $client->tos_uri }}" class="link" target="_blank" rel="noopener noreferrer">{{ __('auth.authorize.terms') }}</a>
        </p>
    @endif

    <div class="mt-12 flex justify-end gap-4">
        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="btn">{{ __('auth.authorize.deny') }}</button>
        </form>
        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
            @csrf
            <input type="hidden" name="state" value="{{ $request->state }}">
            <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <button type="submit" class="btn btn-primary">{{ __('auth.authorize.approve') }}</button>
        </form>
    </div>
@endsection
