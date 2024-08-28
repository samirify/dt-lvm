<script lang="ts" setup>
import { defineProps } from 'vue';
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faAngleRight } from "@fortawesome/free-solid-svg-icons";

const props = defineProps({
    newLine: {
        type: Boolean,
        default: true
    },
    line: {
        type: Object,
        default: null
    },
    iconType: {
        type: String,
        default: 'fas'
    }
});

</script>

<template>
    <div class="line-icon">
        <FontAwesomeIcon :icon="faAngleRight"
            :class="['fa-2xs', 'mt-2', props.newLine ? 'fa-fade' : '', props.newLine ? 'code-new-line-cursor' : '']" />
    </div>
    <div class="line-content">
        <span v-if="props.line" :class="props.line.textColor">
            <FontAwesomeIcon v-if="props.line.icon" :icon="[iconType, props.line.icon]"
                class="console-icons show-console-icons" />
            <span>
                {{ props.line.content }}
            </span>
        </span>
    </div>
</template>