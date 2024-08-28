<script setup lang="ts">
import HelpFooter from '@/components/help/HelpFooter.vue';
import { useHelpStore } from '@/components/help/helpStore';
import BackButton from '@/components/layout/BackButton.vue';
import FooterComponent from '@/components/layout/FooterComponent.vue';
import HeaderComponent from '@/components/layout/HeaderComponent.vue';
import { CAccordion, CAccordionBody, CAccordionHeader, CAccordionItem } from '@coreui/bootstrap-vue';
import { faComment } from '@fortawesome/free-regular-svg-icons';
import { faList, faQuestionCircle } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { RouterLink } from 'vue-router';

const help = useHelpStore()
</script>

<template>
  <HeaderComponent />
  <div class="container shadow rounded-bottom">
    <div class="rounded px-3 py-5">
      <h1 class=" text-white text-center mb-5">Frequently Asked Questions</h1>
      <div class="text-center mb-5">
        <div role="group" class="border border-2 btn-group btn-group-sm">
          <BackButton />
          <RouterLink class="btn btn-primary" :to="'/help'">
            <FontAwesomeIcon :icon="faList" class="me-2" /> Browse Help topics
          </RouterLink>
        </div>
      </div>
      <CAccordion>
        <CAccordionItem v-for="(faq, index) in help.availableFAQs" :key="index">
          <CAccordionHeader>
            <FontAwesomeIcon :icon="faQuestionCircle" class="me-2" />{{ faq.question }}
          </CAccordionHeader>
          <CAccordionBody>
            <strong class="text-success">
              <FontAwesomeIcon :icon="faComment" class="me-2" />Answer
            </strong> <span v-html="faq.answer"></span>
          </CAccordionBody>
        </CAccordionItem>
      </CAccordion>
      <HelpFooter />
    </div>
  </div>
  <FooterComponent />
</template>
