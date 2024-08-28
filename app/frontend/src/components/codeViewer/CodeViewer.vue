<script lang="ts" setup>
import CodeViewerControls from "@/components/codeViewer/CodeViewerControls.vue";
import CodeViewerLog from "@/components/codeViewer/CodeViewerLog.vue";
import { useCodeViewerStore } from "./codeViewerStore";

const props = defineProps({
  serverIsConnected: {
    type: Boolean,
    default: false
  },
});

const appVersion = import.meta.env.VITE_REVERB_APP_VERSION;

const codeViewerStore = useCodeViewerStore();
</script>

<template>
  <div :class="[
    'col-xl-9',
    'col-lg-8',
    'col-md-6',
    'console-area',
    'shadow',
    'p-4',
    'h-100',
    'overflow-hidden',
    codeViewerStore.coloured !== 'yes' ? 'console-no-colors' : '',
    codeViewerStore.showIcons !== 'yes' ? 'console-no-icons' : ''
  ]">
    <div class="row h-100 mb-3 mx-0 overflow-hidden">
      <div class="h-100">
        <div class="row h-100">
          <div class="col-12 h-100 p-0 m-0">
            <div class="card h-100 border-0">
              <div class="card-header border-0 px-0">
                <div class="row gx-1 console-heading">
                  <div class="col-6">
                    <div class="float-start mb-1">
                      <div class="viewer-version mb-2"
                        :title="props.serverIsConnected ? 'Server is connected' : 'Server is disconnected'">
                        Samirify Deployment v{{ appVersion }} <span
                          :class="['align-middle', `border-${props.serverIsConnected ? 'green' : 'red'}`, 'rounded-circle', 'd-inline-block']"></span>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <CodeViewerControls />
                  </div>
                </div>
              </div>
              <div class="card-body p-0 m-0 overflow-hidden">
                <div class="h-100">
                  <div class="h-100 scrollable overflow-auto">
                    <CodeViewerLog />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>