@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in · Serenovia</title>
    <link rel="icon" type="image/png" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="apple-touch-icon" href="{{ $publicBasePath }}/images/serenovia-mark.png?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin-login.css?v=1">
</head>
<body>
    <main class="login-card">
        <aside class="login-brand">
            <a href="{{ route('home') }}">
                <img src="{{ $publicBasePath }}/images/serenovia-mark.png?v=1" alt="" width="148" height="148">
                <strong>Serenovia Africa Safaris</strong>
            </a>
            <small>© {{ now()->year }} Serenovia Africa Safaris</small>
        </aside>
        <section class="login-panel" aria-labelledby="login-title">
            <header><h1 id="login-title">Admin login</h1><p>Sign in to manage your Serenovia website.</p></header>
            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                @if ($errors->any())<p class="login-error" role="alert">{{ $errors->first() }}</p>@endif
                <label for="email">Account email address</label>
                <div class="login-input">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 20v-2a7 7 0 0 1 14 0v2z"/></svg></span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                </div>
                <label for="password">Password</label>
                <div class="login-input">
                    <span aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                </div>
                <label class="login-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
                <button type="submit">Sign in <span aria-hidden="true">↗</span></button>
            </form>
            <footer><a href="{{ route('home') }}">Return to Serenovia</a></footer>
        </section>
    </main>
</body>
</html>
