import './bootstrap';
// extra for resources/js/app.js


import { createApp } from 'vue';
import App from './components/GanttChart.vue';

import axios from 'axios';

axios.get('http://localhost:8000/api/tasks')
    .then(response => {
        console.log(response.data);
    });
                    //Importante: usar sempre http://localhost:8000/api/tasks para pegar os dados do backend e não misturar com Blade, Web.php ...

createApp(App).mount('#app');
