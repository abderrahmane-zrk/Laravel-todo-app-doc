@extends('layouts.app')

@section('content')

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


    {{-- إضافة مهمة --}}
    <form id="task-form" class="flex flex-col md:flex-row items-center gap-2 mb-6">
        <input type="text" name="title" id="title" placeholder="اسم المهمة"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
        <input type="text" name="reference" id="reference" placeholder="المرجع"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
      <div class="mt-2">
            <label for="comment" class="block text-sm font-medium text-gray-700">   ملاحظات او تعليقات </label>
            <input id="comment" type="hidden" name="comment">
            <trix-editor input="comment" class="trix-content bg-white border rounded-md mt-1"></trix-editor>
      </div>

            <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition">➕ إضافة</button>
    
    </form>

    {{-- أزرار العمليات --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" data-status="pending"
            class="status-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">🔁 إعادة
            كجديدة </button>
        <button type="button" data-status="in_progress"
            class="status-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow transition">⏳ قيد
            المعالجة</button>
        <button type="button" data-status="done"
            class="status-btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">✅ تم
            الإنجاز</button>
        <button id="delete-btn"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition">🗑️ حذف المحدد</button>
    </div>

    {{-- جدول Tabulator --}}
    <div id="task-table" class="bg-white rounded-lg shadow overflow-x-auto"></div>
    
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




    <style>
    /* تحسين شكل جدول Tabulator باستخدام Tailwind */
    #task-table .tabulator {
        @apply rounded-xl border border-gray-200 shadow;
    }

    #task-table .tabulator-header {
        @apply bg-gray-100 text-gray-700 font-semibold;
    }

    #task-table .tabulator-col {
        @apply text-center text-sm;
    }

    #task-table .tabulator-row {
        @apply hover:bg-gray-50 transition;
    }

    #task-table .tabulator-cell {
        @apply text-center text-gray-800;
    }

    #task-table .tabulator-placeholder {
        @apply text-center text-gray-400 py-4;
    }

    #task-table .tabulator-row.tabulator-selected {
        @apply bg-blue-100;
    }
</style>


@endsection

@push('scripts')
    

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        
        // إضافة مهمة
        document.getElementById('task-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const reference = document.getElementById('reference').value;
            const comment = document.getElementById('comment').value; // ✅ قراءة محتوى Trix

            fetch('{{ route("tasks.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ title, reference, comment }) // ✅ إرسال أيضًا
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification("تمت إضافة المهمة بنجاح ✅");
                    this.reset();
                    refreshTaskList();
                } else {
                    showNotification("فشل في الإضافة ❌: " + (data.message || ''));
                }
            });
        });


        const notification = document.getElementById('notification');

        // إشعار Tailwind
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const color = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white z-50 ${color}`;
            notification.innerText = message;
            notification.classList.remove('hidden');
            setTimeout(() => notification.classList.add('hidden'), 5000);
        }

        // إعداد الجدول
        function refreshTaskList() {
            fetch('{{ route("tasks.index") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    const uniqueStatuses = [...new Set(data.tasks.map(task => task.status))];

                    const statusLabels = {
                        pending: "🕓 جديدة",
                        in_progress: "⏳ قيد المعالجة",
                        done: "✅ مكتملة"
                    };

                    const statusFilterOptions = {
                        "": "الكل",
                    };

                    uniqueStatuses.forEach(status => {
                        statusFilterOptions[status] = statusLabels[status] || status;
                    });

                    if (window.taskTable) {
                        window.taskTable.destroy();
                    }

                    window.taskTable = new Tabulator("#task-table", {
                        layout: "fitColumns",
                        selectable: true,
                        placeholder: "لا توجد مهام حالياً",
                        data: data.tasks,
                        columns: [
                            { title: "📌", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 50 },
                            { title: "المهمة", field: "title", headerSort: true, headerFilter: "input" },
                            { title: "🔖 المرجع", field: "reference", headerSort: false, headerFilter: "input" },
                            {
                                title: "الحالة", field: "status", hozAlign: "center", headerSort: true,
                                headerFilter: "list",
                                headerFilterParams: {
                                    clearable: true,
                                    values: statusFilterOptions
                                },
                                formatter: cell => {
                                    const val = cell.getValue();
                                    if (val === 'pending') return "🕓 جديدة";
                                    if (val === 'in_progress') return "⏳ قيد المعالجة";
                                    if (val === 'done') return "✅ مكتملة";
                                    return val;
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
                        ],
                    });
                }
            });
        }


        // إرجاع الـ IDs المحددة
        function getSelectedTaskIds() {
            return taskTable.getSelectedData().map(task => task.id);
        }

        // تغيير الحالة
        document.querySelectorAll('.status-btn').forEach(button => {
            button.addEventListener('click', function () {
                const status = this.dataset.status;
                const ids = getSelectedTaskIds();
                if (ids.length === 0) return showNotification('الرجاء تحديد مهام أولاً', 'error');

                fetch('{{ route("tasks.bulk-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids, status })
                }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showNotification("تم تحديث الحالة بنجاح 🔄");
                        refreshTaskList();
                    }
                });
            });
        });

        // حذف المهام المحددة
        document.getElementById('delete-btn').addEventListener('click', function () {
            const ids = getSelectedTaskIds();
            if (ids.length === 0) return showNotification('حدد المهام التي تريد حذفها', 'error');

            fetch('{{ route("tasks.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids })
            }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification("تم حذف المهام المحددة 🗑️");
                    refreshTaskList();
                }
            });
        });

        // تحميل أولي
        refreshTaskList();

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

        

        document.getElementById('upload-form').addEventListener('submit', function (e) {
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
