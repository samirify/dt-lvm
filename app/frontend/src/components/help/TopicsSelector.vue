<script lang="tsx" setup>
import { RouterLink } from 'vue-router';
import { useHelpStore, type HelpTopic } from './helpStore';

interface TopicsSelectorProps {
  topics: HelpTopic[];
  fullWidth?: boolean;
  topicsTitle?: boolean;
}

const props = withDefaults(defineProps<TopicsSelectorProps>(), {
  topics: () => [],
  fullWidth: false,
  topicsTitle: true
})

const help = useHelpStore()


</script>

<template>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton1" role="menu">
    <span v-if="props.topicsTitle" class="text-muted dropdown-item-text"><small
        class="text-uppercase">Topics</small></span>
    <a v-for="topic in topics" :key="topic.code" class="dropdown-item" @click="help.setOpenTopic(topic)"
      href="javascript:void(0)">{{ topic.title }}</a>
    <hr class="dropdown-divider" role="separator" />

    <RouterLink class="dropdown-item" :to="'/help/faqs'">
      FAQs
    </RouterLink>
    <RouterLink class="dropdown-item" :to="'/help'">
      All topics
    </RouterLink>
  </div>
</template>