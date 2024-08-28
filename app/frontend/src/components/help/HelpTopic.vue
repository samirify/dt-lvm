<script lang="tsx" setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useHelpStore } from './helpStore';
import { computed, reactive } from 'vue';
import { faChevronLeft } from '@fortawesome/free-solid-svg-icons';
import axios from '@/lib/axios';

const help = useHelpStore()

const state = reactive<{
    isLoading: boolean
    content: string
}>({
    isLoading: true,
    content: ''
})

const isOpen = computed(() => {
    return Object.keys(help.openTopic || {}).length !== 0
})

const fetchContent = (code: string): void => {
    axios.get(`/help/topic/${code}`)
        .then(response => {
            if (response.data.success) {
                state.content = response.data.data["content"]
            }
        })
        .catch(error => {
            console.error('Initialise Error: ', error);
        })
        .finally(() => {
            state.isLoading = false;
        });
}

const content = computed(() => {
    if (help.openTopic?.code) fetchContent(help.openTopic.code)
    return state.content
})
</script>

<template>
    <div :class="`panel-wrap ${isOpen ? 'open' : ''}`">
        <div class="panel_overlay"></div>
        <div class="panel">
            <div class="panel_header">
                <div class="panel_close_btn" role="button" tabindex="0" @click="help.clearTopic()">
                    <FontAwesomeIcon :icon="faChevronLeft" />
                </div>
                <div class="panel_title_container">
                    <h2 class="panel_title"><span class="float-start">{{ help.openTopic?.title }}</span></h2>
                    <div class="panel_subtitle" v-if="help.openTopic?.subTitle">{{ help.openTopic?.subTitle }}</div>
                </div>
            </div>

            <div class="panel_content" v-html="content"></div>
        </div>
    </div>
</template>