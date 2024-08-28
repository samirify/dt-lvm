<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faClockRotateLeft,
  faEyeDropper,
  faFont,
  faTarpDroplet,
  faTrash,
} from "@fortawesome/free-solid-svg-icons";
import { fontSizes, useCodeViewerStore } from "./codeViewerStore";
import { useDeploymentDetailsStore } from "../deployment/details/deploymentDetailsStore";

const rootApiUrl = import.meta.env.VITE_DEPLOYMENT_API_URL_ROOT

const codeViewer = useCodeViewerStore();
const deploymentDetails = useDeploymentDetailsStore();

const openInNewTab = (url: string) => {
  window.open(url, "_blank", "noopener,noreferrer");
};

</script>

<template>
  <div class="btn-group float-end mb-1">
    <button class="btn btn-outline-info btn-xs" @click="codeViewer.clearConsole()">
      <span class="d-none d-lg-none d-xl-inline me-2">Clear</span>
      <FontAwesomeIcon :icon="faTrash" />
    </button>
    <button :class="['btn', 'btn-outline-info', 'btn-xs', codeViewer.coloured === 'yes' ? 'active' : '']"
      @click="codeViewer.toggleColor(codeViewer.coloured === 'yes' ? 'no' : 'yes')">
      <span class="d-none d-lg-none d-xl-inline me-2">Colored</span>
      <FontAwesomeIcon :icon="faEyeDropper" />
    </button>
    <button :class="['btn', 'btn-outline-info', 'btn-xs', codeViewer.showIcons === 'yes' ? 'active' : '']"
      @click="codeViewer.toggleIcons(codeViewer.showIcons === 'yes' ? 'no' : 'yes')">
      <span class="d-none d-lg-none d-xl-inline me-2">Icons</span>
      <FontAwesomeIcon :icon="faTarpDroplet" />
    </button>
    <button class="btn btn-outline-info btn-xs" @click="openInNewTab(
      `${rootApiUrl}/export-project-log/${deploymentDetails.project?.deploymentId}`
    )">
      <span class="d-none d-lg-none d-xl-inline me-2">Log</span>
      <FontAwesomeIcon :icon="faClockRotateLeft" />
    </button>
    <div role="group" class="dropdown btn-group">
      <button type="button" id="dropdown-basic" aria-expanded="false" data-bs-toggle="dropdown"
        class="btn-xs dropdown-toggle btn btn-outline-info"><span class="d-none d-lg-none d-xl-inline me-2">{{
          fontSizes[codeViewer.fontSize].label }}</span>
        <FontAwesomeIcon :icon="faFont" />
      </button>

      <div x-placement="bottom-end" aria-labelledby="dropdown-basic" class="dropdown-menu">
        <a v-for="fontSizeCode in Object.keys(fontSizes)" :key="fontSizeCode" class="small dropdown-item" role="button"
          tabindex="0" href="#" @click="codeViewer.setFontSize(fontSizeCode)">{{ fontSizes[fontSizeCode].label }}</a>
      </div>
    </div>
  </div>
</template>