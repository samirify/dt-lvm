<script setup lang="ts">
import axios from '@/lib/axios';
import { faBan, faCircleNotch } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { onMounted, reactive, ref } from 'vue';
import DeploymentsTable from './table/DeploymentsTable.vue';
import type { DeploymentPipeline, DeploymentProject } from '../Deployment.d';
import { steps, type HeaderMessage } from './deploymentDetailsUtil';
import type { Choice } from './form/Forms.d';
import { useDeploymentDetailsStore } from './deploymentDetailsStore';
import * as yup from 'yup';
import { Field, Form } from 'vee-validate'
import { useCodeViewerStore } from '@/components/codeViewer/codeViewerStore';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref()

const formData = ref({
  project: "",
  branch: "",
})

const state = reactive<{
  projects: DeploymentProject[]
  availableProjects: Choice[]
  deployments: DeploymentPipeline[]
  branches: Choice[]
  isLoading: boolean
  isSubmitting: boolean
  headerMessage: HeaderMessage
}>({
  projects: [],
  availableProjects: [],
  deployments: [],
  branches: [],
  isLoading: true,
  isSubmitting: false,
  headerMessage: steps.selectProject
});

const projectFormSchema = yup.object().shape({
  project: yup.string().required("Please select a project"),
  branch: yup.string().required("Please select a branch"),
});

const submit = async (values: any, { resetForm }: any) => {
  state.isSubmitting = true
  state.headerMessage = {
    ...steps.deploy,
    isLoading: true,
    variant: "warning",
  };
  await axios.post('/deploy-project', values)
    .then((res) => {
      if (res.data.success) {
        codeViewer.clearConsole();
        router.push(`/deployment-status/${res.data.data.deploymentId}`);

        resetForm();
      } else {
        // handle errors
      }
    })
    .catch((err) => {
      console.log("err: ", err);
      state.headerMessage = {
        ...steps.deploy,
        isLoading: false,
        variant: "success",
      };
    })
    .finally(() => state.isSubmitting = false);
}

const deploymentProject = useDeploymentDetailsStore()
const codeViewer = useCodeViewerStore()

const isSubmitBtnDisabled = (meta: any) => {
  const disabled = (!meta.valid || !meta.dirty) || state.isLoading || state.isSubmitting ||
    state.branches.length <= 0 ||
    state.availableProjects.length <= 0

  return disabled
}

const determineHeaderMsg = (branches: Choice[], branchSelected?: boolean) => {
  if (branches.length > 0) {
    if (branchSelected) {
      state.headerMessage = {
        ...steps.deploy,
        isLoading: false,
        variant: "success",
      }
    } else {
      state.headerMessage = {
        ...steps.selectBranch,
        isLoading: false,
        variant: "light",
      }
    }
  } else {
    state.headerMessage = {
      text: "No branches found! Select another project",
      isLoading: false,
      variant: "danger",
      icon: faBan,
    }
  }
};

const getRepoData = async (projectCode: string) => {
  state.branches = []
  state.isLoading = true
  state.headerMessage = {
    ...steps.selectBranch,
    isLoading: true,
    variant: "warning",
  };
  await axios.get(`/project-data/${projectCode}`)
    .then((res) => {
      if (res.data.success) {
        state.branches = res.data.data.branches;
        determineHeaderMsg(res.data.data.branches);
      } else {
        // TODO: handle errors
      }
    })
    .catch((err) => {
      determineHeaderMsg([]);
      console.log("err: ", err);
    })
    .finally(() => {
      state.isLoading = false;
    });
};

onMounted(async () => {
  try {
    state.headerMessage = {
      ...steps.selectProject,
      isLoading: true,
      variant: "warning",
    }
    const response = await axios.get('/projects');
    state.projects = response.data.data.projects;
    state.availableProjects = response.data.data.projects;
    state.deployments = response.data.data.deployments;
  } catch (error) {
    state.headerMessage = {
      text: "Failed to retrieve projects!",
      isLoading: false,
      variant: "danger",
      icon: faBan,
    };
    console.error('Error fetching projects', error);
  } finally {
    state.isLoading = false;
    state.headerMessage = {
      ...steps.selectProject,
      isLoading: false,
      variant: "light",
    };
  }
});

</script>

<template>
  <div class="container d-flex flex-column">
    <div class="row mb-0 flex-grow-1">
      <div class="col">
        <div class="mx-1 my-5 shadow-sm border-0 rounded-2 card">
          <div class="bg-white p-0 border-0 rounded-2 card-header">
            <div role="alert"
              :class="`fade rounded-0 m-0 border-top-0 border-start-0 border-end-0 border-3 rounded-2 alert alert-${state.headerMessage.variant} show`">
              <h4 v-if="state.isLoading" class="my-4 text-center">
                <FontAwesomeIcon :icon="faCircleNotch" :spin="true" /> {{ state.headerMessage.loadingMessage }}
              </h4>
              <h4 v-else class="my-4 text-center">
                <FontAwesomeIcon :icon="state.headerMessage.icon" /> {{ state.headerMessage.text }}
              </h4>
            </div>
          </div>
          <div class="py-4 px-5 bg-light rounded-2 card-body">
            <Form ref="form" :validation-schema="projectFormSchema" @submit="submit" :initial-values="formData"
              v-slot="{ meta }">
              <div class="row">
                <div class="col-sm-6">
                  <div class=" sfy-field mb-3"><label class="form-label" for="validationProject">Project</label>
                    <Field as="select" name="project" :disabled="state.projects.length <= 0" class="  form-select"
                      id="validationProject" @change="(e: any) => {
                        if (e.target.value) {
                          getRepoData(e.target.value);
                          deploymentProject.setProject(state.availableProjects.find((p) => p.id === e.target.value) || null)
                        } else {
                          state.headerMessage = {
                            ...steps.selectProject,
                            isLoading: false,
                            variant: 'light',
                          };
                          state.branches = []
                        }
                      }">
                      <option value="">--- Select ---</option>
                      <option v-for="project in state.projects" :key="project.id" :value="project.id">{{ project.name }}
                      </option>
                    </Field><span class="bottom-border"></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class=" sfy-field mb-3"><label class="form-label" for="validationBranch">Branch</label>
                    <Field as="select" name="branch" :disabled="state.branches.length <= 0" class="  form-select"
                      id="validationBranch" @change="(e: any) => {
                        if (e.target.value) {
                          determineHeaderMsg(state.branches, true);
                        } else {
                          determineHeaderMsg(state.branches);
                        }
                      }">
                      <option value="">--- Select ---</option>
                      <option v-for="branch in state.branches" :key="branch.id" :value="branch.id">{{ branch.name }}
                      </option>
                    </Field><span class="bottom-border"></span>
                  </div>
                </div>
              </div>
              <div class="py-3 text-end row">
                <div class="col"><button type="submit" :disabled="isSubmitBtnDisabled(meta)" value="Deploy"
                    class="px-4 btn btn-primary btn-sm">Deploy</button></div>
              </div>
            </Form>
            <hr />
            <DeploymentsTable title="Previously deployed pipelines" :deployments="state.deployments" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
