<?php

namespace Caronae\Providers;

use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\GelfHandler;

class GraylogLogProvider extends ServiceProvider
{
    public function boot(Writer $log)
    {
        if (!empty(env('GRAYLOG_HOST'))) {
            $transport = new \Gelf\Transport\UdpTransport(env('GRAYLOG_HOST'), env('GRAYLOG_PORT', 12201), \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
            $publisher = new \Gelf\Publisher();
            $publisher->addTransport($transport);

            $monolog = $log->getMonolog();
            $monolog->pushHandler(new GelfHandler($publisher));
        }

        \Log::info('Registered Graylog');
    }
}
