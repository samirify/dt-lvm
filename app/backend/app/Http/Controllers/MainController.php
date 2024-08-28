<?php

namespace App\Http\Controllers;

use App\Service\Help\HelpService;
use App\Traits\SAAApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MainController extends Controller
{
    use SAAApiResponse;

    public function __construct(
        private readonly HelpService $helpService,
    ) {}

    public function initialise(): Response|JsonResponse
    {
        $repoData = $this->helpService->getHelp();

        return $this->successResponse($repoData);
    }
}
