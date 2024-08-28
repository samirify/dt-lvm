<script lang="tsx" setup>
import { useHelpStore } from './helpStore'
const help = useHelpStore()
</script>

<template>
  <span className="float-start">{{ help.openTopic?.title }}</span>
</template>
