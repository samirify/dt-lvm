import { defineStore } from "pinia";

export type DeploymentProject = {
  id: string
  deploymentId?: string
  name: string
  branch?: string
}

interface DeploymentDetailsStoreState {
  project: DeploymentProject | null,
}

const initialState: DeploymentDetailsStoreState = {
  project: null,
};

export const useDeploymentDetailsStore = defineStore({
  id: 'deploymentDetails',
  state: () => (initialState),
  actions: {
    setProject(value: DeploymentProject | null) {
      this.project = value;
    },
  }
})