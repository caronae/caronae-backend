<?php
namespace Bootstrap;
use Monolog\Logger as Monolog;
use Monolog\Handler\GelfHandler;
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use Monolog\Handler\StreamHandler;

class ConfigureLogging extends BaseConfigureLogging {

    /**
     * OVERRIDE PARENT
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureHandlers(Application $app, Writer $log)
    {
        parent::configureHandlers($app, $log);

        if (!empty(env('GRAYLOG_HOST'))) {
            $transport = new \Gelf\Transport\UdpTransport(env('GRAYLOG_HOST'), env('GRAYLOG_PORT', 12201), \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
            $publisher = new \Gelf\Publisher();
            $publisher->addTransport($transport);

            $monolog = $log->getMonolog();
            $monolog->pushHandler(new GelfHandler($publisher));
        }
    }
}