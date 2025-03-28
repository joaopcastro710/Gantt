<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Gantt Chart</h2>

    <!-- Sucesso Message -->
    <div v-if="successMessage"
         class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
      {{ successMessage }}
    </div>

    <!-- Botão de criar nova tarefa -->
    <button @click="showCreateModal = true"
            class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
      + Nova Tarefa
    </button>

    <!-- Modal Criar -->
    <div v-if="showCreateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded shadow-md w-96">
        <h3 class="text-lg font-semibold mb-4">Nova Tarefa</h3>
        <label class="block mb-2">Título:
          <input v-model="newTask.title" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Início:
          <input v-model="newTask.start_date" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Fim:
          <input v-model="newTask.end_date" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Deadline:
          <input v-model="newTask.deadline" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <div class="flex justify-end mt-4">
          <button @click="createTask" class="bg-blue-600 text-white px-4 py-2 rounded mr-2 hover:bg-blue-700">Criar</button>
          <button @click="showCreateModal = false" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- Modal Editar -->
    <div v-if="editingTask" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded shadow-md w-96">
        <h3 class="text-lg font-semibold mb-4">Editar Tarefa</h3>
        <label class="block mb-2">Título:
          <input v-model="editingTask.title" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Início:
          <input v-model="editingTask.start_date" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Fim:
          <input v-model="editingTask.end_date" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <label class="block mb-2">Deadline:
          <input v-model="editingTask.deadline" type="datetime-local" class="border p-2 rounded w-full" />
        </label>
        <div class="flex justify-end mt-4">
          <button @click="updateTask" class="bg-blue-600 text-white px-4 py-2 rounded mr-2 hover:bg-blue-700">Guardar</button>
          <button @click="cancelEdit" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- Gantt Chart -->
    <svg ref="svg" width="800" height="400"></svg>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import axios from 'axios';
import * as d3 from 'd3';

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
    const svg = ref<SVGSVGElement | null>(null);
    const showCreateModal = ref(false);
    const successMessage = ref('');
    const newTask = ref({ title: '', start_date: '', end_date: '', deadline: '' });
    const editingTask = ref<Task | null>(null);

    const colorScale = d3.scaleOrdinal(d3.schemeCategory10);

    const fetchTasks = async () => {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/tasks');
        tasks.value = response.data;
        drawChart(tasks.value);
      } catch (error) {
        console.error("Erro ao buscar tarefas:", error);
      }
    };

    const createTask = async () => {
      try {
        await axios.post('http://127.0.0.1:8000/api/tasks', newTask.value);
        showCreateModal.value = false;
        successMessage.value = '✅ Tarefa criada com sucesso!';
        newTask.value = { title: '', start_date: '', end_date: '', deadline: '' };
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao criar tarefa:", error);
      }
    };

    const startEdit = (task: Task) => {
      editingTask.value = { ...task };
    };

    const cancelEdit = () => {
      editingTask.value = null;
    };

    const updateTask = async () => {
      if (!editingTask.value) return;
      try {
        await axios.put(`http://127.0.0.1:8000/api/tasks/${editingTask.value.id}`, editingTask.value);
        successMessage.value = '✅ Tarefa atualizada!';
        editingTask.value = null;
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao atualizar tarefa:", error);
      }
    };

    const deleteTask = async (id: number) => {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/tasks/${id}`);
        successMessage.value = '🗑️ Tarefa eliminada!';
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao apagar tarefa:", error);
      }
    };

    const drawChart = (data: Task[]) => {
      if (!svg.value) return;

      const margin = { top: 20, right: 30, bottom: 30, left: 100 };
      const width = 800 - margin.left - margin.right;
      const height = data.length * 40;

      const svgEl = d3.select(svg.value);
      svgEl.selectAll("*").remove();

      const g = svgEl
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

      const parseDate = d3.timeParse("%Y-%m-%d %H:%M:%S");
      const tasksParsed = data.map(d => ({
        ...d,
        start: parseDate(d.start_date)!,
        end: parseDate(d.end_date)!
      }));

      const x = d3.scaleTime()
        .domain([d3.min(tasksParsed, d => d.start)!, d3.max(tasksParsed, d => d.end)!])
        .range([0, width]);

      const y = d3.scaleBand()
        .domain(tasksParsed.map(d => d.title))
        .range([0, height])
        .padding(0.2);

      g.append("g").call(d3.axisLeft(y));
      g.append("g")
        .attr("transform", `translate(0, ${height})`)
        .call(d3.axisBottom(x).ticks(5).tickFormat(d3.timeFormat("%d/%m")));

      g.selectAll("rect")
        .data(tasksParsed)
        .enter()
        .append("rect")
        .attr("x", d => x(d.start))
        .attr("y", d => y(d.title)!)
        .attr("width", d => x(d.end) - x(d.start))
        .attr("height", y.bandwidth())
        .attr("fill", (d, i) => colorScale(i.toString()))
        .style("cursor", "pointer")
        .on("click", (_e, d) => {
          const original = tasks.value.find(t => t.title === d.title);
          if (original) startEdit(original);
        });
    };

    onMounted(fetchTasks);

    return {
      svg,
      newTask,
      createTask,
      showCreateModal,
      successMessage,
      editingTask,
      startEdit,
      updateTask,
      cancelEdit
    };
  }
});
</script>

<style scoped>
svg {
  background: #f9fafb;
  margin-bottom: 16px;
}
button {
  transition: 0.2s;
}
</style>
