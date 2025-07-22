<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تطبيق المهام</title>
    
    {{-- تضمين CSS عبر Vite --}}
   @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <!-- رأس الصفحة -->
    <header class="mb-8 text-center">
        <h2 class="text-2xl font-bold text-red-600">إدارة المهام</h2>
    </header>

    <!-- المحتوى الديناميكي -->
    <main>
        @yield('content')
    </main>

    {{-- تضمين سكربتات من الأقسام الداخلية إن وجدت --}}
    @stack('scripts')
</body>
</html>
