<script lang="tsx" setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: true
  },
});
</script>

<template>
  <div :class="[
    'app_full_main_loader_container',
    props.show ? '' : 'hide'
  ]">
    <div class="main-full-preloader"></div>
  </div>
</template>
