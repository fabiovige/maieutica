<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        //$this->middleware('acl');
    }

    public function index()
    {
        $user = auth()->user();
        $permissionNames = $user->getPermissionNames();
        $permissions = $user->permissions;

        $roles = $user->getRoleNames()->first();
        var_dump($roles);
        
        try {
            $kids = Kid::getKids();
            $data = [];
            foreach ($kids as $key => $kid) {
                $kids[$key]['months'] = $kid->months;
                $data['countChecklists'][$kid->id] = $kid->checklists()->count();
                $data['countPlanes'][$kid->id] = $kid->planes()->count();
            }
            $data['kids'] = $kids;
            return view('home', $data);
        } catch (\Exception $e) {
            Log::error("message: {$e->getMessage()} file: {$e->getFile()} line: {$e->getLine()}");
            dd($e->getMessage());
        }
    }
}
