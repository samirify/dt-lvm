<script setup lang="tsx">
import HelpFooter from '@/components/help/HelpFooter.vue';
import BackButton from '@/components/layout/BackButton.vue';
import FooterComponent from '@/components/layout/FooterComponent.vue';
import HeaderComponent from '@/components/layout/HeaderComponent.vue';
import { faQuestionCircle, faSearch } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useHelpStore, type HelpTopic } from '@/components/help/helpStore';
import { computed, reactive } from 'vue';
import { storeToRefs } from 'pinia';

const help = useHelpStore()
const { availableTopics } = storeToRefs(help);

const state = reactive<{ searchTerm: string }>({
  searchTerm: ''
})

const filteredTopics = computed(() => {
  return availableTopics.value.filter((topic: HelpTopic) => {
    return topic.title.toUpperCase().includes(state.searchTerm.toUpperCase());
  })
})

const handleTopicsSearch = (e: any) => {
  state.searchTerm = e.target.value
};

</script>

<template>
  <HeaderComponent />
  <div class="container shadow rounded-bottom">
    <div class="rounded px-3 py-5">
      <h1 class=" text-white text-center mb-5">Help topics</h1>
      <div class="text-center mb-5">
        <div role="group" class="border border-2 btn-group btn-group-sm">
          <BackButton />
          <RouterLink v-if="help.availableFAQs?.length" class="btn btn-primary" :to="'/help/faqs'">
            <FontAwesomeIcon :icon="faQuestionCircle" class="me-2" /> Frequently
            Asked Questions
          </RouterLink>
        </div>
      </div>
      <div class="d-flex justify-content-center">
        <div class="search-box w-100">
          <form><input class="form-control p-2 bg-transparent" placeholder="What are you looking for?"
              @input="handleTopicsSearch" /><button class="btn btn-light" type="button">
              <FontAwesomeIcon :icon="faSearch" />
            </button></form>
        </div>
      </div>

      <template v-if="filteredTopics.length">
        <div v-for="topic in filteredTopics" :key="topic.code" class="shadow mb-2 card">
          <div class="card-body">
            <div class="card-title h5">{{ topic.title }}</div>
            <div class="my-3 text-muted card-subtitle h6">{{ topic.subTitle }}</div>
            <div class="card-body">
              <button type="button" class="float-end btn btn-primary" @click="help.setOpenTopic(topic)">View
                topic</button>
            </div>
          </div>
        </div>
      </template>
      <h5 v-else class="bg-white p-5 text-center rounded">No available topics matching your query!</h5>
    </div>
    <HelpFooter />
  </div>
  <FooterComponent />
</template>
