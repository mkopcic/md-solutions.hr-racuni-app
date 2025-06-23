<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Dobrodošli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-blue-50 to-white text-gray-800 antialiased">

    <div class="min-h-screen flex flex-col justify-between">

        <!-- Hero Section -->
        <section class="flex flex-col items-center justify-center flex-1 px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-blue-700 mb-4">
                Aplikacija za obrtnike i fakture
            </h1>
            <p class="text-lg md:text-xl text-gray-600 max-w-2xl mb-8">
                Jednostavno izdavanje računa, vođenje knjige prometa i pregled poslovanja.
            </p>

            @auth
                <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl shadow hover:bg-blue-700 transition">
                    Idi na Dashboard
                </a>
            @else
                <div class="flex gap-4 flex-wrap justify-center">
                    <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-500 text-white font-semibold rounded-xl hover:bg-blue-600 transition">
                        Prijava
                    </a>
                    <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition">
                        Registracija
                    </a>
                </div>
            @endauth
        </section>

        <!-- Footer -->
        <footer class="text-center text-sm text-gray-500 py-6">
            &copy; {{ now()->year }} Mellon Fakture. Sva prava pridržana.
        </footer>

    </div>

</body>
</html>
