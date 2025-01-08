<template>
    <div class="flex flex-col h-[500px]">
        <div class="flex-1 items-center">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-green-500 mr-2">{{ user.name }}</h1>
            <span :class="isUserOnline ? 'bg-green-500' : 'bg-red-500'"
                class="inline-block w-3 h-3 rounded-full rounded-full"></span>
        </div>


        <!-- Message -->
        <div ref="messageContainer" class="overflow-y-auto flex-1 flex flex-col-reverse border-t border-gray-200  ">
            <div class="space-y-4">
                <div v-for="message in messages" :key="message.id"
                    :class="{ 'text-right': message.sender_id === currentUser.id }" class="mb-4">

                    <div :class="message.sender_id === currentUser.id ? 'bg-gray-200 text-yellow-800 '  : 'text-left text-gray-800 dark:text-gray-100'"
                        class="inline-block px-4 py-2 rounded-lg">

                        <p>{{ message.text }}</p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatTime(message.created_at) }}</span>

                    </div>
                </div>
            </div>
        </div>

        <!-- message input -->
        <div class="border-t  pt-4">
            <form @submit.prevent="sendMessage">
                <div class="flex items-center gap-4">
                    <input v-model="newMessage" @keydown="sendTypingEvent" type="text" placeholder="Type a message"
                        class="flex-1 px-4 py-2 border border-gray-200 rounded-lg" />

                    <button type="submit"
                        class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 disabled:opacity-25">
                        <i class="fas fa-paper-plane"></i>
                        <span class="ml-2">Send</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <small v-if="isUserTyping" class="text-gray-500">{{ user.name }} is typing...</small>




</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    currentUser: {
        type: Object,
        required: true
    }
});

const messages = ref([]);
const newMessage = ref('');
const messageContainer = ref(null);
const isUserTyping = ref(false);
const isUserTypingTimer = ref(null);
const isUserOnline = ref(false);


watch(
    messages,
    () => {
        nextTick(() => {
            messageContainer.value.scrollTo({
                top: messageContainer.value.scrollHeight,
                behavior: 'smooth'
            });
        });
    },
    { deep: true }
);


const fetchMessages = async () => {
    try {
        const response = await axios.get(`http://127.0.0.1:8000/messages/${props.user.id}`);
        messages.value = response.data;
    } catch (error) {
        console.error("Failed to fetch messages:", error);
    }
}

const sendMessage = async () => {
    if (newMessage.value.trim() !== '') {

        try {
            const response = await axios.post(`http://127.0.0.1:8000/messages/${props.user.id}`, {
                message: newMessage.value
            });

            messages.value.push(response.data);
            newMessage.value = '';
        } catch (error) {
            console.error("Failed to send message:", error);
        }
    }
};



const sendTypingEvent = () => {
    Echo.private(`chat.${props.user.id}`).whisper("typing", {
        userID: props.currentUseruser.id
    });
};

const formatTime = (datetime) => {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(datetime).toLocaleString([], options);
};

onMounted(() => {
    // console.log('onMounted');
    fetchMessages();

    Echo.join(`presence.chat`)
        .here((users) => {
            isUserOnline.value = users.some((user) => user.id === props.user.id);
        })
        .joining((user) => {
            if (user.id === props.user.id) isUserOnline.value = true;
        })
        .leaving((user) => {
            if (user.id === props.user.id) isUserOnline.value = false;
        });


    Echo.private(`chat.${props.currentUseruser.id}`)
        .listen('MessageSend', (response) => {
            messages.value.push(response.message);
        })
        .listenForWhisper('typing', (response) => {
            isUserTyping.value = response.userID === props.user.id;

            if (isUserTyping.value) {
                clearTimeout(isUserTypingTimer.value);
            }

            isUserTypingTimer.value = setTimeout(() => {
                isUserTyping.value = false;
            }, 1000);
        });
});



</script>
