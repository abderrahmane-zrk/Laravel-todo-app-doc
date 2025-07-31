@extends('layouts.app')

@section('content')
<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">قائمة الوثائق / المرفقات</h2>

    <div id="attachments-table"></div>
</div>

<form id="uploadForm" enctype="multipart/form-data">
    <label class="block mb-2 font-medium">رفع وثيقة عامة:</label>
    <input type="file" name="file" required class="block mb-2">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">رفع</button>
</form>

@endsection

@push('scripts')
<script type="module">
    

    const table = new Tabulator("#attachments-table", {
        ajaxURL: "{{ route('attachments.fetch') }}",
        
        layout: "fitColumns",
        pagination: "local",
        paginationSize: 10,
        columns: [
           {
                title: "الاسم الأصلي",
                field: "original_name",
                headerFilter: true,
                formatter: function(cell) {
                    const data = cell.getRow().getData();
                    const label = cell.getValue();
                    const url = data.filename; // أو استخدم full_url لو كان كاملاً
                    return `<a href="${url}" target="_blank" class="text-blue-600 underline">${label}</a>`;
                }
            },

            {
                title: "نوع الملف",
                field: "mime_type",
                headerFilter: true
            },
            {
                title: "الحجم",
                field: "size",
                headerFilter: true,
                formatter: function(cell) {
                    const rawValue = cell.getValue();
                    console.log("Raw size value:", rawValue);

                    const bytes = parseInt(rawValue, 10);

                    if (typeof bytes !== 'number' || isNaN(bytes) || bytes <= 0) {
                        return "no"; 
                    }

                    const megabytes = bytes / (1024 * 1024);
                    return megabytes.toFixed(2) + ' MB';
                }


            },

            {
                title: "مرتبطة بالمهمة",
                field: "task_title",
                headerFilter: true
            },
            {
                title: "تاريخ الرفع",
                field: "created_at",
                sorter: "date",
                headerFilter: true
            }
        ],
    });


    document.getElementById('uploadForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        const res = await fetch("{{ route('attachments.uploadGeneral') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            alert("تم رفع الوثيقة بنجاح");
            table.setData(); // إعادة تحميل جدول تابوليتور
            this.reset();
        } else {
            alert("حدث خطأ");
        }
    });


   
</script>
@endpush
