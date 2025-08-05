@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="text-2xl font-bold text-gray-800">لوحة تحكم المشرف</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- >إدارة المستخدمين 👥--}}
        <a href="{{ route('admin.users.index') }}"
           class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition border border-gray-200">
            <h2 class="text-lg font-semibold text-blue-600">إدارة المستخدمين 👥</h2>
            <p class="text-gray-600 text-sm mt-1">واجهة >إدارة المستخدمين 👥</p>
        </a>

        

        {{-- رابط register --}}
        <a href="{{ route('register') }}"
           class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition border border-gray-200">
            <h2 class="text-lg font-semibold text-blue-600">register- إنشاء مستخدم جديد</h2>
            <p class="text-gray-600 text-sm mt-1">واجهة لتسجيل مستخدم جديد يدويًا</p>
        </a>

        {{-- إدارة الصلاحيات --}}
        <a href="{{ route(name: 'admin.permissions') }}"
           class="block p-4 bg-white rounded-xl shadow hover:shadow-md transition border border-gray-200">
            <h2 class="text-lg font-semibold text-green-600">🔐 إدارة الصلاحيات</h2>
            <p class="text-gray-600 text-sm mt-1">إعطاء أو إزالة صلاحيات للمستخدمين</p>
        </a>

    </div>
</div>
@endsection
