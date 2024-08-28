<script setup lang="ts">
import DeploymentDetails from '@/components/deployment/details/DeploymentDetails.vue'
import FooterComponent from '@/components/layout/FooterComponent.vue';
import HeaderComponent from '@/components/layout/HeaderComponent.vue';
</script>

<template>
  <HeaderComponent />
  <main class="d-flex flex-column flex-grow-1 overflow-hidden">
    <DeploymentDetails />
  </main>
  <FooterComponent />
</template>
