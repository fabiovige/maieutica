<?php

namespace app\Services\Log;

use Monolog\Logger;
use app\Services\Log\DatabaseHandler;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array $config
     * @return Logger
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
