<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$projectFilePath = __DIR__ . '/../config/project-pipelines.yaml';

if (!file_exists($projectFilePath)) {
    throw new Exception('Could find projects configuration file!');
}

$projects = Yaml::parseFile($projectFilePath) ?? [];

return $projects;
