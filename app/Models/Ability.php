<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'resource_id', 'ability', 'created_by', 'updated_by', 'deleted_by'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public static function assocAbilities($role, $resources) 
    {
        $abilitiesRole = $role->abilities()->pluck('id')->toArray();
        $abilities = [];
        foreach($resources as $resource) {
            foreach ($resource->abilities as $ability) {
                if (in_array($ability->id, $abilitiesRole)) {
                    $abilities[$resource->name][] = $ability->name;
                }
            }
        }
        return $abilities;
    }
}
