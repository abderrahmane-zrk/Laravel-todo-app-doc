@extends('layouts.app')

@section('content')
<div class="p-4 max-w-xl mx-auto">
    <h2 class="text-xl font-bold mb-4">إضافة مستخدم جديد</h2>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">الاسم</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">البريد الإلكتروني</label>
            <input type="email" name="email" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">كلمة المرور</label>
            <input type="password" name="password" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">الدور</label>
            <select name="role" class="w-full border p-2 rounded" required>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            حفظ المستخدم
        </button>
    </form>
</div>
@endsection
