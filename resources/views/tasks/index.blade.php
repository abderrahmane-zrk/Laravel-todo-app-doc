@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-12 px-4">
    <h1 class="text-3xl font-bold text-center mb-8 text-blue-800">📋 قائمة المهام</h1>

    <!-- إضافة مهمة -->
    <form id="add-task-form" class="flex gap-2 mb-6">
        @csrf
        <input type="text" id="task-title" name="title" placeholder="أدخل مهمة جديدة"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 transition text-white px-4 py-2 rounded-lg ">➕ إضافة</button>
    </form>

    <!-- عمليات متعددة -->
    <div class="flex flex-wrap gap-2 justify-center mb-6">
        @csrf
        <button type="button" id="mark-complete"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition">✅ إنهاء المحدد</button>
        <button type="button" id="mark-incomplete"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow transition">🔁 إلغاء إنهاء</button>
        <button type="button" id="delete-selected"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition">🗑️ حذف المحدد</button>
    </div>

    <!-- قائمة المهام -->
    <ul id="task-list" class="space-y-3">
        @foreach ($tasks as $task)
            <li id="task-{{ $task->id }}"
                class="flex items-center justify-between bg-white border border-gray-200 px-4 py-3 rounded-lg shadow-sm hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="task-checkbox h-5 w-5 text-blue-600" value="{{ $task->id }}">
                    <span class="{{ $task->completed ? 'line-through text-gray-400' : 'text-gray-800 font-medium' }}">
                        {{ $task->title }}
                    </span>
                </div>
                <span
                    class="text-xs px-2 py-1 rounded-full {{ $task->completed ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $task->completed ? 'مكتملة' : 'نشطة' }}
                </span>
            </li>
        @endforeach
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const csrf = document.querySelector('input[name="_token"]').value;

    // إضافة مهمة
    document.getElementById('add-task-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const title = document.getElementById('task-title').value;

        const response = await fetch('{{ route("tasks.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ title })
        });

        const data = await response.json();
        if (data.success) {
            addTaskToList(data.task); // ✅ تضيفه مباشرة في الأعلى
            document.getElementById('task-title').value = '';
        }
    });

    // حذف متعدد
    document.getElementById('delete-selected').addEventListener('click', async function () {
        const selected = getSelectedTaskIds();
        if (selected.length === 0) return;

        const response = await fetch('{{ route("tasks.deleteMultiple") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: selected })
        });

        const data = await response.json();
        if (data.success) {
            selected.forEach(id => {
                document.querySelector(`#task-${id}`)?.remove();
            });
        }
    });

    // إنهاء أو إلغاء إنهاء المهام المحددة
    document.getElementById('mark-complete').addEventListener('click', () => toggleCompletionForSelected(true));
    document.getElementById('mark-incomplete').addEventListener('click', () => toggleCompletionForSelected(false));

    async function toggleCompletionForSelected(completed) {
        const selected = getSelectedTaskIds();
        if (selected.length === 0) return;

        const response = await fetch('{{ route("tasks.bulkToggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selected, completed })
        });

        const data = await response.json();
        if (data.success) {
            await refreshTaskList();
        }
    }

    function getSelectedTaskIds() {
        return [...document.querySelectorAll('.task-checkbox:checked')].map(cb => cb.value);
    }

    // جلب قائمة المهام وتحديث الواجهة
    async function refreshTaskList() {
        const response = await fetch('{{ route("tasks.index") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        if (data.success) {
            const ul = document.getElementById('task-list');
            ul.innerHTML = '';

            // ❗️هنا نضمن أن نبدأ بالأقدم أولًا، ثم نضيفهم باستخدام prepend ليظهر الأحدث بالأعلى
            data.tasks.reverse().forEach(task => {
                addTaskToList(task);
            });
        }
    }

    // توليد عنصر المهمة وإضافته للقائمة
    function addTaskToList(task) {
        const ul = document.getElementById('task-list');
        const li = document.createElement('li');
        li.id = `task-${task.id}`;
        li.className = "flex items-center justify-between bg-white border border-gray-200 px-4 py-3 rounded-lg shadow-sm hover:bg-gray-50 transition";

        const completedClass = task.completed ? 'line-through text-gray-400' : 'text-gray-800 font-medium';
        const statusBadge = task.completed
            ? '<span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">مكتملة</span>'
            : '<span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">نشطة</span>';

        li.innerHTML = `
            <div class="flex items-center gap-3">
                <input type="checkbox" class="task-checkbox h-5 w-5 text-blue-600" value="${task.id}">
                <span class="${completedClass}">${task.title}</span>
            </div>
            ${statusBadge}
        `;

        ul.prepend(li); // ✅ الأحدث دائمًا في الأعلى
    }
});
</script>



@endsection
