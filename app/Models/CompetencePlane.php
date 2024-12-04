<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompetencePlane extends Model
{
    use HasFactory;

    protected $table = 'competence_plane';



    protected $fillable = ['plane_id', 'competence_id'];

    public static function deleteCompetencePlane($request)
    {
        return DB::table('competence_plane')->where('plane_id', $request->plane_id)
            ->where('competence_id', $request->competence_id)
            ->delete();
    }
}
