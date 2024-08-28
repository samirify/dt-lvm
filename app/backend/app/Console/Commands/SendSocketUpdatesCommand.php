<?php

namespace App\Console\Commands;

use App\Events\PipelineMessage;
use App\Exceptions\Deployment\DeploymentInProgressException;
use App\Service\DeploymentService;
use DirectoryIterator;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class SendSocketUpdatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deployment-updates {deployment-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deployment updates. This command will start the socket server and send live updates...';

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
        try {
            $deploymentId = $this->argument('deployment-id');
            $basePath = base_path();

            $key = new Key('deployment-messages-' . $deploymentId);
            $store = new FlockStore();
            $factory = new LockFactory($store);

            $lock = $factory->createLockFromKey($key);

            $deploymentRecord = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

            if (!$lock->acquire() && ($deploymentRecord && in_array($deploymentRecord['status_code'], ['STARTED']))) {
                throw new DeploymentInProgressException();
            }

            $done = false;
            $stepsFiles = ['1-start', '2-pull', '3-pull', '4-extract', '5-extract', '6-extract', '7-upload', '8-upload', '9-upload', '10-done', 'error'];

            $deploymentRecord = $this->deploymentService->getDeploymentRecordById((int)$deploymentId);

            $existingMessages = json_decode($deploymentRecord['log_messages'] ?? '', true) ?: [];

            $logsFolder = "{$basePath}/release-files/deployment-{$deploymentId}-logs";

            if (count($existingMessages) > 0 && (!is_dir($logsFolder) || count(scandir($logsFolder)) === 2)) {
                system("rm -rf {$logsFolder}");

                foreach ($existingMessages as $msg) {
                    broadcast(new PipelineMessage($msg));
                }

                mkdir($logsFolder, 0777, true);
            } else {
                do {
                    $logsDir = new DirectoryIterator($logsFolder);

                    foreach ($logsDir as $fileinfo) {
                        if ('json' === strtolower($fileinfo->getExtension())) {
                            foreach ($stepsFiles as $k => $stepFile) {
                                $thisStepFile = "{$logsFolder}/{$stepFile}.json";
                                if (file_exists($thisStepFile) && in_array($stepFile, $stepsFiles)) {
                                    unset($stepsFiles[$k]);

                                    $messages = json_decode(file_get_contents($thisStepFile), true);

                                    foreach ($messages as $message) {
                                        broadcast(new PipelineMessage($message));
                                    }
                                }
                            }
                        }
                    }

                    $doneFile = "{$logsFolder}/10-done.json";
                    $done = file_exists($doneFile);

                    sleep(1);
                } while (!$done);
            }

            $this->line(sprintf("<%s>%s</>", 'fg=black;bg=yellow', 'Started deployment id: ' . $deploymentId));
        } catch (DeploymentInProgressException $e) {
            $this->error(' <error> ' . $e->getMessage() . ' </error>');
        } catch (Exception $e) {
            broadcast(new PipelineMessage([
                'deploymentId' => $deploymentId,
                'textColor' => 'red',
                'content' => "An error occurred. Please check the logs!" . $e->getMessage(),
                'icon' => 'times',
                'deplymentStatus' => 'FAILED'
            ]));
            $this->error(' <error> ' . $e->getMessage() . ' </error>');
        }
    }
}
