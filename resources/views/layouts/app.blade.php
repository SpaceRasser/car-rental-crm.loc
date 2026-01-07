<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100">
    @include('layouts.navigation')

    @isset($header)
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $header }}
        </div>
    </header>
    @endisset

    <main>
        {{ $slot }}
    </main>
</div>

@livewireScripts
<script>
    const formatPhone = (value) => {
        let digits = value.replace(/\D/g, '');

        if (digits.startsWith('8')) {
            digits = '7' + digits.slice(1);
        }

        if (digits.startsWith('7') && digits.length >= 11) {
            const parts = [
                digits.slice(0, 1),
                digits.slice(1, 4),
                digits.slice(4, 7),
                digits.slice(7, 9),
                digits.slice(9, 11),
            ];
            return `+${parts[0]} (${parts[1]}) ${parts[2]}-${parts[3]}-${parts[4]}`;
        }

        if (digits.length > 0) {
            return `+${digits}`;
        }

        return value;
    };

    const formatLicense = (value) => {
        const cleaned = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        return cleaned;
    };

    const handleSelectFilter = (input) => {
        const select = document.getElementById(input.dataset.selectTarget);
        if (!select) {
            return;
        }

        const term = input.value.toLowerCase();
        Array.from(select.options).forEach((option, index) => {
            if (index === 0 && option.value === '') {
                option.hidden = false;
                return;
            }

            const text = option.textContent.toLowerCase();
            option.hidden = term !== '' && !text.includes(term);
        });
    };

    const handleMask = (input) => {
        const type = input.dataset.mask;
        if (!type) {
            return;
        }

        if (type === 'phone') {
            input.value = formatPhone(input.value);
        }

        if (type === 'email') {
            input.value = input.value.trim().toLowerCase();
        }

        if (type === 'license') {
            input.value = formatLicense(input.value);
        }
    };

    document.addEventListener('input', (event) => {
        const input = event.target;
        if (input.matches('[data-select-target]')) {
            handleSelectFilter(input);
        }

        if (input.matches('[data-mask]')) {
            handleMask(input);
        }
    });

    document.addEventListener('blur', (event) => {
        const input = event.target;
        if (input.matches('[data-mask]')) {
            handleMask(input);
        }
    }, true);
</script>
</body>
</html>
