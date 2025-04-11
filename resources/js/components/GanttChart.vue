<template>
  <div class="max-w-7xl mx-auto p-6 bg-gray-50 rounded-xl shadow-lg">
    <h2 class="text-3xl font-extrabold text-gray-800 mb-6">📊 Gantt Chart</h2>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:space-x-6 space-y-4 md:space-y-0">
      <div>
        <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por:</label>
        <select v-model="selectedFilter" @change="applyFilter" id="filter" class="border-gray-300 rounded-lg p-2 w-full">
          <option value="all">Todos</option>
          <option value="month">Por Mês</option>
          <option value="week">Por Semana</option>
          <option value="day">Por Dia</option>
        </select>
      </div>

      <div v-if="selectedFilter === 'month'" class="w-full md:w-auto">
        <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Mês:</label>
        <input type="month" id="month" v-model="selectedMonth" @change="applyFilter" class="border-gray-300 rounded-lg p-2 w-full" />
      </div>

      <div v-if="selectedFilter === 'week'" class="w-full md:w-auto">
        <label for="week" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Semana:</label>
        <input type="week" id="week" v-model="selectedWeek" @change="applyFilter" class="border-gray-300 rounded-lg p-2 w-full" />
      </div>

      <div v-if="selectedFilter === 'day'" class="w-full md:w-auto">
        <label for="day" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Dia:</label>
        <input type="date" id="day" v-model="selectedDay" @change="applyFilter" class="border-gray-300 rounded-lg p-2 w-full" />
      </div>
    </div>

    <div v-if="successMessage"
         class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce">
      {{ successMessage }}
    </div>

    <div class="flex justify-end mb-4">
      <button @click="showCreateModal = true"
              class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition">+ Nova Tarefa</button>
    </div>

    <!-- Gráfico -->
    <div class="overflow-x-auto bg-white rounded-xl shadow p-4">
      <svg ref="svg" width="1000" height="500"></svg>
    </div>

    <!-- Modal de criação -->
    <div v-if="showCreateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-xl shadow-lg w-[400px]">
        <h3 class="text-xl font-semibold mb-4">📝 Nova Tarefa</h3>
        <div class="space-y-3">
          <label class="block">
            <span class="text-sm font-medium">Título</span>
            <input v-model="newTask.title" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Início</span>
            <input v-model="newTask.start_date" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Fim</span>
            <input v-model="newTask.end_date" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Deadline</span>
            <input v-model="newTask.deadline" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
        </div>
        <div class="flex justify-end mt-6 space-x-2">
          <button @click="createTask" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Criar</button>
          <button @click="showCreateModal = false" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">Cancelar</button>
        </div>
      </div>
    </div>

    <!-- Modal de edição -->
    <div v-if="editingTask" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-xl shadow-lg w-[400px]">
        <h3 class="text-xl font-semibold mb-4">✏️ Editar Tarefa</h3>
        <div class="space-y-3">
          <label class="block">
            <span class="text-sm font-medium">Título</span>
            <input v-model="editingTask.title" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Início</span>
            <input v-model="editingTask.start_date" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Fim</span>
            <input v-model="editingTask.end_date" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Deadline</span>
            <input v-model="editingTask.deadline" type="datetime-local" class="border-gray-300 rounded-md p-2 w-full" />
          </label>
        </div>
        <div class="flex justify-between mt-6">
          <button @click="updateTask" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Guardar</button>
          <button @click="deleteTask(editingTask.id)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Apagar</button>
          <button @click="cancelEdit" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">Cancelar</button>
        </div>
      </div>
    </div>
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
    const selectedMonth = ref('');
    const selectedWeek = ref('');
    const selectedDay = ref('');
    const tasks = ref<Task[]>([]);
    const filteredTasks = ref<Task[]>([]);
    const svg = ref<SVGSVGElement | null>(null);
    const showCreateModal = ref(false);
    const successMessage = ref('');
    const newTask = ref({ title: '', start_date: '', end_date: '', deadline: '' });
    const editingTask = ref<Task | null>(null);
    const selectedFilter = ref('all');

    const colorScale = d3.scaleOrdinal(d3.schemeTableau10);

    const fetchTasks = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/tasks');
    console.log("Dados recebidos do backend:", response.data); // Log para depuração
    tasks.value = response.data;
    applyFilter();
  } catch (error) {
    console.error("Erro ao buscar tarefas:", error);
  }
};

const applyFilter = () => {
  console.log("Filtro selecionado:", selectedFilter.value); // Log para depuração
  console.log("Mês selecionado:", selectedMonth.value); // Log para depuração
  console.log("Semana selecionada:", selectedWeek.value); // Log para depuração
  console.log("Dia selecionado:", selectedDay.value); // Log para depuração

  if (selectedFilter.value === 'month' && selectedMonth.value) {
    const [year, month] = selectedMonth.value.split('-').map(Number);
    const startOfMonth = new Date(year, month - 1, 1, 0, 0, 0, 0);
    const endOfMonth = new Date(year, month, 0, 23, 59, 59, 999);

    filteredTasks.value = tasks.value.filter(task => {
      const startDate = new Date(task.start_date);
      const endDate = new Date(task.end_date);
      return (
        (startDate >= startOfMonth && startDate <= endOfMonth) || // Início dentro do mês
        (endDate >= startOfMonth && endDate <= endOfMonth) ||     // Fim dentro do mês
        (startDate <= startOfMonth && endDate >= endOfMonth)      // Abrange o mês inteiro
      );
    });
  } else if (selectedFilter.value === 'week' && selectedWeek.value) {
    const [year, week] = selectedWeek.value.split('-W').map(Number);
    const jan1 = new Date(year, 0, 1);
    const daysOffset = (week - 1) * 7;
    const startOfWeek = new Date(jan1.setDate(jan1.getDate() + daysOffset));
    startOfWeek.setHours(0, 0, 0, 0);
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    endOfWeek.setHours(23, 59, 59, 999);

    filteredTasks.value = tasks.value.filter(task => {
      const startDate = new Date(task.start_date);
      const endDate = new Date(task.end_date);
      return (
        (startDate >= startOfWeek && startDate <= endOfWeek) || // Início dentro da semana
        (endDate >= startOfWeek && endDate <= endOfWeek) ||     // Fim dentro da semana
        (startDate <= startOfWeek && endDate >= endOfWeek)      // Abrange a semana inteira
      );
    });
  } else if (selectedFilter.value === 'day' && selectedDay.value) {
    const selectedDate = new Date(selectedDay.value);
    selectedDate.setHours(0, 0, 0, 0); // Início do dia
    const endOfDay = new Date(selectedDate);
    endOfDay.setHours(23, 59, 59, 999); // Fim do dia

    filteredTasks.value = tasks.value.filter(task => {
      const startDate = new Date(task.start_date);
      const endDate = new Date(task.end_date);
      return (
        (startDate >= selectedDate && startDate <= endOfDay) || // Início no dia
        (endDate >= selectedDate && endDate <= endOfDay) ||     // Fim no dia
        (startDate <= selectedDate && endDate >= endOfDay)      // Abrange o dia inteiro
      );
    });
  } else {
    filteredTasks.value = tasks.value; // Mostrar todas as tarefas
  }

  console.log("Tarefas filtradas:", filteredTasks.value); // Log para depuração
  drawChart(filteredTasks.value);
};

    const createTask = async () => {
      try {
        await axios.post('http://127.0.0.1:8000/api/tasks', newTask.value);
        showCreateModal.value = false;
        successMessage.value = 'Tarefa criada!';
        newTask.value = { title: '', start_date: '', end_date: '', deadline: '' };
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao criar tarefa:", error);
      }
    };

    const updateTask = async () => {
      if (!editingTask.value) return;
      try {
        await axios.put(`http://127.0.0.1:8000/api/tasks/${editingTask.value.id}`, editingTask.value);
        successMessage.value = 'Tarefa atualizada!';
        editingTask.value = null;
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao atualizar tarefa:", error);
      }
    };

    const deleteTask = async (taskId: number) => {
      try {
        await axios.delete(`http://127.0.0.1:8000/api/tasks/${taskId}`);
        successMessage.value = 'Tarefa apagada!';
        editingTask.value = null;
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao apagar tarefa:", error);
      }
    };

    const cancelEdit = () => {
      editingTask.value = null;
    };

    const drawChart = (data: Task[]) => {
  if (!svg.value) {
    console.error("Elemento SVG não encontrado.");
    return;
  }

  console.log("Dados para renderizar no gráfico:", data); // Log para depuração

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

  // Funções para parsear e formatar datas
  const parseDate = d3.timeParse("%Y-%m-%d %H:%M:%S");
  const formatDate = d3.timeFormat("%Y-%m-%d %H:%M:%S");

  // Validação e conversão de dados
  const tasksParsed = data
    .map(d => ({
      ...d,
      start: parseDate(d.start_date),
      end: parseDate(d.end_date),
    }))
    .filter(d => d.start && d.end) as (Task & { start: Date; end: Date })[];

  if (tasksParsed.length === 0) {
    console.warn("Nenhuma tarefa válida encontrada para exibir.");
    return;
  }

  // Configuração da escala temporal (eixo X)
  const x = d3.scaleTime()
    .domain([d3.min(tasksParsed, d => d.start)!, d3.max(tasksParsed, d => d.end)!])
    .range([0, width]);

  // Configuração da escala de bandas (eixo Y)
  const y = d3.scaleBand()
    .domain(tasksParsed.map(d => d.title))
    .range([0, height])
    .padding(0.2);

  // Adiciona os eixos
  g.append("g").call(d3.axisLeft(y));
  g.append("g")
    .attr("transform", `translate(0, ${height})`)
    .call(d3.axisBottom(x).ticks(5).tickFormat(d3.timeFormat("%d/%m")));

  // Adiciona as barras do gráfico
  const bars = g.selectAll("g.task")
    .data(tasksParsed)
    .enter()
    .append("g")
    .attr("class", "task");

  bars.append("rect")
    .attr("x", d => x(d.start))
    .attr("y", d => y(d.title)!)
    .attr("width", d => x(d.end) - x(d.start))
    .attr("height", y.bandwidth())
    .attr("fill", (d, i) => colorScale(i.toString()))
    .style("cursor", "pointer")
    .on("click", (event, d) => {
      editingTask.value = {
        id: d.id,
        title: d.title,
        start_date: formatDate(d.start),
        end_date: formatDate(d.end),
        deadline: d.deadline,
      };
    });

  // Adiciona manipuladores para ajustar datas de início
  bars.append("rect")
    .attr("x", d => x(d.start) - 5)
    .attr("y", d => y(d.title)! + y.bandwidth() / 4)
    .attr("width", 10)
    .attr("height", y.bandwidth() / 2)
    .attr("fill", "gray")
    .style("cursor", "ew-resize")
    .call(
      d3.drag<SVGRectElement, any>()
        .on("drag", function (event, d) {
          const newStart = x.invert(x(d.start) + event.dx);
          d.start = newStart;
          d3.select(this.parentNode).select("rect")
            .attr("x", x(d.start))
            .attr("width", x(d.end) - x(d.start));
          d3.select(this).attr("x", x(d.start) - 5);
        })
        .on("end", async function (event, d) {
          const updated = {
            ...d,
            start_date: formatDate(d.start),
            end_date: formatDate(d.end),
          };

          try {
            await axios.put(`http://127.0.0.1:8000/api/tasks/${d.id}`, updated);
            successMessage.value = "Data inicial atualizada!";
            setTimeout(() => (successMessage.value = ""), 3000);
            await fetchTasks();
          } catch (error) {
            console.error("Erro ao atualizar data inicial:", error);
          }
        })
    );

  // Adiciona manipuladores para ajustar datas de fim
  bars.append("rect")
    .attr("x", d => x(d.end) - 5)
    .attr("y", d => y(d.title)! + y.bandwidth() / 4)
    .attr("width", 10)
    .attr("height", y.bandwidth() / 2)
    .attr("fill", "gray")
    .style("cursor", "ew-resize")
    .call(
      d3.drag<SVGRectElement, any>()
        .on("drag", function (event, d) {
          const newEnd = x.invert(x(d.end) + event.dx);
          d.end = newEnd;
          d3.select(this.parentNode).select("rect")
            .attr("width", x(d.end) - x(d.start));
          d3.select(this).attr("x", x(d.end) - 5);
        })
        .on("end", async function (event, d) {
          const updated = {
            ...d,
            start_date: formatDate(d.start),
            end_date: formatDate(d.end),
          };

          try {
            await axios.put(`http://127.0.0.1:8000/api/tasks/${d.id}`, updated);
            successMessage.value = "Data final atualizada!";
            setTimeout(() => (successMessage.value = ""), 3000);
            await fetchTasks();
          } catch (error) {
            console.error("Erro ao atualizar data final:", error);
          }
        })
    );
};

    onMounted(fetchTasks);

    return {
  svg,
  newTask,
  createTask,
  showCreateModal,
  successMessage,
  editingTask,
  updateTask,
  deleteTask,
  cancelEdit,
  selectedFilter,
  selectedMonth,
  selectedWeek,
  selectedDay,
  applyFilter
};

  }
});
</script>
<style scoped>
svg {
  background: #f9fafb;
  margin-bottom: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
}

button {
  transition: 0.2s;
}

button:hover {
  filter: brightness(0.95);
}

input, select {
  font-size: 14px;
  padding: 6px;
}

.fixed {
  z-index: 1000;
}

.modal input {
  font-size: 0.875rem;
}

.task rect:hover {
  opacity: 0.9;
  stroke: #000;
  stroke-width: 1;
}

.task-buttons {
  margin-top: 8px;
}

.bg-green-600:hover,
.bg-blue-600:hover,
.bg-red-600:hover,
.bg-gray-400:hover {
  filter: brightness(0.9);
}
</style>