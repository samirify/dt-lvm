<script setup lang="tsx">
import ErrorsLayout from './ErrorsLayout.vue';

const props = defineProps({
  errorTitle: {
    type: String,
  },
  errorDetails: {
    type: String,
    default: ''
  },
})

</script>

<template>
  <ErrorsLayout :errorTitle="props.errorTitle || 'Opps!'" :errorDetails="props.errorDetails" />
</template>
