<script lang="tsx" setup>
import { faQuestionCircle } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import TopicsSelector from '@/components/help/TopicsSelector.vue'
import { useHelpStore } from './helpStore';
import HelpTopic from './HelpTopic.vue';

const help = useHelpStore()

</script>

<template>
  <div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownHelpTopicsTrigger"
      data-bs-toggle="dropdown" aria-expanded="false">
      Help
      <FontAwesomeIcon :icon="faQuestionCircle" />
    </button>
    <TopicsSelector :topics="help.availableTopics" />
  </div>
  <HelpTopic />
</template>