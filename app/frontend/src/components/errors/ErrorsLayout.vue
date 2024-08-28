<script lang="tsx" setup>
import { RouterLink } from "vue-router";
import { useHelpStore } from "../help/helpStore";
import BackButton from "../layout/BackButton.vue";

const help = useHelpStore()

const props = defineProps({
  errorTitle: {
    type: String,
  },
  errorDetails: {
    type: String,
    default: ''
  },
})
</script>

<template>
  <main className="container">
    <div className="bg-light p-5 rounded mt-3 shadow">
      <div className="my-3">
        <h1>
          <i className="fa-solid fa-triangle-exclamation"></i>
          {{ props.errorTitle }}
        </h1>
        <p v-if="props.errorDetails" className="lead text-danger pt-3">
          <strong v-html="props.errorDetails"></strong>
        </p>
      </div>

      <div className="text-center mt-3">
        <hr />
        <ButtonGroup>
          <BackButton />
          <RouterLink v-if="help.availableFAQs?.length" class="btn btn-dark" to="/help/faqs">
            <i className="fa-solid fa-question-circle me-2"></i> Frequently
            Asked Questions
          </RouterLink>
        </ButtonGroup>
      </div>
    </div>
  </main>
</template>