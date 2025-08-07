@extends('layouts.app')

@section('content')
    <div class="p-4">
        <h2 class="text-2xl font-bold mb-4">📤 إسناد المهام </h2>

        

        <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>


    <!-- إشعار -->
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs">
        
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
    </div>

    <!-- Modal التعليق -->
    <div id="comment-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl p-6 max-w-xl w-full relative">
            <button onclick="document.getElementById('comment-modal').classList.add('hidden')" class="absolute top-2 left-2 text-gray-600 text-xl">&times;</button>
            <h2 class="text-lg font-bold mb-4"> ملاحظات او تعليقات </h2>
            <div id="comment-modal-content" class="prose max-h-96 overflow-y-auto"></div>
        </div>
    </div>

        <!-- نموذج إنشاء وتوجيه مهمة جديدة -->
        <form id="task-form" class="flex flex-col md:flex-row items-center gap-2 mb-6">
            <div>
                <label for="title" class="block font-medium">عنوان المهمة</label>
                <input type="text" id="title" name="title" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label for="reference" class="block font-medium">مرجع المهمة</label>
                <input type="text" id="reference" name="reference" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label for="comment" class="block font-medium">ملاحظات</label>
                <input id="comment" type="hidden" name="comment">
                <trix-editor input="comment" class="trix-content"></trix-editor>
            </div>

            <div>
                <label for="assigned_users" class="block font-medium">المستخدمون الموجه إليهم</label>
                <select id="assigned_users" name="assigned_users[]" class="w-full border rounded px-3 py-2" multiple required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">➕  إنشاء مهمة جديدة  </button>
        </form>

        <!-- أزرار التحكم الجماعي -->
        <div class="mt-6 flex flex-wrap gap-2">
            <button id="bulk-delete" class="bg-red-600 text-white px-3 py-1 rounded">🗑️ حذف المحدد</button>
            <button class="status-btn bg-yellow-500 text-white px-3 py-1 rounded" data-status="pending">↩️ إعادة للوضع الجديد</button>
            <button class="status-btn bg-blue-500 text-white px-3 py-1 rounded" data-status="in_progress">⚙️ قيد المعالجة</button>
            <button class="status-btn bg-green-600 text-white px-3 py-1 rounded" data-status="done">✅ مكتملة</button>
        </div>

        <!-- جدول المهام -->
        <div class="mt-4">
            <div id="task-table"></div>
        </div>
    </div>

    <!-- Modal لتوجيه مهمة موجودة -->
    <div id="assign-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded shadow max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4">📤 توجيه مهمة</h3>
            <input type="hidden" id="assign_task_id">
            <label class="block mb-2">اختر المستخدمين:</label>
            <select id="assign_users" class="w-full border rounded px-3 py-2 mb-4" multiple>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <div class="flex justify-end gap-2">
                <button id="assign-cancel" class="px-3 py-1 border rounded">إلغاء</button>
                <button id="assign-confirm" class="bg-blue-600 text-white px-4 py-1 rounded">📤 توجيه</button>
            </div>
        </div>
    </div>

    <!-- نافذة Modal للملفات -->
    <!-- Modal لإرفاق ملفات -->
    <div id="attachments-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden">

        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">

            <!-- زر إغلاق -->
            <button onclick="closeAttachmentsModal()"
                    class="absolute top-2 left-2 text-gray-500 hover:text-gray-800 text-xl">
                ×
            </button>

            <h2 class="text-xl font-semibold mb-4 text-center">إدارة المرفقات</h2>

            <!-- نموذج رفع الملفات -->
            <form id="upload-form" enctype="multipart/form-data" class="mb-6">
                @csrf
                <input type="hidden" name="task_id" id="attachment_task_id">

                <div class="flex items-center space-x-4">
                    <input type="file" name="attachment" id="attachment-input" multiple
                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0
                                file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 transition">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded shadow">
                        رفع
                    </button>
                </div>
            </form>

            <!-- قائمة الملفات المرفقة -->
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">الملفات المرفقة:</h3>

                <div id="attachmentList">
                    <!-- يتم ملؤها بـ JS عند الفتح أو بعد الرفع -->
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const notify = (message, type = 'success') => {
            const n = document.getElementById('notification');
            n.textContent = message;
            n.className = `p-3 rounded mb-4 ${type === 'success' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'}`;
            n.classList.remove('hidden');
            setTimeout(() => n.classList.add('hidden'), 3000);
        };


       // ثابتات الحالة
        const statusLabels = {
            pending: "🕓 جديدة",
            in_progress: "⏳ قيد المعالجة",
            done: "✅ مكتملة"
        };

        // خيارات الفلترة للهيدر - ثابتة
        const statusFilterOptions = {
            "": "الكل",
            "pending": "🕓 جديدة",
            "in_progress": "⏳ قيد المعالجة",
            "done": "✅ مكتملة"
        };


        const table = new Tabulator("#task-table", {
            ajaxURL: "{{ route('tasks.assignedbypermitted.fetch') }}",
            layout: "fitColumns",
            selectable: true,
            columns: [
                {formatter:"rowSelection", titleFormatter:"rowSelection", hozAlign:"center", headerSort:false, width:50},
                {title: "مهمة", field: "title"},
                {title: "مرجع", field: "reference"},
                {
                    title: "الحالة",
                    field: "status",
                    hozAlign: "center",
                    headerSort: true,
                    headerFilter: "list",
                    headerFilterParams: {
                        clearable: true,
                        values: statusFilterOptions // ← ثابتة
                    },
                    formatter: cell => {
                        const val = cell.getValue();
                        return statusLabels[val] || val;
                    }
                },

                {
                    title: "📆 أنشئت في", field: "created_at", headerSort: true, headerFilter: "input",
                    formatter: cell => new Date(cell.getValue()).toLocaleString()
                },
                {
                    title: "📅 اكتملت في", field: "completed_at", headerSort: true, headerFilter: "input",
                    formatter: cell => cell.getValue() ? new Date(cell.getValue()).toLocaleString() : "-"
                },
                {
                    title: "ملاحظات او تعليقات", 
                    formatter: function(cell, formatterParams) {
                        return "<button class='view-comment-btn relative inline-flex items-center bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full hover:bg-blue-200 transition'>عرض</button>";
                    },
                    cellClick: function(e, cell) {
                        const commentHtml = cell.getRow().getData().comment || "لا يوجد تعليق";
                        document.getElementById('comment-modal-content').innerHTML = commentHtml;
                        document.getElementById('comment-modal').classList.remove('hidden');
                    }
                },         
                {
                    title: "مرفقات",
                    field: "attachments_count",
                    hozAlign: "center",
                    headerSort: false,
                    width: 150,
                    formatter: function (cell) {
                        const taskId = cell.getRow().getData().id;
                        const count = cell.getValue();

                        const badge = count > 0
                            ? `<span class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-blue bg-red-500 rounded-full">${count}</span>`
                            : '';

                        return `
                            <button onclick="openAttachmentsModal(${taskId})"
                                class="relative inline-flex items-center bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full hover:bg-blue-200 transition">
                                <span class="mr-1">📎 مرفقات</span>
                                ${badge}
                            </button>
                        `;
                    }
                },
                {
                    title: "📤", 
                    formatter: () => '📤', hozAlign: "center", cellClick: (e, cell) => {
                    document.getElementById('assign_task_id').value = cell.getRow().getData().id;
                    document.getElementById('assign-modal').classList.remove('hidden');
                    }
                }
            ]
        });

        

        document.getElementById('task-form').addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(e.target);

            // إضافة التوكن داخل الـ formData
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const res = await fetch('/tasks', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    notify('تمت إضافة المهمة بنجاح');
                    e.target.reset();
                    table.replaceData();
                } else {
                    notify(data.message || 'حدث خطأ ما', 'error');
                }
            } catch (err) {
                console.error(err);
                notify('فشل الاتصال بالسيرفر', 'error');
            }
        });


        document.getElementById('bulk-delete').addEventListener('click', async () => {
            const ids = table.getSelectedData().map(r => r.id);
            if (!ids.length) return;

            const res = await fetch('{{ route("tasks.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ids})
            });

            const data = await res.json();
            if (res.ok) {
                notify('تم حذف المهام المحددة');
                table.replaceData();
            } else {
                notify(data.message || 'حدث خطأ في الحذف', 'error');
            }
        });


        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const status = btn.dataset.status;
                const ids = table.getSelectedData().map(r => r.id);
                if (!ids.length) return;

                const res = await fetch('{{ route("tasks.bulk-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ids, status})
                });

                const data = await res.json();
                if (res.ok) {
                    notify('تم تحديث حالة المهام');
                    table.replaceData();
                } else {
                    notify(data.message || 'حدث خطأ', 'error');
                }
            });
        });


        document.getElementById('assign-cancel').addEventListener('click', () => {
            document.getElementById('assign-modal').classList.add('hidden');
        });

        document.getElementById('assign-confirm').addEventListener('click', async () => {
            const taskId = document.getElementById('assign_task_id').value;
            const users = Array.from(document.getElementById('assign_users').selectedOptions).map(opt => opt.value);

            const res = await fetch(`/tasks/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({task_id: taskId, assigned_users: users})
            });

            const data = await res.json();
            if (res.ok) {
                notify('تم توجيه المهمة بنجاح');
                document.getElementById('assign-modal').classList.add('hidden');
            } else {
                notify(data.message || 'فشل في التوجيه', 'error');
            }
        });

        //
        // تحميل الملفات - فتح مودل
        let currentTaskId = null;

        function openAttachmentsModal(taskId) {
            currentTaskId = taskId;
            document.getElementById("attachments-modal").classList.remove("hidden");
            loadAttachments(taskId);
        }

        function closeAttachmentsModal() {
            document.getElementById("attachments-modal").classList.add("hidden");
        }


        function loadAttachments(taskId) {
            fetch(`{{ url('/tasks') }}/${taskId}/attachments`)
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('attachmentList');
                    list.innerHTML = '';

                    if (data.length === 0) {
                        list.innerHTML = `<p class="text-gray-500 text-sm">لا توجد ملفات مرفقة بعد.</p>`;
                        return;
                    }

                    const ul = document.createElement('ul');
                    ul.className = 'space-y-2';

                    data.forEach(file => {
                        const sizeKB = file.size / 1024;
                        const sizeFormatted = sizeKB > 1024
                            ? (sizeKB / 1024).toFixed(2) + ' MB'
                            : sizeKB.toFixed(2) + ' KB';

                        const ext = file.original_name.split('.').pop().toLowerCase();
                        const createdAt = new Date(file.created_at).toLocaleString('ar-DZ');

                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between bg-gray-100 p-2 rounded shadow-sm';

                        li.innerHTML = `
                            <div>
                                <p class="font-semibold text-gray-800">${file.original_name}</p>
                                <p class="text-sm text-gray-600">
                                    النوع: ${ext} |
                                    الحجم: ${sizeFormatted} |
                                    أضيف في: ${createdAt}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <a href="${file.url}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                    معاينة
                                </a>
                                <button onclick="deleteAttachment(${file.id})"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    🗑 حذف
                                </button>
                            </div>
                        `;

                        ul.appendChild(li);
                    });

                    list.appendChild(ul);
                });
        }

        

        const uploadForm = document.getElementById('upload-form');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const input = document.getElementById('attachment-input');
                if (!input.files.length || !currentTaskId) return;

                const formData = new FormData();
                formData.append('attachment', input.files[0]);

                fetch(`{{ url('/tasks') }}/${currentTaskId}/attachments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        loadAttachments(currentTaskId);
                    } else {
                        alert(data.message || "فشل في رفع الملف");
                    }
                });
            });
        }


        function deleteAttachment(id) {
            if (!confirm('هل أنت متأكد من حذف هذا الملف؟')) return;

            fetch(`{{ url('/attachments') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadAttachments(currentTaskId);
                }
            });
        }

        // جعل الدوال متاحة عالمياً لأننا نستخدمها في onclick داخل HTML
        window.openAttachmentsModal = openAttachmentsModal;
        window.closeAttachmentsModal = closeAttachmentsModal;
        window.deleteAttachment = deleteAttachment;

    });
</script>
@endpush

