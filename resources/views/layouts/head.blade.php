<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ ! empty($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

<script>
    try {
        document.documentElement.dataset.theme = localStorage.getItem('theme') === 'light' ? 'light' : 'dark';
    } catch (e) {
        document.documentElement.dataset.theme = 'dark';
    }
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
