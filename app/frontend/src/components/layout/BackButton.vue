<script lang="tsx" setup>
import { faChevronLeft } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<template>
  <button type="button" class="btn btn-secondary" @click="router.go(-1)">
    <FontAwesomeIcon :icon="faChevronLeft" class="me-2" /> Back
  </button>
</template>
