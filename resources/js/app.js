import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import{ createApp } from 'vue';
import ChatComponent from './components/ChatComponent.vue';

const app = createApp({});

app.component('chat-component', ChatComponent);
app.mount('#app');
