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
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/admin.css">
    <link rel="stylesheet" href="{{ $publicBasePath }}/css/ui-typography.css?v=1">
</head>
<body>
    <main class="auth-page">
        <section class="auth-side">
            <a class="admin-brand" href="{{ route('home') }}"><span class="admin-emblem">S</span><span><b>SERENOVIA</b><small>AFRICA SAFARIS</small></span></a>
            <h1>Make room<br>for <em>wonder.</em></h1>
            <p>Manage every detail of the journeys and stories you share with the world.</p>
            <small>MOSHI · TANZANIA</small>
        </section>
        <section class="auth-panel">
            <form class="auth-card" method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <span class="eyebrow">ADMIN DESK</span>
                <h2>Welcome back</h2>
                <p>Sign in to manage your Serenovia website.</p>
                @if ($errors->any())<div class="auth-error">{{ $errors->first() }}</div>@endif
                <div class="field"><label for="email">EMAIL ADDRESS</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></div>
                <div class="field"><label for="password">PASSWORD</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
                <label class="checkbox-row"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button class="button" type="submit">Sign in to admin <span>↗</span></button>
                <div class="admin-help"><a href="{{ route('home') }}">Return to Serenovia</a></div>
            </form>
        </section>
    </main>
</body>
</html>
