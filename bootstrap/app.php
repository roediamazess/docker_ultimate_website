<?php

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Http\Kernel as AppHttpKernel;
use App\Console\Kernel as AppConsoleKernel;
use App\Exceptions\Handler as AppExceptionHandler;

$app = new Application(dirname(__DIR__));

$app->singleton(HttpKernel::class, AppHttpKernel::class);
$app->singleton(ConsoleKernel::class, AppConsoleKernel::class);
$app->singleton(ExceptionHandler::class, AppExceptionHandler::class);

return $app;
