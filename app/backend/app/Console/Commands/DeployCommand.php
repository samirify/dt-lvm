<?php

namespace App\Console\Commands;

use App\Exceptions\Deployment\DeploymentInProgressException;
use App\Service\DeploymentService;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {id} {--live-messages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy application. This command will start application deployment...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly DeploymentService $deploymentService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $basePath = base_path();
        $downloadPath = $basePath . '/release-files';

        if (!is_dir($downloadPath)) mkdir($downloadPath, 0777, true);

        $deploymentId = $this->argument('id');
        $availableProjects = config('projects.availableProjects');

        $key = new Key('deployment-' . $deploymentId);
        $store = new FlockStore();
        $factory = new LockFactory($store);

        try {
            $startTime = new DateTime();

            $deploymentRecord = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

            $lock = $factory->createLockFromKey($key);

            if (!$lock->acquire() && ($deploymentRecord && in_array($deploymentRecord['status_code'], ['STARTED']))) {
                throw new DeploymentInProgressException();
            }

            $logsPath = "{$basePath}/release-files/deployment-{$deploymentId}-logs";

            $this->deploymentService->clearLogFiles($logsPath);

            $this->deploymentService->clearDeploymentRecordLogMessages((int)$deploymentId);

            $sendLiveMessages = $this->option('live-messages');
            if ($sendLiveMessages) $this->sendLiveMessages($deploymentId);

            if (!is_dir($logsPath)) mkdir($logsPath, 0777, true);

            $deploymentProject = $availableProjects[$deploymentRecord['project_code']] ?? [];

            if (empty($deploymentProject)) {
                throw new Exception('Pipeline record not found', 404);
            }

            $branch = $deploymentRecord['branch'];
            $nestedFolderName = $deploymentProject['files']['nestedFolderName'] ?? '';

            $this->deploymentService->updateDeploymentRecordStatus((int)$deploymentId, 'STARTED');

            $this->deploymentService->logSocketMessages($logsPath . '/1-start.json', [
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'orange',
                    'content' => 'Deployment ' . $deploymentId . ' started!',
                    'icon' => 'rocket'
                ],
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'transparent',
                    'content' => 'Pulling files...',
                    'icon' => 'download'
                ]
            ]);

            $filesDownloaded = $this->deploymentService->downloadRepo($downloadPath, $deploymentRecord['project_code'], $branch);

            if (false !== $filesDownloaded) {
                $this->deploymentService->logSocketMessages($logsPath . '/2-pull.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'yellow',
                        'content' => "Files pulled successfully ({$filesDownloaded})",
                        'icon' => 'check',
                    ]
                ]);
            } else {
                $this->deploymentService->logSocketMessages($logsPath . '/3-pull.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'red',
                        'content' => 'Error pulling files!',
                        'icon' => 'times'
                    ]
                ]);

                throw new Exception('Error pulling files!', 500);
            }

            $this->deploymentService->logSocketMessages($logsPath . '/4-extract.json', [
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'transparent',
                    'content' => 'Extracting files...',
                    'icon' => 'file-zipper'
                ]
            ]);

            $filesExtracted = $this->deploymentService->extractFiles($basePath, $downloadPath, $branch, $deploymentId);

            if (false !== $filesExtracted) {
                $this->deploymentService->logSocketMessages($logsPath . '/5-extract.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'yellow',
                        'content' => "Files extracted successfully ({$filesExtracted})",
                        'icon' => 'check'
                    ]
                ]);
            } else {
                $this->deploymentService->logSocketMessages($logsPath . '/6-extract.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'red',
                        'content' => 'Error extracting files!',
                        'icon' => 'times'
                    ]
                ]);

                throw new Exception('Error extracting files!', 500);
            }

            $this->deploymentService->logSocketMessages($logsPath . '/7-upload.json', [
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'transparent',
                    'content' => 'Uploading files...',
                    'icon' => 'upload',
                    'longOperation' => true
                ]
            ]);

            $uploadFiles = $this->deploymentService->uploadFiles(
                "{$basePath}/release-files/deployment-{$deploymentId}" . (!empty($nestedFolderName) ? "/{$nestedFolderName}" : ''),
                $deploymentProject,
                [
                    'export_log' => true,
                    'logs_file_path' => "{$basePath}/release-files/deployment-{$deploymentId}-logs/statusLog.log"
                ]
            );

            if (false !== $uploadFiles) {
                $this->deploymentService->logSocketMessages($logsPath . '/8-upload.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'yellow',
                        'content' => "Files uploaded successfully ({$uploadFiles})",
                        'icon' => 'check'
                    ]
                ]);
            } else {
                $this->deploymentService->logSocketMessages($logsPath . '/9-upload.json', [
                    [
                        'deploymentId' => $deploymentId,
                        'textColor' => 'red',
                        'content' => 'Error uploading files!',
                        'icon' => 'times'
                    ]
                ]);

                throw new Exception('Error uploading files!', 500);
            }

            sleep(1);

            $endTime = new DateTime();

            $timeElapsed = $this->deploymentService->formatSeconds($endTime->getTimestamp() - $startTime->getTimestamp() - 1);

            $this->deploymentService->updateDeploymentRecordStatus((int)$deploymentId, 'SUCCEEDED');

            $this->deploymentService->logSocketMessages($logsPath . '/10-done.json', [
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'green',
                    'content' => "Deployment completed successfully ({$timeElapsed})",
                    'icon' => 'check'
                ]
            ], 'SUCCEEDED');

            $this->deploymentService->updateDeploymentRecordCompletedLog((int)$deploymentId, $this->deploymentService->getProjectDeploymentLog($deploymentId));

            $this->line(sprintf("<%s>%s</>", 'fg=black;bg=yellow', 'Started deployment id: ' . $deploymentId));
        } catch (DeploymentInProgressException $e) {
            $this->error(' <error> ' . $e->getMessage() . ' </error>');
        } catch (Exception $e) {
            $this->deploymentService->updateDeploymentRecordStatus((int)$deploymentId, 'FAILED');
            $this->deploymentService->updateDeploymentRecordCompletedLog(
                (int)$deploymentId,
                $this->deploymentService->getProjectDeploymentLog($deploymentId) . PHP_EOL .
                    'Error: ' . $e->getMessage()
            );

            $this->deploymentService->logSocketMessages($logsPath . '/error.json', [
                [
                    'deploymentId' => $deploymentId,
                    'textColor' => 'red',
                    'content' => 'Error: ' . $e->getMessage(),
                    'icon' => 'times'
                ]
            ], 'FAILED');

            $this->sendFailedEmail($deploymentId);
            $this->error(' <error> ' . $e->getMessage() . ' </error>');
        } finally {
            // Clean-up
            // system("rm -rf {$logsPath}");
            system("rm -rf {$basePath}/release-files/deployment-{$deploymentId}");
        }
    }

    private function sendLiveMessages($deploymentId): void
    {
        $this->deploymentService->runBackgroundProcess('app:deployment-updates', [
            $deploymentId,
        ]);
    }

    private function sendFailedEmail($deploymentId): void
    {
        $this->deploymentService->runBackgroundProcess('app:send-deployment-failed-email', [
            $deploymentId,
        ]);
    }
}
