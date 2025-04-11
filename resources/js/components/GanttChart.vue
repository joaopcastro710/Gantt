<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Gantt Chart</h2>

    <div class="mb-4">
      <label for="filter" class="mr-2">Filtrar por:</label>
      <select v-model="selectedFilter" @change="applyFilter" id="filter" class="border p-2 rounded">
        <option value="all">Todos</option>
        <option value="month">Por Mês</option>
        <option value="week">Por Semana</option>
        <option value="day">Por Dia</option>
      </select>
    </div>

    <div v-if="successMessage"
         class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
      {{ successMessage }}
    </div>

    <button @click="showCreateModal = true"
            class="mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
      + Nova Tarefa
    </button>

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
          <button @click="deleteTask(editingTask.id)" class="bg-red-600 text-white px-4 py-2 rounded mr-2 hover:bg-red-700">Apagar</button>
          <button @click="cancelEdit" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancelar</button>
        </div>
      </div>
    </div>

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
        tasks.value = response.data;
        applyFilter();
      } catch (error) {
        console.error("Erro ao buscar tarefas:", error);
      }
    };

    const applyFilter = () => {
      const now = new Date();
      if (selectedFilter.value === 'month') {
        filteredTasks.value = tasks.value.filter(task => {
          const startDate = new Date(task.start_date);
          return startDate.getMonth() === now.getMonth() && startDate.getFullYear() === now.getFullYear();
        });
      } else if (selectedFilter.value === 'week') {
        const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
        const endOfWeek = new Date(now.setDate(startOfWeek.getDate() + 6));
        filteredTasks.value = tasks.value.filter(task => {
          const startDate = new Date(task.start_date);
          return startDate >= startOfWeek && startDate <= endOfWeek;
        });
      } else if (selectedFilter.value === 'day') {
        filteredTasks.value = tasks.value.filter(task => {
          const startDate = new Date(task.start_date);
          return startDate.toDateString() === now.toDateString();
        });
      } else {
        filteredTasks.value = tasks.value;
      }
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

  // Configuração de margens e dimensões
  const margin = { top: 20, right: 30, bottom: 30, left: 100 };
  const width = 800 - margin.left - margin.right;
  const height = data.length * 40;

  // Seleção do elemento SVG e limpeza de conteúdo anterior
  const svgEl = d3.select(svg.value);
  svgEl.selectAll("*").remove();

  // Grupo principal do gráfico
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
    .filter(d=> d.start && d.end) as (Task & { start: Date; end: Date })[];

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
            successMessage.value = "🕒 Data inicial atualizada!";
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
            successMessage.value = "🕒 Data final atualizada!";
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
      applyFilter
    };
  }
});
</script>