<template>
    <div>
      <h2 class="text-xl font-bold mb-4">Gantt Chart</h2>
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
  
      const drawChart = (data: Task[]) => {
        if (!data || data.length === 0 || !svg.value) return;
  
        const margin = { top: 20, right: 30, bottom: 30, left: 100 };
        const width = 800 - margin.left - margin.right;
        const height = data.length * 40;
  
        const svgEl = d3.select(svg.value);
        svgEl.selectAll("*").remove(); // limpa SVG anterior
  
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
          .domain([
            d3.min(tasksParsed, d => d.start)!,
            d3.max(tasksParsed, d => d.end)!
          ])
          .range([0, width]);
  
        const y = d3.scaleBand()
          .domain(tasksParsed.map(d => d.title))
          .range([0, height])
          .padding(0.2);
  
        // Eixo Y (nomes das tasks)
        g.append("g").call(d3.axisLeft(y));
  
        // Eixo X (datas)
        g.append("g")
          .attr("transform", `translate(0, ${height})`)
          .call(d3.axisBottom(x).ticks(5).tickFormat(d3.timeFormat("%d/%m")));
  
        // Barras da Gantt Chart
        g.selectAll("rect")
          .data(tasksParsed)
          .enter()
          .append("rect")
          .attr("x", d => x(d.start))
          .attr("y", d => y(d.title)!)
          .attr("width", d => x(d.end) - x(d.start))
          .attr("height", y.bandwidth())
          .attr("fill", "#3B82F6");
      };
  
      onMounted(async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/tasks');
    console.log("API response:", response.data);

    // A resposta já É um array, portanto usamos diretamente
    if (Array.isArray(response.data)) {
      tasks.value = response.data;
      drawChart(tasks.value);
    } else {
      console.error("Formato inválido de resposta da API:", response.data);
    }

  } catch (error) {
    console.error("Erro ao buscar as tarefas:", error);
  }
});

  
      return { svg };
    }
  });
  </script>
  
  <style scoped>
  svg {
    background: #f9fafb;
  }
  </style>
  