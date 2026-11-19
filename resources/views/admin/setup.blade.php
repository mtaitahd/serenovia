@php($publicBasePath = preg_replace('#/public$#', '', rtrim(request()->getBasePath(), '/')))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First admin setup · Serenovia</title>
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
            <h1>Your journeys.<br><em>Your story.</em></h1>
            <p>Set up the first administrator account to manage the Serenovia website and its travel stories.</p>
            <small>MOSHI · TANZANIA</small>
        </section>
        <section class="auth-panel">
            <form class="auth-card" method="POST" action="{{ route('admin.setup.store') }}">
                @csrf
                <span class="eyebrow">ONE-TIME SETUP</span>
                <h2>Create your admin account</h2>
                <p>This form is available only until the first administrator is created. Use a password with at least 12 characters.</p>
                @if ($errors->any())<div class="auth-error">{{ $errors->first() }}</div>@endif
                <div class="field"><label for="name">YOUR NAME</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
                <div class="field"><label for="email">EMAIL ADDRESS</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
                <div class="field"><label for="password">PASSWORD</label><input id="password" type="password" name="password" required minlength="12" autocomplete="new-password"></div>
                <div class="field"><label for="password_confirmation">CONFIRM PASSWORD</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></div>
                <button class="button" type="submit">Create administrator </button>
                <div class="admin-help">Your password is stored securely as a hash.</div>
            </form>
        </section>
    </main>
</body>
</html>
