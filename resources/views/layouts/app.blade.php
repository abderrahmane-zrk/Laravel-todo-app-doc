<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة البريد و المهام</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- تضمين CSS عبر Vite --}}
   @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    {{-- ✅ Header احترافي --}}
    <header x-data="{ open: false }" class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        {{-- الشعار --}}
        <div class="text-2xl font-extrabold text-blue-600 flex items-center gap-2">
            📋 <span>إدارة البريد والمهام</span>
        </div>

        {{-- زر الموبايل --}}
        <div class="lg:hidden">
            <button @click="open = !open" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- حساب المستخدم --}}
        @auth
            <div class="hidden lg:flex items-center gap-4">
                <div class="relative" x-data="{ userMenu: false }">
                    <button @click="userMenu = !userMenu" class="flex items-center gap-1 text-sm text-gray-700 hover:text-blue-600 font-semibold">
                        👤 {{ Auth::user()->name }}
                        <svg class="w-4 h-4 transform" :class="{ 'rotate-180': userMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="userMenu" @click.away="userMenu = false"
                         class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-50">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                🔓 تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
    </div>

    {{-- الشريط الجانبي للموبايل --}}
    <nav x-show="open" class="lg:hidden bg-gray-800 text-white px-4 py-4 space-y-2">
        <a href="{{ route('tasks.index') }}" class="block hover:text-yellow-300">📝 مهامي الخاصة</a>
        <a href="{{ route('tasks.assigned') }}" class="block hover:text-yellow-300">📥 البريد/المهام الوارد</a>
        <a href="{{ route('attachments.index') }}" class="block hover:text-yellow-300">📎 الوثائق/المستندات</a>

        @can('assign_task')
            <a href="{{ route('tasks.assign') }}" class="block hover:text-yellow-300">🧭 متابعة و إسناد البريد و المهام </a>
        @endcan

        @role('admin')
            <a href="{{ route('admin.users.index') }}" class="block hover:text-yellow-300">👥 إدارة المستخدمين</a>
            <a href="{{ route('admin.permissions') }}" class="block hover:text-yellow-300">🔐 إدارة الصلاحيات</a>
        @endrole

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="block w-full text-left text-red-600 hover:text-red-400">🔓 تسجيل الخروج</button>
            </form>
        @endauth
    </nav>

    {{-- الشريط العادي --}}
    <nav class="hidden lg:flex bg-gray-900 text-white px-6 py-3 justify-between items-center">
        <div class="text-lg font-bold tracking-wide text-yellow-300 flex items-center gap-2">
            🧭 <span>التنقل السريع</span>
        </div>

        <div class="flex items-center gap-6 text-sm">
            <a href="{{ route('tasks.index') }}" class="hover:text-yellow-300 transition">📝 مهامي الخاصة</a>
            <a href="{{ route('tasks.assigned') }}" class="hover:text-yellow-300 transition">📥 البريد/المهام الوارد</a>
            <a href="{{ route('attachments.index') }}" class="hover:text-yellow-300 transition">📎 الوثائق/المستندات</a>

            @can('assign_task')
                <a href="{{ route('tasks.assign') }}" class="hover:text-yellow-300 transition">🧭 متابعة و إسناد البريد و المهام </a>
            @endcan

            @role('admin')
                <a href="{{ route('admin.users.index') }}" class="hover:text-yellow-300 transition">👥 إدارة المستخدمين</a>
                <a href="{{ route('admin.permissions') }}" class="hover:text-yellow-300 transition">🔐 إدارة الصلاحيات</a>
            @endrole
        </div>
    </nav>
</header>



    <!-- المحتوى الديناميكي -->
    <main>
        @yield('content')
    </main>

    {{-- تضمين سكربتات من الأقسام الداخلية إن وجدت --}}
    @stack('scripts')
</body>
</html>
