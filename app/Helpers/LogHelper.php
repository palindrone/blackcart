<?php


namespace App\Helpers;


use App\Models\Province;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LogHelper
{

    public static function logException(\Throwable $exception) {
        if ($exception instanceof NotFoundHttpException) {
	        // This is mostly garbage
	        return;
        }

		$errorID = date("md")."-".rand(1000,10000);

		// Log basic error info
		Log::channel("errorInfo")->info(get_class($exception) . " {$errorID}: " . $exception->getMessage() . " in " . $exception->getFile().":".$exception->getLine());

		// Log Stack to stack log
		Log::channel("errorStack")->info("Reporting Error {$errorID}");
		$errorString = $exception->getTraceAsString();
		Log::channel("errorStack")->debug($errorString);
	}
}
