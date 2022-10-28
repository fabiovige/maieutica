<?php

namespace app\Services\Log;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Monolog\Handler\AbstractProcessingHandler;

class DatabaseHandler extends AbstractProcessingHandler
{
    private $log = null;
    private $isDelete = null;

    public function __construct($log)
    {
        $this->log = $log;
    }

    protected function write(array $record): void
    {
        dd($record);
        if (!empty($record['message']) && $record['level_name'] != 'ERROR') {
            $this->createLog(
                null,
                null,
                $this->log::ACTION_INFO,
                $record['message']
            );

            return;
        }

        $model = Arr::get($record['context'], 0);

        if (!$model instanceof Model) {
            return;
        }

        if ($model->log == false) {
            return;
        }

        $this->isDelete = Arr::get($record['context'], 1);

        $action = $this->getAction($model);
        $description = $this->getDescription($action, $model);

        $this->createLog(get_class($model), $model->getKey(), $action, $description);
    }

    private function createLog($object, $objectId, $action, $description)
    {
        dd('create log');
        $this->log::create(
            [
                'object' => $object,
                'object_id' => $objectId,
                'action' => $action,
                'description' => $description
            ]
        );
    }

    private function getDescription($action, $model)
    {
        if ($action == $this->log::ACTION_INSERT) {
            return $model->toJson();
        }

        if ($action == $this->log::ACTION_UPDATE) {
            return json_encode(
                [
                    'original' => $model->getOriginal(),
                    'updated' => $model->toArray()
                ]
            );
        }

        return '';
    }

    private function getAction($model)
    {
        if ($this->isDelete) {
            return $this->log::ACTION_REMOVE;
        }

        if (!$model->getOriginal()) {
            return $this->log::ACTION_INSERT;
        }

        return $this->log::ACTION_UPDATE;
    }
}
