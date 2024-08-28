<?php

namespace App\Console\Commands;

use App\Mail\PipelineStatusEmail;
use App\Service\DeploymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DeploymentFailedEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-deployment-failed-email {deployment-id} {--email-to} {--subject}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to user when deployment fails.';

    private array $emailConfig;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly DeploymentService $deploymentService
    ) {
        $this->emailConfig = [
            'to' => env('APP_EMAIL_ERROR_TO_ADDRESS', '')
        ];

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deploymentId = $this->argument('deployment-id');

        $emailTo = $this->option('email-to') ?? '';
        $subject = $this->option('subject') ?? 'Samirify Deployment id: ' . $deploymentId;

        $recipients = $this->emailConfig['to'] ? explode(',', $this->emailConfig['to']) : [];

        if ($recipients) {
            if ($emailTo) {
                $recipients = array_merge($recipients, explode(';', $emailTo));
            }

            $logContent = $this->deploymentService->getProjectDeploymentLogContent($deploymentId);

            foreach ($recipients as $recipient) {
                Mail::to($recipient)
                    ->send(new PipelineStatusEmail([
                        'subject' => $subject,
                        'messageContent' => "<pre><strong>Deployment {$deploymentId} has failed!</strong></pre>{$logContent['html']}",
                    ]));
            }

            $this->line('An email was sent to: ' . PHP_EOL . implode(PHP_EOL, $recipients));
        } else {
            $this->warn('WARNING: No recipients configured. Email was not sent!' . PHP_EOL);
            $this->error('If you need to see the full update log lease contact the release team.');
        }
    }
}
