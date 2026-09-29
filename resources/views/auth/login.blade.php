<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in – {{ config('app.name', 'Reisetagebuch') }}</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #191e24;
            color: #f1f1f1;
        }
        form {
            width: 100%;
            max-width: 320px;
            padding: 2rem;
            border-radius: 0.75rem;
            background: #232830;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }
        h1 { font-size: 1.25rem; margin: 0 0 0.25rem; }
        p.subtitle { font-size: 0.8rem; color: #9aa4b2; margin: 0 0 1.5rem; }
        label { display: block; font-size: 0.875rem; margin-bottom: 0.25rem; }
        input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #3a4150;
            background: #191e24;
            color: inherit;
            box-sizing: border-box;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: none;
            border-radius: 0.375rem;
            background: #4f7cff;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { background: #3d68f0; }
        .error {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin: -0.75rem 0 1rem;
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <h1>Admin sign in</h1>
        <p class="subtitle">For accessing internal tools (Horizon, Telescope). Not the app login.</p>

        @if ($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Sign in</button>
    </form>
</body>
</html>
