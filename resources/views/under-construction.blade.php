<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>We'll be right back - EnglishBaro</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(rgba(10, 15, 35, .55), rgba(10, 15, 35, .55)), url('{{ asset('images/under-construction-bg.jpg') }}') center / cover no-repeat;
            color: #fff;
            text-align: center;
        }
        .card { max-width: 32rem; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .375rem .875rem;
            border-radius: 9999px;
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        h1 { margin: 1.25rem 0 .75rem; font-size: 1.875rem; font-weight: 700; }
        p { margin: 0; color: rgba(255,255,255,.75); font-size: 1rem; line-height: 1.6; }
        .dot { width: .5rem; height: .5rem; border-radius: 9999px; background: #fbbf24; animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
        .admin-login {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            padding: .5rem 1rem;
            border-radius: .5rem;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .25);
            color: #fff;
            font-size: .8125rem;
            font-weight: 600;
            text-decoration: none;
            backdrop-filter: blur(4px);
        }
        .admin-login:hover { background: rgba(255, 255, 255, .22); }
        .admin-login-form { position: fixed; top: 1.25rem; right: 1.25rem; margin: 0; }
        .admin-login-form button { font-family: inherit; cursor: pointer; }
    </style>
</head>
<body>
    @auth
        <form method="POST" action="{{ route('logout') }}" class="admin-login-form">
            @csrf
            <button type="submit" class="admin-login">Logout ({{ auth()->user()->name }})</button>
        </form>
    @else
        <a href="{{ route('login') }}" class="admin-login">Admin Login</a>
    @endauth

    <div class="card">
        <span class="badge"><span class="dot"></span> Under Construction</span>
        <h1>We'll be right back</h1>
        <p>EnglishBaro is getting some updates behind the scenes. Please check back shortly.</p>
    </div>
</body>
</html>
