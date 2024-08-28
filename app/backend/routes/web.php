<?php

use App\Events\PipelineMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/broadcast', function () {
    broadcast(new PipelineMessage([
        'deploymentId' => 2,
        'textColor' => 'green',
        'icon' => 'check',
        'content' => 'Test success message sent successfully!'
    ]));
});
