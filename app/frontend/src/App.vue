<script setup lang="tsx">
import { onMounted, reactive } from 'vue';
import { RouterView } from 'vue-router'
import axios from './lib/axios';
import { useHelpStore } from './components/help/helpStore';
import { useMainStore } from './components/main/mainStore';
import MainFullPreloader from './components/layout/preloaders/MainFullPreloader.vue';

const state = reactive<{
  isLoading: boolean
}>({
  isLoading: true,
});

const main = useMainStore()
const help = useHelpStore()

onMounted(async () => {
  try {
    const response = await axios.get('/initialise');

    if (response.data.success) {
      help.setAvailableTopics(response.data.data.help?.topics || []);
      help.setAvailableFAQs(response.data.data.help?.faqs || []);
    }
  } catch (error) {
    main.appError = {
      title: "Failed to Initialise!",
      message: error as string,
    }
    console.error('Initialise Error: ', error);
  } finally {
    state.isLoading = false;
  }
});

</script>

<template>
  <MainFullPreloader :show="state.isLoading" />
  <RouterView />
</template>

<style scoped></style>
