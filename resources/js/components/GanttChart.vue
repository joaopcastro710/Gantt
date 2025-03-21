<template>
    <div>
        <h2 class="text-xl font-bold mb-4">Gantt Chart</h2>
        <ul>
            <li v-for="task in tasks" :key="task.id" class="mb-2">
                <div class="p-2 border rounded shadow">
                    <strong>{{ task.title }}</strong><br>
                    {{ task.start_date }} → {{ task.end_date }} (Deadline: {{ task.deadline }})
                </div>
            </li>
        </ul>
    </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import axios from 'axios';

interface Task {
    id: number;
    title: string;
    start_date: string;
    end_date: string;
    deadline: string;
}

export default defineComponent({
    name: 'GanttChart',
    setup() {
        const tasks = ref<Task[]>([]);

        onMounted(async () => {
            try {
                const response = await axios.get('http://127.0.0.1:8000/tasks');

                tasks.value = response.data;
            } catch (error) {
                console.error("Erro ao buscar as tarefas:", error);
            }
        });

        return { tasks };
    }
});
</script>

<style scoped>
/* extra, doing it later */
</style>
