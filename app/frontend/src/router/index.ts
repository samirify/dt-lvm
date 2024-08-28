import { createRouter, createWebHistory } from 'vue-router'
import DeploymentDetailsView from '@/views/DeploymentDetailsView.vue'
import DeploymentStatusView from '@/views/DeploymentStatusView.vue'
import HelpMainView from '@/views/help/HelpMainView.vue'
import FAQsView from '@/views/help/FAQsView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'deployment-details',
      component: DeploymentDetailsView
    },
    {
      path: '/deployment-status/:id',
      name: 'deployment-status',
      component: DeploymentStatusView
    },
    {
      path: '/help',
      name: 'help',
      component: HelpMainView
    },
    {
      path: '/help/faqs',
      name: 'help-faqs',
      component: FAQsView
    },
  ]
})

export default router
