<?php

namespace App\Services\Log;

use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return Logger
     *
     * @throws Exception
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('local');
        $logger->pushHandler(
            new DatabaseHandler(new $config['with']['logModel'])
        );

        return $logger;
    }
}
