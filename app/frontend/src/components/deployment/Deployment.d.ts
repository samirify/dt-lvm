export const inProgressStatuses: string[] = ["NEW", "STARTED"]

export type DeploymentPipeline = {
  id: string;
  project_code: string;
  branch: string;
  status_code: string;
  created_at?: string;
};

export type DeploymentProject = {
  id: string;
  name: string;
  branch?: string;
};

export type DeploymentSocketMessage = {
  id: string;
  deploymentId: number | undefined;
  content: string;
  textColor?: string;
  icon?: string;
  deploymentStatus?: string;
  longOperation?: boolean
};

export interface DeploymentStatusProps { }
