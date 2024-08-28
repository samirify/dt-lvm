<?php

declare(strict_types=1);

namespace App\Service\VersionControl;

use App\Service\VersionControl\DTO\RepoDataDTO;
use App\Service\VersionControl\Interface\RepoServiceInterface;
use Exception;

class SVNService implements RepoServiceInterface
{
    private array $project = [];
    private array $availableProjects = [];

    public function __construct(
        private readonly string $code
    ) {
        $this->availableProjects = config('projects.availableProjects');
        $this->project = $this->availableProjects[$this->code];
    }

    public function getData(): RepoDataDTO
    {
        // Implement logic for SVN
        throw new Exception('SVN not implemented yet!');

        // return RepoDataDTO::create([], []);
    }

    public function downloadRepo(string $downloadPath, string $branch): void
    {
        // Implement logic for SVN
        throw new Exception('SVN not implemented yet!');
    }
}
