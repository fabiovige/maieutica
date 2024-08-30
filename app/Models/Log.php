<?php

namespace App\Models;

class Log extends BaseModel
{
    public const ACTION_INSERT = 'insert';

    public const ACTION_UPDATE = 'update';

    public const ACTION_REMOVE = 'remove';

    public const ACTION_INFO = 'info';

    public const ACTION_LIST = [
        self::ACTION_INSERT,
        self::ACTION_UPDATE,
        self::ACTION_REMOVE,
        self::ACTION_INFO,
    ];

    protected $guarded = [];

    public $log = false;
}
