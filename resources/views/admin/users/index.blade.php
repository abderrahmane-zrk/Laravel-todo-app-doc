@extends('layouts.app')

@section('content')
<div class="p-4">


    <!-- ✅ Modal تعديل المستخدم -->
    <div id="edit-user-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full relative">
            <button onclick="closeEditUserModal()" class="absolute top-2 left-2 text-gray-600 text-xl">&times;</button>
            <h2 class="text-lg font-bold mb-4">تعديل المستخدم</h2>

            <form id="edit-user-form">
                <input type="hidden" id="edit_user_id">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="edit_name" placeholder="الاسم" required class="border rounded px-3 py-2">
                    <input type="email" id="edit_email" placeholder="البريد" required class="border rounded px-3 py-2">
                    <select id="edit_role" class="col-span-2 border rounded px-3 py-2">
                        <option value="user">مستخدم</option>
                        <option value="admin">مشرف</option>
                    </select>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">💾 تحديث</button>
                </div>
            </form>
        </div>
    </div>


    <!-- ✅ Modal لتعديل صلاحيات المستخدم -->
    <div id="permissions-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full relative">
            <button onclick="closePermissionsModal()" class="absolute top-2 left-2 text-gray-600 text-xl">&times;</button>
            <h2 class="text-lg font-bold mb-4">تعديل صلاحيات المستخدم</h2>

            <form id="permissions-form">
                <input type="hidden" id="permission_user_id">
                <div id="permissions-list" class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                    <!-- يتم ملؤها من JS -->
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow">💾 حفظ</button>
                </div>
            </form>
        </div>
    </div>


    {{-- إشعار --}}
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs">
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
    </div>

    {{-- نموذج إضافة مستخدم --}}
    <form id="user-form" class="flex flex-col md:flex-row items-center gap-2 mb-6">
        <input type="text" name="name" id="name" placeholder="اسم المستخدم"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>

        <input type="email" name="email" id="email" placeholder="البريد الإلكتروني"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>

        <input type="password" name="password" id="password" placeholder="كلمة المرور"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2" required>

        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="تأكيد كلمة المرور"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2" required>

        <select id="role" name="role"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
            <option value="user">مستخدم</option>
            <option value="admin">مشرف</option>
        </select>

        <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg shadow transition">➕ إضافة</button>
    </form>

    {{-- زر الحذف --}}
    @role('admin')
    <button id="delete-btn"
        class="mb-4 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition">
        🗑️ حذف المحددين
    </button>
    @endrole

    {{-- جدول المستخدمين --}}
    <div id="user-table" class="bg-white rounded-lg shadow overflow-x-auto"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const showNotification = (msg, type = 'success') => {
        const box = document.getElementById('notification');
        const color = type === 'success' ? 'bg-green-600' : 'bg-red-600';
        box.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white z-50 ${color}`;
        box.innerText = msg;
        box.classList.remove('hidden');
        setTimeout(() => box.classList.add('hidden'), 5000);
    };

    // 🟢 تحميل المستخدمين
    function refreshUserList() {
        fetch('{{ route("admin.users.fetch") }}')
            .then(res => res.json())
            .then(data => {
                if (window.userTable) userTable.destroy();

                window.userTable = new Tabulator("#user-table", {
                    layout: "fitColumns",
                    selectable: true,
                    placeholder: "لا يوجد مستخدمون حالياً",
                    data: data,
                    columns: [
                        { title: "✅", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 50 },
                        { title: "👤 الاسم", field: "name", headerFilter: "input" },
                        { title: "📧 البريد", field: "email", headerFilter: "input" },
                        { title: "🎭 الدور", field: "role", headerFilter: "input", hozAlign: "center" },
                        {
                            title: "🛡️ الصلاحيات",
                            field: "permissions",
                            hozAlign: "center",
                            formatter: function(cell, formatterParams) {
                                return `<button onclick="openPermissionsModal(${cell.getRow().getData().id})" 
                                            class="bg-gray-100 hover:bg-gray-200 text-sm px-3 py-1 rounded">⚙️ إدارة</button>`;
                            }
                        },
                        { title: "📅 تاريخ الإنشاء", field: "created_at", hozAlign: "center" , headerFilter: "input" },
                        {
                            title: "⚙️ تعديل ",
                            hozAlign: "center",
                            formatter: function(cell, formatterParams) {
                                const id = cell.getRow().getData().id;
                                return `
                                    <button onclick="editUser(${id})" 
                                        class="bg-yellow-100 hover:bg-yellow-200 text-sm px-3 py-1 rounded">✏️ تعديل</button>`;
                            }
                        },

                    ]
                });
            });
    }

    refreshUserList();

    // 🟢 إضافة مستخدم
    document.getElementById('user-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const payload = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value,
            role: document.getElementById('role').value,
        };

        fetch('{{ route("admin.users.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.errors) {
                const msg = Object.values(data.errors).flat().join(" - ");
                showNotification("❌ " + msg, 'error');
            } else if (data.success) {
                showNotification("✅ تم إضافة المستخدم");
                this.reset();
                refreshUserList();
            }
        }).catch(() => showNotification("❌ فشل في إضافة المستخدم", 'error'));
    });

    // 🟢 حذف مستخدمين
    document.getElementById('delete-btn')?.addEventListener('click', function () {
        const selectedIds = userTable.getSelectedData().map(row => row.id);
        if (selectedIds.length === 0) return showNotification("❗ حدد مستخدمين أولاً", 'error');

        if (!confirm("هل أنت متأكد من حذف المستخدمين المحددين؟")) return;

        fetch('{{ route("admin.users.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification("✅ تم حذف المستخدمين");
                refreshUserList();
            } else {
                showNotification("❌ فشل في الحذف", 'error');
            }
        });
    });

    function openPermissionsModal(userId) {

        document.getElementById('permission_user_id').value = userId;

       fetch(`{{ route('admin.permissions.data', ':id') }}`.replace(':id', userId))
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('permissions-list');
                list.innerHTML = '';

                data.allPermissions.forEach(permission => {
                    const checked = data.userPermissions.includes(permission) ? 'checked' : '';
                    list.innerHTML += `
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="permissions[]" value="${permission}" ${checked}>
                            <span>${permission}</span>
                        </label>
                    `;
                });

                document.getElementById('permissions-modal').classList.remove('hidden');
            });
    }

    function closePermissionsModal() {
        document.getElementById('permissions-modal').classList.add('hidden');
    }

    document.getElementById('permissions-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const userId = document.getElementById('permission_user_id').value;
        const formData = new FormData(this);
        const selectedPermissions = formData.getAll('permissions[]');
        fetch(`{{ route('admin.permissions.update', ':id') }}`.replace(':id', userId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ permissions: selectedPermissions })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification('✅ تم تحديث الصلاحيات');
                closePermissionsModal();
                refreshUserList();
            } else {
                showNotification('❌ حدث خطأ أثناء التحديث', 'error');
            }
        });
    });


    window.editUser = function (userId) {
        fetch(`/admin/users/${userId}/edit`)
            .then(res => res.json())
            .then(user => {
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_name').value = user.name;
                document.getElementById('edit_email').value = user.email;
                document.getElementById('edit_role').value = user.role;

                document.getElementById('edit-user-modal').classList.remove('hidden');
            });
    };

    window.closeEditUserModal = function () {
        document.getElementById('edit-user-modal').classList.add('hidden');
    };

    document.getElementById('edit-user-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const userId = document.getElementById('edit_user_id').value;
        const payload = {
            name: document.getElementById('edit_name').value,
            email: document.getElementById('edit_email').value,
            role: document.getElementById('edit_role').value,
        };

        fetch(`/admin/users/${userId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification("✅ تم تحديث المستخدم");
                closeEditUserModal();
                refreshUserList();
            } else {
                showNotification("❌ حدث خطأ في التحديث", 'error');
            }
        });
    });


    // ✅ مهم: تعريفها على النطاق العام
    window.openPermissionsModal = openPermissionsModal;
    window.closePermissionsModal = closePermissionsModal;


});
</script>
@endpush
