<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsible extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'cell',
        'created_by',
        'updated_by',
        'deleted_by',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
    ];

    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCellAttribute($value)
    {
        return '('.substr($value, 0, 2).') '.substr($value, 2, 5).'-'.substr($value, 7, 4);
    }

    public function setCellAttribute($value)
    {
        $value = str_replace('(', '', $value);
        $value = str_replace(')', '', $value);
        $value = str_replace('-', '', $value);
        $value = str_replace(' ', '', $value);

        $this->attributes['cell'] = $value;
    }
}
