<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Resource;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function index()
    {
//        if (auth()->user()->isSuperAdmin()) {
//            $roles = Role::paginate($this->paginate);
//        } else {
//            $roles = Role::where('created_by', '=', auth()->user()->id)->paginate($this->paginate);
//        }

        $message = label_case('Index Role ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('roles.index');
    }

    public function index_data()
    {
        if (auth()->user()->isSuperAdmin()) {
            $data = Role::select('id', 'name');
        } else {
            $data = Role::select('id', 'name')
                ->where('created_by', '=', auth()->user()->id);
        }

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                if (request()->user()->can('roles.show')) {
                    $html = '<a class="btn btn-sm btn-success" href="'.route('roles.show', $data->id).'"><i class="bi bi-gear"></i> Gerenciar</a>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->rawColumns(['name', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Role ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
        Log::info($message);

        return view('roles.create');
    }

    public function store(RoleRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['role'] = $this->defineRole($data['name']);
            $data['created_by'] = auth()->user()->id;

            $this->role->create($data);

            DB::commit();
            flash(self::MSG_CREATE_SUCCESS)->success();

            $message = label_case('Store Role '.self::MSG_CREATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('roles.index');
        } catch (Exception $e) {
            DB::rollBack();

            flash(self::MSG_CREATE_ERROR)->error();

            $message = label_case('Store Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function show($id)
    {
        try {
            $role = $this->role->findOrFail($id);

            $message = label_case('Show Role ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('roles.show', compact('role'));
        } catch (\Exception $e) {
            flash(self::MSG_NOT_FOUND)->error();

            $message = label_case('Show Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->route('roles.index');
        }
    }

    public function edit($id)
    {
        try {
            $role = $this->role->findOrFail($id);
            $message = label_case('Edit Role '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('roles.edit', compact('role'));
        } catch (\Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->error();

            $message = label_case('Edit Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->route('roles.index');
        }
    }

    public function update(RoleRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);

            $data = $request->all();
            $data['updated_by'] = auth()->user()->id;
            $data['role'] = $this->defineRole($data['name']);

            $role->update($data);

            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();

            $message = label_case('Update Role '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('roles.show', $id);
        } catch (\Exception $e) {
            DB::rollBack();

            flash(self::MSG_UPDATE_ERROR)->error();

            $message = label_case('Update Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->back();
        }
    }

    public function destroy(Role $role)
    {
        DB::beginTransaction();

        try {
            if (auth()->user()->role_id == $role->id) {
                throw new Exception(self::MSG_DELETE_ROLE_SELF);
            }
            $role->deleted_by = auth()->user()->id;
            $role->update();
            $role->delete();

            DB::commit();
            flash(self::MSG_DELETE_SUCCESS)->success();

            $message = label_case('Destroy Role '.self::MSG_DELETE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('roles.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_DELETE_ERROR);

            $message = label_case('Destroy Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function syncResources(int $role)
    {
        DB::beginTransaction();
        try {
            $role = $this->role->findOrFail($role);
            $resources = Resource::orderBy('name')->get();
            DB::commit();

            $message = label_case('Resources Role ').' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::info($message);

            return view('roles.sync-resources', compact('role', 'resources'));
        } catch (Exception $e) {
            DB::rollBack();

            flash(self::MSG_UPDATE_ERROR)->error();

            $message = label_case('Resources Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->route('roles.index', $role);
        }
    }

    public function updateSyncResources($role, Request $request)
    {
        DB::beginTransaction();
        try {
            $role = $this->role->findOrFail($role);
            $role->updated_by = auth()->user()->id;
            $role->update();
            $role->resources()->sync($request->abilities);

            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();

            $message = label_case('Update Resources Role '.self::MSG_UPDATE_SUCCESS).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::notice($message);

            return redirect()->route('roles.show', $role);
        } catch (Exception $e) {
            DB::rollBack();

            flash(self::MSG_UPDATE_ERROR)->error();

            $message = label_case('Update Resources Role '.$e->getMessage()).' | User:'.auth()->user()->name.'(ID:'.auth()->user()->id.')';
            Log::error($message);

            return redirect()->route('roles.index', $role);
        }
    }
}
