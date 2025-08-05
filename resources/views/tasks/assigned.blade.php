@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2 class="text-xl font-bold text-gray-800 mb-4">📥 المهام الموجهة إليّ</h2>

    {{-- ✅ إشعار --}}
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs z-50">
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
        <span id="notification-msg" class="text-sm"></span>
    </div>

    {{-- ✅ جدول المهام --}}
    <div id="assigned-tasks-table" class="bg-white border rounded shadow"></div>

    {{-- ✅ Modal المرفقات --}}
    <div id="attachments-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">
            <button id="close-modal-btn" class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">×</button>
            <h3 class="text-lg font-semibold mb-4">📎 مرفقات المهمة</h3>
            <div id="attachments-list" class="space-y-2"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
document.addEventListener('DOMContentLoaded', () => {

    const notify = (msg, type = 'success') => {
        const box = document.getElementById('notification');
        const msgBox = document.getElementById('notification-msg');
        box.classList.remove('hidden', 'bg-green-600', 'bg-red-600');
        box.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
        msgBox.innerText = msg;
        setTimeout(() => box.classList.add('hidden'), 5000);
    };

    document.getElementById('closeBtn').addEventListener('click', () => {
        document.getElementById('notification').classList.add('hidden');
    });

    const assignedTasksTable = new Tabulator("#assigned-tasks-table", {
        ajaxURL: "{{ route('tasks.assigned.fetch') }}",
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "لا توجد مهام موجهة حالياً.",
        columns: [
            { title: "📛 العنوان", field: "title", headerFilter: "input" },
            { title: "📌 المرجع", field: "reference", hozAlign: "center" },
            { title: "📂 الحالة", field: "status", hozAlign: "center" },
            { title: "🕒 البدء", field: "started_at", hozAlign: "center" },
            { title: "✅ الإنجاز", field: "completed_at", hozAlign: "center" },
            { title: "📎 مرفقات", field: "attachments_count", hozAlign: "center" },
            {
                title: "📝 الملاحظات",
                field: "comment",
                formatter: cell => {
                    let text = cell.getValue();
                    return text.length > 100 ? text.substring(0, 100) + '...' : text;
                }
            },
            {
                title: "👁️ عرض",
                formatter: () => "📎",
                hozAlign: "center",
                cellClick: function(e, cell) {
                    const taskId = cell.getRow().getData().id;
                    fetch(`/tasks/${taskId}/attachments`)
                        .then(res => res.json())
                        .then(data => {
                            const list = document.getElementById('attachments-list');
                            list.innerHTML = '';
                            if (data.length === 0) {
                                list.innerHTML = '<p class="text-gray-500">لا توجد مرفقات.</p>';
                            } else {
                                data.forEach(att => {
                                    list.innerHTML += `
                                        <div class="flex justify-between items-center border rounded px-4 py-2">
                                            <span>${att.original_name}</span>
                                            <a href="/attachments/download/${att.id}" class="text-blue-600 hover:underline" target="_blank">📥 تحميل</a>
                                        </div>
                                    `;
                                });
                            }
                            document.getElementById('attachments-modal').classList.remove('hidden');
                        });
                }
            }
        ]
    });

    document.getElementById('close-modal-btn').addEventListener('click', () => {
        document.getElementById('attachments-modal').classList.add('hidden');
    });
});
</script>
@endpush
