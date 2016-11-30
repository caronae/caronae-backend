<?php

namespace Caronae\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Symfony\Component\Console\Input\InputOption;

class DBStartCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the application database based on config';

    /**
     * Create a new command instance.
     *
     * @return void
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

            $result = $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $config['database'] .
                ' CHARACTER SET ' . $config['charset'] . ' COLLATE ' . $config['collation'] . ';');

            if ($result === false) {
                $errorInfo = $pdo->errorInfo();
                $this->error($errorInfo[0] . ' ' . $errorInfo[1] . ' ' . $errorInfo[2]);
                return;
            }

            $this->info("Database " . $config['database'] . " created!");
            if ($this->option('no-migrate') == false) {
                $this->call('migrate');
            }
            if ($this->option('no-seed') == false) {
                $this->call('db:seed');
            }
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
        return [
            ['no-migrate', 'nm', InputOption::VALUE_NONE, 'Don\'t call migrate.'],
            ['no-seed', 'ns', InputOption::VALUE_NONE, 'Don\'t call seed.'],
        ];
    }

}