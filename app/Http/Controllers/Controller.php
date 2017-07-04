<?php

namespace Caronae\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Log;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function error(string $message, int $statusCode)
    {
        Log::warning(get_class($this) . ': ' . $message);
        return response()->json(['error' => $message], $statusCode);
    }
}
