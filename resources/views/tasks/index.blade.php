@extends('layouts.app')

@section('content')
    <!-- إشعار -->
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs">
        
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
    </div>

    {{-- إضافة مهمة --}}
    <form id="task-form" class="flex flex-col md:flex-row items-center gap-2 mb-6">
        <input type="text" name="title" id="title" placeholder="اسم المهمة"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
        <input type="text" name="reference" id="reference" placeholder="المرجع"
            class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition">➕ إضافة</button>
    </form>

    {{-- أزرار العمليات --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" data-status="pending"
            class="status-btn bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">🔁 إعادة
            تنشيط</button>
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
        let taskTable = new Tabulator("#task-table", {
            layout: "fitColumns",
            selectable: true,
            placeholder: "لا توجد مهام حالياً",
            columns: [
                { title: "📌", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 50 },
                { title: "المهمة", field: "title", headerSort: false },
                { title: "🔖 المرجع", field: "reference", headerSort: false },
                {
                    title: "الحالة", field: "status", hozAlign: "center", headerSort: false,
                    formatter: cell => {
                        const val = cell.getValue();
                        if (val === 'pending') return "🕓 نشطة";
                        if (val === 'in_progress') return "⏳ قيد المعالجة";
                        if (val === 'done') return "✅ مكتملة";
                        return val;
                    }
                },
                {
                    title: "📅 اكتملت في", field: "completed_at", headerSort: false,
                    formatter: cell => cell.getValue() ? new Date(cell.getValue()).toLocaleString() : "-"
                },
            ],
        });

        // تحميل المهام
        function refreshTaskList() {
            fetch('{{ route("tasks.index") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    taskTable.setData(data.tasks);
                }
            });
        }

        // إضافة مهمة
        document.getElementById('task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const title = document.getElementById('title').value;
            const reference = document.getElementById('reference').value;

            fetch('{{ route("tasks.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ title, reference })
            }).then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification("تمت إضافة المهمة بنجاح ✅");
                    this.reset();
                    refreshTaskList();
                }
            });
        });

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
    });
    </script>
@endpush
