@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">🔐 إدارة الصلاحيات</h2>

    {{-- ✅ إشعار --}}
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs z-50">
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
        <span id="notification-msg" class="text-sm"></span>
    </div>

    {{-- ✅ نموذج إنشاء صلاحية --}}
    <form id="create-permission-form" class="bg-white p-4 rounded shadow mb-6 flex flex-col md:flex-row items-center gap-4 border border-gray-100">
        <input type="text" name="name" id="permission-name" placeholder="اسم الصلاحية"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>

        <select name="role" id="permission-role"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
            <option value="">🔘 بدون ربط بدور</option>
            @foreach (\Spatie\Permission\Models\Role::all() as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
        </select>

        <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg shadow transition">➕ إنشاء</button>
    </form>

    {{-- ✅ زر الحذف --}}
    <button id="delete-permissions-btn" class="mb-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">
        🗑️ حذف المحددين
    </button>

    {{-- ✅ جدول الصلاحيات --}}
    <div id="permissions-table" class="bg-white rounded shadow mb-6"></div>

    {{-- ✅ جدول المستخدمين المرتبطين بالصلاحية --}}
    <div id="users-by-permission" class="hidden bg-white rounded shadow p-4">
        <h3 class="text-lg font-semibold mb-4">👥 المستخدمون المرتبطون بالصلاحية: <span id="permission-title" class="text-blue-600"></span></h3>
        <div id="users-table"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
document.addEventListener('DOMContentLoaded', () => {

    const showNotification = (msg, type = 'success') => {
        const box = document.getElementById('notification');
        const msgBox = document.getElementById('notification-msg');
        box.classList.remove('bg-green-600', 'bg-red-600');
        box.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
        msgBox.innerText = msg;
        box.classList.remove('hidden');
        setTimeout(() => box.classList.add('hidden'), 5000);
    };

    // ✅ جدول الصلاحيات
    const permissionTable = new Tabulator("#permissions-table", {
        ajaxURL: "{{ route('admin.permissions.fetch') }}",
        layout: "fitColumns",
        selectable: true,
        placeholder: "لا توجد صلاحيات حالياً",
        columns: [
            { title: "✅", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 50 },
            {
                title: "📛 اسم الصلاحية",
                field: "name",
                headerFilter: "input",
                formatter: function(cell) {
                    const name = cell.getValue();
                    return `<button class="text-blue-600 hover:underline" onclick="showUsersForPermission('${name}')">${name}</button>`;
                }
            },
            { title: "🛡️ Guard", field: "guard_name", hozAlign: "center" },
            { title: "🎭 مرتبط بـ Role", field: "roles", formatter: "html", hozAlign: "center" },
            { title: "📅 تاريخ الإنشاء", field: "created_at", hozAlign: "center" }
        ],
        

    });

    // ✅ جدول المستخدمين المرتبطين بصلاحية
    const usersTable = new Tabulator("#users-table", {
        layout: "fitColumns",
        placeholder: "لا يوجد مستخدمون",
        columns: [
            { title: "👤 الاسم", field: "name" },
            { title: "📧 البريد", field: "email" },
            { title: "📅 تاريخ الإسناد", field: "assigned_at" }
        ]
    });

    // ✅ إنشاء صلاحية
    document.getElementById('create-permission-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('permission-name').value;
        const role = document.getElementById('permission-role').value;

        fetch("{{ route('admin.permissions.create') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, role })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ تم إنشاء الصلاحية');
                this.reset();
                permissionTable.setData();
            } else {
                showNotification(data.message || '❌ حدث خطأ', 'error');
            }
        })
        .catch(() => showNotification('❌ فشل في إنشاء الصلاحية', 'error'));
    });

    // ✅ حذف صلاحيات متعددة
    document.getElementById('delete-permissions-btn').addEventListener('click', function () {
        const selected = permissionTable.getSelectedData();
        const names = selected.map(row => row.name);

        if (names.length === 0) return showNotification("❗ حدد صلاحيات أولاً", 'error');
        if (!confirm("هل أنت متأكد من حذف الصلاحيات المحددة؟")) return;

        fetch("{{ route('admin.permissions.deleteMultiple') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ names })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification("✅ تم حذف الصلاحيات");
                permissionTable.setData();
                document.getElementById('users-by-permission').classList.add('hidden');
            } else {
                showNotification("❌ فشل في الحذف", 'error');
            }
        });
    });

    window.showUsersForPermission = function(permissionName) {
        document.getElementById('permission-title').innerText = permissionName;

        fetch(`{{ route('admin.permissions.users', ':permission') }}`.replace(':permission', permissionName))
            .then(res => res.json())
            .then(data => {
                usersTable.setData(data);
                document.getElementById('users-by-permission').classList.remove('hidden');
            });
    };

});
</script>
@endpush
