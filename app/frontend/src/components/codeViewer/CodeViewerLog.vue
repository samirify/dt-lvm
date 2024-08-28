<script lang="ts" setup>
// import { ScrollTopButton } from "@/components/codeViewer/ScrollTopButton.vue";
import CodeLine from "@/components/codeViewer/CodeLine.vue";
import { useCodeViewerStore } from "./codeViewerStore";
import { onMounted, ref } from "vue";

const codeViewer = useCodeViewerStore()

const viewerRef = ref<null | HTMLDivElement>(null);
const bottomRef = ref<null | HTMLDivElement>(null);

onMounted(() => {
  bottomRef.value?.scrollIntoView({ behavior: "smooth" });
  const navbar = document.querySelector<HTMLElement>('.navbar')
  window.scrollBy(0, - (navbar?.offsetHeight || 0));
})

</script>

<template>
  <pre :class="['h-100', 'overflow-auto', 'w-100', 'm-0', 'p-0', `font-${codeViewer.fontSize}`]">
    <div ref="viewerRef"></div>
    <ul class="px-0">
      <template v-for="(line, index) in codeViewer.codeLines" :key="index" >
        <li class="line text-wrap">
          <CodeLine
            :newLine="false"
            :line="line"
          />
        </li>
        <li class="line text-wrap" v-if="line.longOperation">
          <CodeLine
            :newLine="false"
            :line="{
              ...line,
              textColor: 'info',
              content: 'This operation may take a long time! Please be patient.',
              icon: 'clock'
            }"
            :iconType="'far'"
          />
      </li>
      </template>
<li class="line">
  <CodeLine />
</li>
</ul>
<span ref="bottomRef"></span>
</pre>
</template>