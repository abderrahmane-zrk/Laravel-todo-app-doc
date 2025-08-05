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

    {{-- ✅ Header احترافي --}}
    <header class="bg-white shadow p-4 flex justify-between items-center border-b border-gray-200">
        <div class="text-xl font-bold text-blue-600 flex items-center gap-2">
            📋 تطبيق المهام
        </div>

        @auth
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700">👤 {{ Auth::user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                        🔓 تسجيل الخروج
                    </button>
                </form>
            </div>
        @endauth
    </header>

    {{-- ✅ Navbar ديناميكي --}}
    <nav class="bg-gray-800 text-white px-6 py-3 flex justify-between items-center">
        <div class="text-lg font-semibold">
            🧭 القائمة
        </div>

        <div class="flex items-center gap-6 text-sm">
            <a href="{{ route('tasks.index') }}" class="hover:text-yellow-300 transition">📝 مهامي</a>
            <a href="{{ route('tasks.assigned') }}" class="hover:text-yellow-300 transition">📥 المهام الموجهة إليّ</a>
            <a href="{{ route('attachments.index') }}" class="hover:text-yellow-300 transition">📎 الوثائق</a>

            @role('admin')
                <a href="{{ route('admin.users.index') }}" class="hover:text-yellow-300 transition">👥 إدارة المستخدمين</a>
                <a href="{{ route('admin.permissions') }}" class="hover:text-yellow-300 transition">🔐 إدارة الصلاحيات</a>
            @endrole
        </div>
    </nav>




    <!-- المحتوى الديناميكي -->
    <main>
        @yield('content')
    </main>

    {{-- تضمين سكربتات من الأقسام الداخلية إن وجدت --}}
    @stack('scripts')
</body>
</html>
