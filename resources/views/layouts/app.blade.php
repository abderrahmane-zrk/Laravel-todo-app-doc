<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق المهام</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- تضمين CSS عبر Vite --}}
   @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    {{-- Header --}}
    <header class="bg-gray-100 shadow p-4 mb-4 flex justify-between items-center">
        <div class="text-lg font-bold text-gray-700">
            تطبيق المهام
        </div>

        @auth
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">👤 {{ Auth::user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        @endauth
    </header>



    <!-- المحتوى الديناميكي -->
    <main>
        @yield('content')
    </main>

    {{-- تضمين سكربتات من الأقسام الداخلية إن وجدت --}}
    @stack('scripts')
</body>
</html>
