<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\VersionControl\BitBucketService;
use App\Service\VersionControl\SVNService;
use Exception;

class RepoProjectService
{
    private string $code;

    public array $availableProjects = [];

    private array $supportedVersionControls = ['bitbucket', 'svn'];

    public function __construct()
    {
        $this->availableProjects = config('projects.availableProjects');
    }

    public function setCode(string $code): RepoProjectService
    {
        $this->code = $code;

        return $this;
    }

    public function getProject(): array
    {
        $project = $this->availableProjects[$this->code] ?? [];

        if (empty($project)) {
            throw new Exception('Project not setup!');
        }

        return $project;
    }

    public function getRepoData(): array
    {
        $project = $this->getProject();

        $vc = $this->getVCFromProject($project);

        if (!is_null($vc)) {
            switch (strtolower($vc)) {
                case 'bitbucket':
                    return (new BitBucketService($this->code))->getData($project)->data();
                case 'svn':
                    return (new SVNService($this->code))->getData($project)->data();
            }
        }

        throw new Exception('Cannot retrieve data!');
    }

    public function downloadRepo(string $downloadPath, string $branch): void
    {
        $vc = $this->getVCFromProject();

        if (!is_null($vc)) {
            switch (strtolower($vc)) {
                case 'bitbucket':
                    (new BitBucketService($this->code))->downloadRepo($downloadPath, $branch);
                    break;
                case 'svn':
                    (new SVNService($this->code))->downloadRepo($downloadPath, $branch);
                    break;
            }
        } else {
            throw new Exception('Cannot retrieve files!');
        }
    }

    private function getVCFromProject(): ?string
    {
        $project = $this->getProject();

        $from = $project['files']['vc'] ?? null;
        return in_array($from, $this->supportedVersionControls) ? $from : null;
    }
}
