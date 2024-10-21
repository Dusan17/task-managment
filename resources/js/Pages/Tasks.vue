<script setup>
import { ref, onMounted, computed } from 'vue';

// Define states for tasks, form data, filter, and sorting
const tasks = ref([]);
const searchQuery = ref('');
const sortOrder = ref('asc');
const statusFilter = ref('all'); // 'all', 'pending', 'completed'
const showModal = ref(false);

// Define a form for adding/updating tasks
const taskForm = ref({
    id: null,
    title: '',
    description: '',
    due_date: '',
});

// Fetch tasks from the API
const fetchTasks = async () => {
    try {
        const response = await fetch('/tasks');
        tasks.value = await response.json();
    } catch (error) {
        console.error('Error fetching tasks:', error);
    }
};

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const saveTask = async () => {
    const method = taskForm.value.id ? 'PUT' : 'POST';
    const url = taskForm.value.id ? `/tasks/${taskForm.value.id}` : '/tasks';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                title: taskForm.value.title,
                description: taskForm.value.description,
                due_date: taskForm.value.due_date,
            }),
        });

        if (response.ok) {
            taskForm.value = { id: null, title: '', description: '', due_date: '' };
            fetchTasks(); // Refresh the task list
            showModal.value = false; // Close the modal after saving
        } else {
            console.error('Failed to save task');
        }
    } catch (error) {
        console.error('Error saving task:', error);
    }
};

// Delete a task
const deleteTask = async (taskId) => {
    try {
        const response = await fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (response.ok) {
            fetchTasks(); // Refresh the task list
        } else {
            console.error('Failed to delete task');
        }
    } catch (error) {
        console.error('Error deleting task:', error);
    }
};

// Mark a task as complete
const completeTask = async (taskId) => {
    try {
        const response = await fetch(`/tasks/${taskId}/complete`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (response.ok) {
            fetchTasks(); // Refresh the task list
        } else {
            console.error('Failed to complete task');
        }
    } catch (error) {
        console.error('Error completing task:', error);
    }
};

// Filter tasks based on status and search query
const filteredTasks = computed(() => {
    let filtered = tasks.value;

    // Search filter
    if (searchQuery.value) {
        filtered = filtered.filter(task =>
            task.title.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    // Status filter
    if (statusFilter.value === 'completed') {
        filtered = filtered.filter(task => task.status); // Completed tasks
    } else if (statusFilter.value === 'pending') {
        filtered = filtered.filter(task => !task.status); // Pending tasks
    }

    // Sort by due date
    filtered.sort((a, b) => {
        const dateA = new Date(a.due_date);
        const dateB = new Date(b.due_date);

        if (sortOrder.value === 'asc') {
            return dateA - dateB;
        } else {
            return dateB - dateA;
        }
    });

    return filtered;
});

// Populate form for editing a task
const editTask = (task) => {
    taskForm.value = { ...task };
    showModal.value = true;
};

// Open modal for a new task
const openModalForNewTask = () => {
    taskForm.value = { id: null, title: '', description: '', due_date: '' }; // Reset the form
    showModal.value = true;
};

// Close the modal
const closeModal = () => {
    showModal.value = false;
};

// Toggle sort order
const toggleSortOrder = () => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

// Fetch tasks when the component is mounted
onMounted(() => {
    fetchTasks();
});
</script>

<template>
    <div class="container mx-auto mt-8">
        <h1 class="text-xl font-bold mb-4">Task Management</h1>

        <!-- Add New Task Button -->
        <button @click="openModalForNewTask" class="mb-6 bg-blue-500 text-white px-4 py-2 rounded-lg">
            Add New Task
        </button>

        <!-- Search and Sort Controls -->
        <div class="flex items-center mb-6 space-x-4">
            <!-- Search Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Search by Title</label>
                <input
                    v-model="searchQuery"
                    type="text"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg"
                    placeholder="Search tasks by title"
                />
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Filter by Status</label>
                <select
                    v-model="statusFilter"
                    class="mt-1 block w-full px-8 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <!-- Sort Button -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort by Due Date</label>
                <button
                    @click="toggleSortOrder"
                    class="mt-1 px-4 py-2 bg-blue-500 text-white rounded-lg"
                >
                    Sort: {{ sortOrder === 'asc' ? 'Ascending' : 'Descending' }}
                </button>
            </div>
        </div>

        <!-- Task Table -->
        <div v-if="filteredTasks.length > 0" class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg">
                <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Title</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Due Date</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="task in filteredTasks" :key="task.id" class="border-b">
                    <td class="px-6 py-4">{{ task.title }}</td>
                    <td class="px-6 py-4">{{ task.description }}</td>
                    <td class="px-6 py-4">{{ task.due_date }}</td>
                    <td class="px-6 py-4">
                            <span :class="task.status ? 'text-green-500' : 'text-red-500'">
                                {{ task.status ? 'Completed' : 'Pending' }}
                            </span>
                    </td>
                    <td class="px-6 py-4 flex space-x-4">
                        <button @click="completeTask(task.id)" v-if="!task.status" class="text-blue-500 underline">
                            Complete
                        </button>
                        <button @click="editTask(task)" class="text-yellow-500 underline">Edit</button>
                        <button @click="deleteTask(task.id)" class="text-red-500 underline">Delete</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-else>
            <p>No tasks available or matching your search criteria.</p>
        </div>

        <!-- Modal for Adding/Editing Task -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-lg font-medium mb-4">{{ taskForm.id ? 'Edit Task' : 'Add New Task' }}</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input
                        v-model="taskForm.title"
                        type="text"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg"
                        required
                    />
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="taskForm.description"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg"
                    ></textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input
                        v-model="taskForm.due_date"
                        type="date"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                </div>

                <div class="mt-6 flex justify-between">
                    <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded-lg">Cancel</button>
                    <button @click="saveTask" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                        {{ taskForm.id ? 'Update Task' : 'Add Task' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
