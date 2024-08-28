<?php

declare(strict_types=1);

namespace App\Service\VersionControl\Interface;

use App\Service\VersionControl\DTO\RepoDataDTO;

interface RepoServiceInterface
{
    public function getData(): RepoDataDTO;

    public function downloadRepo(string $downloadPath, string $branch): void;
}
