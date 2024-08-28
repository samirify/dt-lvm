<?php

namespace App\Http\Controllers;

use App\Service\DeploymentService;
use App\Service\RepoProjectService;
use App\Traits\SAAApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    use SAAApiResponse;

    public array $availableProjects = [];

    public function __construct(
        private readonly DeploymentService $deploymentService,
        private readonly RepoProjectService $repoProjectService,
    ) {
        $this->availableProjects = config('projects.availableProjects');
    }

    public function projectsList(): Response|JsonResponse
    {
        $projects = [];

        foreach ($this->availableProjects as $key => $project) {
            $projects[] = [
                'id' => $key,
                'name' => $project['name']
            ];
        }

        return $this->successResponse([
            'projects' => $projects,
            'deployments' => $this->deploymentService->getDeploymentRecords([
                'id',
                'project_code',
                'branch',
                'status_code',
                'created_at'
            ])
        ]);
    }

    public function projectData(string $projectCode): Response|JsonResponse
    {
        $repoData = $this->repoProjectService
            ->setCode($projectCode)
            ->getRepoData();

        return $this->successResponse($repoData);
    }

    public function initiateProjectDeployment(Request $request): Response|JsonResponse
    {
        $deploy = $this->deploymentService->deploy($request->all());

        return $this->successResponse($deploy);
    }

    public function reDeployProject(int $deploymentId): Response|JsonResponse
    {
        $pipeline = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

        if (!$pipeline) {
            return $this->errorResponse('Pipeline not found!', 404);
        }

        if ('STARTED' !== $pipeline['status_code']) {
            $this->deploymentService->reDeploy($deploymentId);
        }

        return $this->successResponse([
            'status' => $pipeline['status_code']
        ]);
    }

    public function projectStatus(int $deploymentId): Response|JsonResponse
    {
        $pipeline = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

        if (!$pipeline) {
            return $this->errorResponse('Pipeline not found!', 404);
        }

        $this->deploymentService->projectStatus($deploymentId);

        return $this->successResponse([
            'project' => [
                'id' => $pipeline['project_code'],
                'status' => $pipeline['status_code'],
                'deploymentId' => $deploymentId,
                'name' => $this->availableProjects[$pipeline['project_code']]['name'],
                'branch' => $pipeline['branch']
            ]
        ]);
    }

    public function exportProjectDeploymentLog(int $deploymentId): Response
    {
        $deploymentRecord = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

        if (!$deploymentRecord) {
            return response($this->deploymentService->projectDeploymentLogError('Project not found!'));
        }

        $logContent = $this->deploymentService->getProjectDeploymentLogContent($deploymentId, $deploymentRecord['completed_log']);

        return response($logContent['html']);
    }
}
