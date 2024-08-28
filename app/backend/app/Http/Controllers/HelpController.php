<?php

namespace App\Http\Controllers;

use App\Service\Help\HelpService;
use App\Traits\SAAApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class HelpController extends Controller
{
    use SAAApiResponse;

    public function __construct(
        private readonly HelpService $helpService,
    ) {}

    public function getTopic(string $code): Response|JsonResponse
    {
        return $this->successResponse($this->helpService->getTopic($code));
    }
}
