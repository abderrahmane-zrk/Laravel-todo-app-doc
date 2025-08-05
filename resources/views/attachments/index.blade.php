@extends('layouts.app')

@section('content')
<div class="p-4 space-y-6">

    <h2 class="text-xl font-bold text-gray-800">قائمة الوثائق / المرفقات 📎</h2>

    {{-- إشعار --}}
    <div id="notification" class="hidden fixed bottom-5 right-5 bg-white border border-gray-200 p-4 rounded shadow-lg max-w-xs">
        <span id="closeBtn" class="absolute top-2 left-2 cursor-pointer text-gray-500 hover:text-black">×</span>
    </div>

    {{-- رفع وثيقة --}}
    <form id="uploadForm" enctype="multipart/form-data" class="flex flex-col md:flex-row items-center gap-3">
        <input type="file" name="file" required class="border rounded p-2">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded shadow hover:bg-blue-700 transition">⬆️ رفع الوثيقة</button>
    </form>

    {{-- زر الحذف --}}
    @role('admin')
    <button id="delete-btn"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition">
        🗑️ حذف المحددين
    </button>
    @endrole

    {{-- جدول --}}
    <div id="attachments-table" class="bg-white rounded shadow overflow-x-auto"></div>

</div>
@endsection

@push('scripts')
<script type="module">
document.addEventListener('DOMContentLoaded', function () {

    const showNotification = (msg, type = 'success') => {
        const box = document.getElementById('notification');
        const color = type === 'success' ? 'bg-green-600' : 'bg-red-600';
        box.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white z-50 ${color}`;
        box.innerText = msg;
        box.classList.remove('hidden');
        setTimeout(() => box.classList.add('hidden'), 4000);
    };

    // ✅ جدول Tabulator
    const table = new Tabulator("#attachments-table", {
        ajaxURL: "{{ route('attachments.fetch') }}",
        layout: "fitColumns",
        selectable: true,
        placeholder: "لا توجد مرفقات بعد",
        columns: [
            { title: "✅", formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", width: 50 },
            {
                title: "📎 الاسم",
                field: "original_name",
                headerFilter: true,
                formatter: function(cell) {
                    const data = cell.getRow().getData();
                    return `<a href="${data.filename}" target="_blank" class="text-blue-600 underline">${cell.getValue()}</a>`;
                }
            },
            { title: "📂 نوع الملف", field: "mime_type", headerFilter: true },
            {
                title: "💾 الحجم",
                field: "size",
                headerFilter: true,
                formatter: cell => `${parseFloat(cell.getValue()).toFixed(2)} MB`
            },
            { title: "📝 المهمة", field: "task_title", headerFilter: true },
            { title: "⏱️ تاريخ الرفع", field: "created_at", sorter: "date", headerFilter: true }
        ]
    });

    // ✅ رفع الوثيقة
    document.getElementById('uploadForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        const res = await fetch("{{ route('attachments.uploadGeneral') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showNotification("✅ تم رفع الوثيقة");
            table.setData();
            this.reset();
        } else {
            showNotification("❌ فشل في رفع الوثيقة", 'error');
        }
    });

    // ✅ حذف المرفقات المحددة
    document.getElementById('delete-btn')?.addEventListener('click', async () => {
        const selected = table.getSelectedData().map(row => row.id);
        if (selected.length === 0) return showNotification("❗ حدد مرفقات أولاً", 'error');

        if (!confirm("هل أنت متأكد من حذف المرفقات المحددة؟")) return;

            const res = await fetch("{{ route('attachments.deleteMultiple') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: selected })
        });

        const data = await res.json();
        if (data.success) {
            showNotification("✅ تم حذف المرفقات");
            table.setData();
        } else {
            showNotification("❌ فشل في حذف المرفقات", 'error');
        }
    });

});
</script>
@endpush
