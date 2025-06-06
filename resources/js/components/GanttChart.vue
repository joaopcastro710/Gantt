<template>
  <div class="max-w-7xl mx-auto p-6 bg-gradient-to-r from-gray-100 to-gray-200 rounded-xl shadow-2xl">
    <h2 class="text-4xl font-extrabold text-gray-800 mb-6 text-center">📊 Gantt Chart</h2>

    <!-- Filtros -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:space-x-6 space-y-4 md:space-y-0">
      <div>
        <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por:</label>
        <select v-model="selectedFilter" @change="applyFilter" id="filter" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500">
          <option value="all">Todos</option>
          <option value="month">Por Mês</option>
          <option value="week">Por Semana</option>
          <option value="day">Por Dia</option>
        </select>
      </div>

      <div v-if="selectedFilter === 'month'" class="w-full md:w-auto">
        <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Mês:</label>
        <input type="month" id="month" v-model="selectedMonth" @change="applyFilter" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
      </div>

      <div v-if="selectedFilter === 'week'" class="w-full md:w-auto">
        <label for="week" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Semana:</label>
        <input type="week" id="week" v-model="selectedWeek" @change="applyFilter" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
      </div>

      <div v-if="selectedFilter === 'day'" class="w-full md:w-auto">
        <label for="day" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Dia:</label>
        <input type="date" id="day" v-model="selectedDay" @change="applyFilter" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
      </div>
    </div>

    <!-- Mensagem de sucesso -->
    <div v-if="successMessage"
         class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-bounce">
      {{ successMessage }}
    </div>

    <!-- Botão para criar nova tarefa -->
    <div class="flex justify-end mb-4">
      <button @click="showCreateModal = true"
              class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
        + Nova Tarefa
      </button>
    </div>

    <!-- Gráfico -->
    <div class="flex justify-center items-center bg-white rounded-xl shadow-lg p-6">
      <svg ref="svg" class="w-full max-w-4xl h-auto"></svg>
    </div>

    <!-- Modal de criação -->
    <div v-if="showCreateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-xl shadow-2xl w-[400px]">
        <h3 class="text-xl font-semibold mb-4 text-center">Nova Tarefa</h3>
        <div class="space-y-4">
          <label class="block">
            <span class="text-sm font-medium">Template</span>
            <select v-model="newTask.task_template_id" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500">
              <option disabled value="">Selecione um template</option>
              <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.title }}</option>
            </select>
          </label>
          <label class="block">
            <span class="text-sm font-medium">Início</span>
            <input v-model="newTask.start_date" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Fim</span>
            <input v-model="newTask.end_date" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Deadline</span>
            <input v-model="newTask.deadline" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
        </div>
        <div class="flex justify-end mt-6 space-x-2">
          <button @click="createTask"
                  class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-green-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105">
            Criar
          </button>
          <button @click="showCreateModal = false"
                  class="bg-gradient-to-r from-gray-400 to-gray-500 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-gray-500 hover:to-gray-600 transition-all duration-300 transform hover:scale-105">
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de edição -->
    <div v-if="editingTask" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-8 rounded-2xl shadow-2xl w-[95vw] max-w-md animate-fade-in-up relative">
        <button @click="cancelEdit"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-bold focus:outline-none">
          &times;
        </button>
        <h3 class="text-2xl font-bold mb-6 text-center text-indigo-700">Editar Tarefa</h3>
        <div class="space-y-4">
          <label class="block">
            <span class="text-sm font-medium">Template</span>
            <select v-model="editingTask.task_template_id" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500">
              <option disabled value="">Selecione um template</option>
              <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.title }}</option>
            </select>
          </label>
          <label class="block">
            <span class="text-sm font-medium">Início</span>
            <input v-model="editingTask.start_date" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Fim</span>
            <input v-model="editingTask.end_date" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
          <label class="block">
            <span class="text-sm font-medium">Deadline</span>
            <input v-model="editingTask.deadline" type="datetime-local" class="border-gray-300 rounded-lg p-3 w-full shadow-md focus:ring-2 focus:ring-indigo-500" />
          </label>
        </div>
        <div class="flex justify-between mt-8">
          <button @click="updateTask"
            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300 transform hover:scale-105">
            Guardar
          </button>
          <button @click="deleteTask(editingTask.id)"
            class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-105">
            Apagar
          </button>
          <button @click="cancelEdit"
            class="bg-gradient-to-r from-gray-400 to-gray-500 text-white px-6 py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-gray-500 hover:to-gray-600 transition-all duration-300 transform hover:scale-105">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import axios from 'axios';
import * as d3 from 'd3';

interface TaskAction {
  id: number;
  task_template_id: number;
  start_date: string;
  end_date: string;
  deadline: string;
  status: string;
  template: {
    title: string;
    description: string;
  };
}

interface TaskTemplate {
  id: number;
  title: string;
  description: string;
}

export default defineComponent({
  name: 'GanttChart',
  setup() {
    const selectedMonth = ref('');
    const selectedWeek = ref('');
    const selectedDay = ref('');
    const tasks = ref<TaskAction[]>([]);
    const filteredTasks = ref<TaskAction[]>([]);
    const svg = ref<SVGSVGElement | null>(null);
    const showCreateModal = ref(false);
    const successMessage = ref('');
    const templates = ref<TaskTemplate[]>([]);
    const newTask = ref({
      task_template_id: '',
      start_date: '',
      end_date: '',
      deadline: '',
      status: 'pending'
    });
    const editingTask = ref<TaskAction | null>(null);
    const selectedFilter = ref('all');

    const colorScale = d3.scaleOrdinal(d3.schemeTableau10);

    const fetchTemplates = async () => {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/task-templates');
        templates.value = response.data;
      } catch (error) {
        console.error("Erro ao buscar templates:", error);
      }
    };

    const fetchTasks = async () => {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/task-actions');
        tasks.value = response.data;
        applyFilter();
      } catch (error) {
        console.error("Erro ao buscar tarefas:", error);
      }
    };

    const applyFilter = () => {
      if (selectedFilter.value === 'month' && selectedMonth.value) {
        const [year, month] = selectedMonth.value.split('-').map(Number);
        const startOfMonth = new Date(year, month - 1, 1, 0, 0, 0, 0);
        const endOfMonth = new Date(year, month, 0, 23, 59, 59, 999);

        filteredTasks.value = tasks.value.filter(task => {
          const startDate = new Date(task.start_date);
          const endDate = new Date(task.end_date);
          return (
            (startDate >= startOfMonth && startDate <= endOfMonth) ||
            (endDate >= startOfMonth && endDate <= endOfMonth) ||
            (startDate <= startOfMonth && endDate >= endOfMonth)
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
            (startDate >= startOfWeek && startDate <= endOfWeek) ||
            (endDate >= startOfWeek && endDate <= endOfWeek) ||
            (startDate <= startOfWeek && endDate >= endOfWeek)
          );
        });
      } else if (selectedFilter.value === 'day' && selectedDay.value) {
        const selectedDate = new Date(selectedDay.value);
        selectedDate.setHours(0, 0, 0, 0);
        const endOfDay = new Date(selectedDate);
        endOfDay.setHours(23, 59, 59, 999);

        filteredTasks.value = tasks.value.filter(task => {
          const startDate = new Date(task.start_date);
          const endDate = new Date(task.end_date);
          return (
            (startDate >= selectedDate && startDate <= endOfDay) ||
            (endDate >= selectedDate && endDate <= endOfDay) ||
            (startDate <= selectedDate && endDate >= endOfDay)
          );
        });
      } else {
        filteredTasks.value = tasks.value;
      }
      drawChart(filteredTasks.value);
    };

    const createTask = async () => {
      try {
        await axios.post('http://127.0.0.1:8000/api/task-actions', newTask.value);
        showCreateModal.value = false;
        successMessage.value = 'Tarefa criada!';
        newTask.value = {
          task_template_id: '',
          start_date: '',
          end_date: '',
          deadline: '',
          status: 'pending'
        };
        await fetchTasks();
        setTimeout(() => successMessage.value = '', 3000);
      } catch (error) {
        console.error("Erro ao criar tarefa:", error);
      }
    };

    const updateTask = async () => {
      if (!editingTask.value) return;
      try {
        await axios.put(`http://127.0.0.1:8000/api/task-actions/${editingTask.value.id}`, {
          task_template_id: editingTask.value.task_template_id,
          start_date: editingTask.value.start_date,
          end_date: editingTask.value.end_date,
          deadline: editingTask.value.deadline,
          status: editingTask.value.status
        });
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
        await axios.delete(`http://127.0.0.1:8000/api/task-actions/${taskId}`);
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

    const drawChart = (data: TaskAction[]) => {
      if (!svg.value) {
        console.error("Elemento SVG não encontrado.");
        return;
      }

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
      const formatDate = d3.timeFormat("%Y-%m-%d %H:%M:%S");

      const tasksParsed = data
        .map(d => ({
          ...d,
          title: d.template?.title ?? '',
          start: parseDate(d.start_date.replace('T', ' ')),
          end: parseDate(d.end_date.replace('T', ' ')),
        }))
        .filter(d => d.start && d.end) as (TaskAction & { title: string; start: Date; end: Date })[];

      if (tasksParsed.length === 0) {
        return;
      }

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

      const bars = g.selectAll("g.task")
        .data(tasksParsed)
        .enter()
        .append("g")
        .attr("class", "task");

      // Barra principal (drag move)
      bars.append("rect")
        .attr("x", d => x(d.start))
        .attr("y", d => y(d.title)!)
        .attr("width", d => x(d.end) - x(d.start))
        .attr("height", y.bandwidth())
        .attr("fill", (d, i) => colorScale(i.toString()))
        .style("cursor", "pointer")
        .call(
          d3.drag()
            .on("start", function (event, d) {
              d3.select(this).raise().attr("stroke", "black");
            })
            .on("drag", function (event, d) {
              // Move toda a barra
              const dx = x.invert(event.x).getTime() - d.start.getTime();
              d.start = new Date(d.start.getTime() + dx);
              d.end = new Date(d.end.getTime() + dx);
              d3.select(this)
                .attr("x", x(d.start));
              // Atualiza handles
              d3.select(this.parentNode).selectAll("rect.handle-start")
                .attr("x", x(d.start) - 4);
              d3.select(this.parentNode).selectAll("rect.handle-end")
                .attr("x", x(d.end) - 4);
              d3.select(this).attr("width", x(d.end) - x(d.start));
            })
            .on("end", async function (event, d) {
              d3.select(this).attr("stroke", null);
              try {
                await axios.put(`http://127.0.0.1:8000/api/task-actions/${d.id}`, {
                  task_template_id: d.task_template_id,
                  start_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.start),
                  end_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.end),
                  deadline: d.deadline,
                  status: d.status
                });
              } catch (error) {
                console.error("Erro ao atualizar datas por drag:", error);
              }
            })
        )
        .on("click", (event, d) => {
          editingTask.value = {
            ...d,
            start_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.start),
            end_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.end),
          };
        });

      // Handles para ajustar início e fim
      const handleWidth = 8;

      // Handle esquerdo (start)
      bars.append("rect")
        .attr("class", "handle-start")
        .attr("x", d => x(d.start) - handleWidth / 2)
        .attr("y", d => y(d.title)!)
        .attr("width", handleWidth)
        .attr("height", y.bandwidth())
        .attr("fill", "#333")
        .attr("cursor", "ew-resize")
        .call(
          d3.drag()
            .on("start", function (event, d) {
              d3.select(this).raise().attr("stroke", "black");
            })
            .on("drag", function (event, d) {
              const newStart = x.invert(event.x);
              if (newStart < d.end) {
                d.start = newStart;
                d3.select(this).attr("x", x(d.start) - handleWidth / 2);
                d3.select(this.parentNode).select("rect:not(.handle-start):not(.handle-end)")
                  .attr("x", x(d.start))
                  .attr("width", x(d.end) - x(d.start));
              }
            })
            .on("end", async function (event, d) {
              d3.select(this).attr("stroke", null);
              try {
                await axios.put(`http://127.0.0.1:8000/api/task-actions/${d.id}`, {
                  task_template_id: d.task_template_id,
                  start_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.start),
                  end_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.end),
                  deadline: d.deadline,
                  status: d.status
                });
              } catch (error) {
                console.error("Erro ao atualizar datas por handle esquerdo:", error);
              }
            })
        );

      // Handle direito (end)
      bars.append("rect")
        .attr("class", "handle-end")
        .attr("x", d => x(d.end) - handleWidth / 2)
        .attr("y", d => y(d.title)!)
        .attr("width", handleWidth)
        .attr("height", y.bandwidth())
        .attr("fill", "#333")
        .attr("cursor", "ew-resize")
        .call(
          d3.drag()
            .on("start", function (event, d) {
              d3.select(this).raise().attr("stroke", "black");
            })
            .on("drag", function (event, d) {
              const newEnd = x.invert(event.x);
              if (newEnd > d.start) {
                d.end = newEnd;
                d3.select(this).attr("x", x(d.end) - handleWidth / 2);
                d3.select(this.parentNode).select("rect:not(.handle-start):not(.handle-end)")
                  .attr("width", x(d.end) - x(d.start));
              }
            })
            .on("end", async function (event, d) {
              d3.select(this).attr("stroke", null);
              try {
                await axios.put(`http://127.0.0.1:8000/api/task-actions/${d.id}`, {
                  task_template_id: d.task_template_id,
                  start_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.start),
                  end_date: d3.timeFormat("%Y-%m-%dT%H:%M")(d.end),
                  deadline: d.deadline,
                  status: d.status
                });
              } catch (error) {
                console.error("Erro ao atualizar datas por handle direito:", error);
              }
            })
        );
    };

    onMounted(() => {
      fetchTemplates();
      fetchTasks();
    });

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
      applyFilter,
      templates
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

@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(40px) scale(0.98);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
.animate-fade-in-up {
  animation: fade-in-up 0.35s cubic-bezier(.4,0,.2,1);
}
</style>

<style scoped>
svg {
  background: #f9fafb;
  margin: 0 auto;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  display: block;
  width: 100%;
  max-width: 800px;
  height: auto;
}
</style>