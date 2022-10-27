<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends BaseModel
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_REMOVE = 'remove';
    const ACTION_INFO = 'info';

    const ACTION_LIST = [
        self::ACTION_INSERT,
        self::ACTION_UPDATE,
        self::ACTION_REMOVE,
        self::ACTION_INFO
    ];

    protected $guarded = [];

    public $log = false;
}
