<?php

namespace Caronae\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class DBDropCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:drop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop the application database based on config';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $driver = config('database.default');

        if ($driver == "mysql") {

            $config = config('database.connections');
            $config = $config['mysql'];

            $pdo = new PDO("mysql:host=" . $config['host'] . ";", $config['username'], $config['password']);

            $result = $pdo->exec('DROP DATABASE ' . $config['database']);

            if ($result === false) {
                $errorInfo = $pdo->errorInfo();
                $this->error($errorInfo[0] . ' ' . $errorInfo[1] . ' ' . $errorInfo[2]);
                return;
            }

            $this->info("Database " . $config['database'] . " droped!");
        } else {
            $this->error("This command just support mysql!");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}