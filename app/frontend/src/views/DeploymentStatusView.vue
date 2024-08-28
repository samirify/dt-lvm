<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { v4 as uuidv4 } from "uuid";
import CodeViewer from '@/components/codeViewer/CodeViewer.vue'
import { useCodeViewerStore } from '@/components/codeViewer/codeViewerStore';
import { inProgressStatuses, type DeploymentSocketMessage } from "@/components/deployment/Deployment.d";

import Echo from 'laravel-echo';

const codeViewer = useCodeViewerStore()
const deploymentDetails = useDeploymentDetailsStore();

const route = useRoute();
const router = useRouter();

const state = reactive<{
  isLoading: boolean
  inProgress: boolean
  isRedeploying: boolean
  serverIsConnected: boolean
}>({
  isLoading: true,
  inProgress: false,
  isRedeploying: false,
  serverIsConnected: false
})

const deploymentId = parseInt(route.params.id.toString());

import Pusher from 'pusher-js';
import { faChevronLeft, faCircleNotch } from "@fortawesome/free-solid-svg-icons";
import { useDeploymentDetailsStore } from "@/components/deployment/details/deploymentDetailsStore";
import { onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "@/lib/axios";
import HeaderComponent from "@/components/layout/HeaderComponent.vue";
import FooterComponent from "@/components/layout/FooterComponent.vue";
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});

window.Echo.connector.pusher.connection.bind('state_change', function (states: any) {
  switch (states.current.toString().toLowerCase()) {
    case 'connected':
      state.serverIsConnected = true;
      break;
    default:
      state.serverIsConnected = false
      codeViewer.addCodeLines({
        id: uuidv4(),
        deploymentId,
        textColor: "red",
        icon: "ban",
        content: "Could not load from server. Server is disconnected!",
      } as DeploymentSocketMessage)
      break;
  }
})

window.Echo.channel('samirify-deploy')
  .listen('PipelineMessage', (msg: any) => {
    if (msg.deploymentId.toString() === deploymentId.toString()) {
      if (!inProgressStatuses.includes(msg.deploymentStatus)) {
        state.inProgress = false;
      }
      codeViewer.addCodeLines(msg as DeploymentSocketMessage)
    }
  })

const handleApiErrors = (errors: string[]): void => {
  codeViewer.addCodeLines({
    id: uuidv4(),
    deploymentId: deploymentId,
    textColor: "red",
    icon: "times",
    content: errors.join("<br />"),
  } as DeploymentSocketMessage)
};

const reDeployProject = async () => {
  state.isRedeploying = true;
  state.inProgress = true;
  await axios.post(`/re-deploy-project/${deploymentId}`)
    .then((res) => {
      if (res.data.success) {
        if (inProgressStatuses.includes(res.data.data.status)) {
          codeViewer.addCodeLines({
            id: uuidv4(),
            deploymentId: deploymentId,
            textColor: "red",
            icon: "hand",
            content:
              "Another deployment is already in progress! Please wait until it finishes..",
            deploymentStatus: "STARTED",
          } as DeploymentSocketMessage)
        } else {
          codeViewer.clearConsole()
        }
      } else {
        handleApiErrors(res.data.errors);
      }
    })
    .catch((error: any) => {
      state.inProgress = false
      handleApiErrors(error.response.data.errors);
    })
    .finally(() => state.isRedeploying = false);
};

onMounted(async () => {
  try {
    codeViewer.addCodeLines({
      id: uuidv4(),
      deploymentId,
      textColor: "info",
      icon: "clock",
      content: "Loading status from server..",
    } as DeploymentSocketMessage)

    const response = await axios.get(`/project-status/${deploymentId}`);
    if (response.data.success) {
      deploymentDetails.setProject(response.data.data.project);
      if (inProgressStatuses.includes(response.data.data.project.status)) {
        state.inProgress = true;
      }
    } else {
      handleApiErrors(response.data.data.errors);
    }
  } catch (error: any) {
    handleApiErrors(error.response.data.errors);
  } finally {
    state.isLoading = false
  }
});
</script>
<template>
  <HeaderComponent />
  <main className="d-flex flex-column flex-grow-1 overflow-hidden">
    <div class="container-fluid d-flex flex-column vh-100 overflow-hidden mx-0 px-0">
      <div class="row mb-0 flex-grow-1 overflow-hidden">
        <div class="col-xl-3 col-lg-4 col-md-6 shadow p-4 card border-0 rounded-0 overflow-hidden">
          <div role="group" class="btn-group"><button type="button" class="btn btn-outline-secondary"
              @click="() => router.push('/')">
              <FontAwesomeIcon :icon="faChevronLeft" size="sm" /> Back
            </button><button type="button" class="btn btn-primary" :disabled="state.isRedeploying || state.inProgress"
              @click="reDeployProject">
              <span v-if="state.isRedeploying || state.inProgress">
                <FontAwesomeIcon :icon="faCircleNotch" :spin="true" class="me-2" />Deploying...
              </span>
              <span v-else>Re-deploy</span>
            </button></div><br />
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-3 d-flex align-items-center justify-content-between"><span
                class="text-muted small">Project</span><strong class="small">{{ deploymentDetails.project?.name
                }}</strong></li>
            <li class="list-group-item px-3 d-flex align-items-center justify-content-between"><span
                class="text-muted small">Branch</span><strong class="small">{{ deploymentDetails.project?.branch
                }}</strong></li>
            <li class="list-group-item px-3 d-flex align-items-center justify-content-between"><span
                class="text-muted small">Live updates</span><strong
                :class="['small', `text-${state.serverIsConnected ? 'success' : 'danger'}`]"><span
                  :class="['align-middle', 'me-1', 'border', 'border-4', `border-${state.serverIsConnected ? 'success' : 'danger'}`, 'rounded-circle', 'd-inline-block']"></span>{{
                    state.serverIsConnected ? "Connected" : "Disconnected" }}</strong>
            </li>
          </ul>
        </div>
        <CodeViewer :server-is-connected="state.serverIsConnected" />
      </div>
    </div>
  </main>
  <FooterComponent />
</template>

<style></style>
