@extends('layouts.app')

@section('content')

  <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>


<div class="p-4">
    <h2 class="text-2xl font-bold mb-4">📤 توجيه المهام</h2>

    <!-- إشعارات -->
    <div id="notification" class="hidden mb-4 p-2 rounded text-white"></div>

    <!-- نموذج إضافة/تعديل المهمة -->
    <form id="task-form" class="space-y-4 mb-6">
        <input type="hidden" name="id" id="task-id">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="title" id="title" placeholder="عنوان المهمة" class="form-input w-full rounded border-gray-300" required>
            <input type="text" name="reference" id="reference" placeholder="مرجع المهمة" class="form-input w-full rounded border-gray-300">
            <div class="mt-2">
                    <label for="comment" class="block text-sm font-medium text-gray-700">   ملاحظات او تعليقات </label>
                    <input id="comment" type="hidden" name="comment">
                    <trix-editor input="comment" class="trix-content bg-white border rounded-md mt-1"></trix-editor>
            </div>
            <select name="assigned_users[]" id="assigned_users" class="form-select w-full rounded border-gray-300" multiple>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <input id="comment" type="hidden" name="comment">
            <trix-editor input="comment" class="trix-content bg-white p-2 rounded border-gray-300"></trix-editor>
        </div>

        <div>
            <input type="file" id="attachments" name="attachments[]" multiple class="block mt-2">
        </div>

        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">➕ توجيه مهمة جديدة</button>
        </div>
    </form>

    <!-- أزرار الحذف وتغيير الحالة -->
    <div class="flex space-x-2 mb-4">
        <button id="delete-selected" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">🗑️ حذف المحدد</button>
        <button data-status="new" class="change-status bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded">🔄 حالة: جديدة</button>
        <button data-status="in_progress" class="change-status bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded">🔄 حالة: قيد المعالجة</button>
        <button data-status="completed" class="change-status bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded">🔄 حالة: مكتملة</button>
    </div>

    <!-- جدول المهام -->
    <div id="tasks-table" class="w-full"></div>

    <!-- نافذة عرض المرفقات -->
    <div id="attachments-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white p-4 rounded w-2/3 max-h-[80vh] overflow-y-auto">
            <h3 class="text-lg font-bold mb-2">📎 مرفقات المهمة</h3>
            <div id="attachments-list" class="space-y-2"></div>
            <button onclick="document.getElementById('attachments-modal').classList.add('hidden')" class="mt-4 bg-gray-600 text-white px-3 py-1 rounded">إغلاق</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const taskForm = document.getElementById('task-form');
        const notification = document.getElementById('notification');
        const attachmentsModal = document.getElementById('attachments-modal');
        let selectedTaskIds = [];

        // إشعارات
        function showNotification(message, type = 'success') {
            notification.textContent = message;
            notification.className = `mb-4 p-2 rounded text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            notification.classList.remove('hidden');
            setTimeout(() => notification.classList.add('hidden'), 3000);
        }

        // تهيئة جدول Tabulator
        const table = new Tabulator("#tasks-table", {
            ajaxURL: "{{ route('tasks.assignedbypermitted.fetch') }}",
            selectable: true,
            layout: "fitColumns",
            columns: [
                {formatter:"rowSelection", titleFormatter:"rowSelection", headerSort:false, width:50, hozAlign:"center", cellClick:function(e, cell){ cell.getRow().toggleSelect(); }},
                {title: "📌 العنوان", field: "title"},
                {title: "📁 المرجع", field: "reference"},
                {title: "🗓️ الإنشاء", field: "created_at"},
                {title: "✅ الإكمال", field: "completed_at"},
                {title: "📝 الحالة", field: "status"},
                {title: "📎 عدد المرفقات", field: "attachments_count", hozAlign:"center"},
                {
                    title: "📤 توجيه",
                    formatter: () => `<button class="bg-indigo-600 text-white px-2 py-1 rounded assign-task">📤</button>`,
                    cellClick: function(e, cell) {
                        const task = cell.getRow().getData();
                        fillForm(task); // تعبئة النموذج لإعادة التوجيه
                    }
                },
                {
                    title: "📂 مرفقات",
                    formatter: () => `<button class="bg-gray-700 text-white px-2 py-1 rounded">عرض</button>`,
                    cellClick: function(e, cell) {
                        const task = cell.getRow().getData();
                        fetch(`/tasks/${task.id}/attachments`)
                            .then(res => res.json())
                            .then(data => {
                                const list = document.getElementById('attachments-list');
                                list.innerHTML = '';
                                data.forEach(att => {
                                    list.innerHTML += `
                                        <div class="border p-2 flex justify-between items-center">
                                            <a href="/storage/${att.filename}" target="_blank">${att.original_name}</a>
                                            <form method="POST" action="/attachments/${att.id}" onsubmit="return confirm('تأكيد حذف المرفق؟')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600">حذف</button>
                                            </form>
                                        </div>`;
                                });
                                attachmentsModal.classList.remove('hidden');
                            });
                    }
                }
            ],
            rowClick: function(e, row){
                const data = row.getData();
                fillForm(data);
            }
        });

        // تعبئة النموذج
        function fillForm(task){
            document.getElementById('task-id').value = task.id;
            document.getElementById('title').value = task.title;
            document.getElementById('reference').value = task.reference;
            document.querySelector("trix-editor").editor.loadHTML(task.comment || '');
            document.getElementById('assigned_users').value = task.assigned_users || [];
        }

        // حفظ / تعديل مهمة
        taskForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(taskForm);
            const taskId = formData.get('id');

            if (!taskId) {
                showNotification("يرجى اختيار مهمة موجودة لتوجيهها", 'error');
                return;
            }

            fetch(`/tasks/assign`, {

                method: "POST",
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: formData,
            })
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
            .then(() => {
                showNotification("تم توجيه المهمة بنجاح");
                table.replaceData();
                taskForm.reset();
                document.querySelector("trix-editor").editor.loadHTML('');
            })
            .catch(() => showNotification("حدث خطأ أثناء التوجيه", 'error'));
        };


        // حذف متعدد
        document.getElementById('delete-selected').onclick = function() {
            const ids = table.getSelectedData().map(t => t.id);
            if (!ids.length || !confirm('تأكيد الحذف؟')) return;
            fetch('{{ route("tasks.deleteMultiple") }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ids})
            }).then(() => {
                showNotification("تم حذف المهام المحددة");
                table.replaceData();
            });
        };

        // تغيير الحالة
        document.querySelectorAll('.change-status').forEach(btn => {
            btn.onclick = () => {
                const status = btn.dataset.status;
                const ids = table.getSelectedData().map(t => t.id);
                if (!ids.length) return;
                fetch('/tasks/bulk-update', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({ids, status})
                }).then(() => {
                    showNotification("تم تغيير الحالة");
                    table.replaceData();
                });
            };
        });

    });
</script>
@endpush
