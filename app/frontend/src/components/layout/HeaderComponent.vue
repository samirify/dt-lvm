<script setup lang="tsx">
import { RouterLink } from 'vue-router'
import { useDeploymentDetailsStore } from '../deployment/details/deploymentDetailsStore';
import logoSrc from "@/assets/images/logo.png"
import HelpTrigger from '@/components/help/HelpTrigger.vue';

const deploymentDetails = useDeploymentDetailsStore()
</script>

<template>
  <nav class="px-1 py-3 shadow navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container-fluid">
      <RouterLink class="navbar-brand" to="/"><img class="mb-1" :src="logoSrc" alt="Logo" height="18" /> Samirify
        Deployment</RouterLink><button type="button" aria-label="Toggle navigation"
        class="navbar-toggler collapsed"><span class="navbar-toggler-icon"></span></button>
      <div class="me-auto my-2 my-lg-0 navbar-nav navbar-nav-scroll" style="max-height: 100px;">
        <HelpTrigger />
      </div>
      <div v-if="deploymentDetails.project" class="justify-content-end navbar-collapse collapse"><span
          class="navbar-text"><span>Project: <span class="text-primary">{{ deploymentDetails.project?.name
              }}</span></span></span></div>
    </div>
  </nav>
</template>