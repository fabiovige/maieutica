<?php

namespace App\Models;

use app\Models\Log as LogModel;
use app\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BaseModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $log = true;

    public static function boot()
    {
        parent::boot();
        /*
        static::created(function ($model) {
            \Log::channel('database')->info(null, [
                $model,
            ]);
        });

        static::updated(function ($model) {
            \Log::channel('database')->info(null, [
                $model,
            ]);
        });

        static::deleted(function ($model) {
            Log::channel('database')->info(null, [
                $model,
                LogModel::ACTION_REMOVE,
            ]);
        });

        static::deleting(function ($model) {
            $userId = Auth::id();
            if (isset($model->deleted_by)) {
                $userId = $model->deleted_by;
            }
            if (! \App::runningInConsole()) {
                $userId = Auth::id();
            }
            $model->deleted_by = $userId;
            $model->save();
        });

        static::creating(function ($model) {
            $userId = User::SUPERADMIN;
            if (isset($model->created_by)) {
                $userId = $model->created_by;
            }
            if (! \App::runningInConsole()) {
                $userId = Auth::id();
            }

            $model->created_by = $userId;
        });

        static::updating(function ($model) {
            $userId = User::SUPERADMIN;
            if (isset($model->updated_by)) {
                $userId = $model->updated_by;
            }
            if (! \App::runningInConsole()) {
                $userId = Auth::id();
            }
            $model->updated_by = $userId;
        });
        */
    }
}
