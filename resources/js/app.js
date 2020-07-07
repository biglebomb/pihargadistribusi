require('./bootstrap');

window.Vue = require('vue');

import App from '../assets/js/components/App.vue';
import { CDataTable, CTooltip } from '@coreui/vue';

const app = new Vue({
    el: '#app',
    components: {
        App
    },
    render: h => h(App)
});
