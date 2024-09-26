<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public $paginate = 15;

    public const MSG_UPDATE_SUCCESS = 'Registro atualizado com sucesso.';
    public const MSG_CREATE_SUCCESS = 'Registro criado com sucesso.';
    public const MSG_DELETE_SUCCESS = 'Registro removido com sucesso.';
    public const MSG_UPDATE_ERROR = 'Não foi possível atualizar o registro!';
    public const MSG_CREATE_ERROR = 'Não foi possível cadastrar o registro!';
    public const MSG_DELETE_ERROR = 'Não foi possível remover o registro!';
    public const MSG_NOT_FOUND = 'Registro não encontrado!';
    public const MSG_DELETE_ROLE_SELF = 'Não é permitido remover seu próprio papél!';
    public const MSG_DELETE_USER_SELF = 'Não é permitido remover seu próprio usuário!';
    public const MSG_DELETE_USER_WITH_ROLE = 'Não é permitido remover um usuário com papél!';
    public const MSG_NOT_FOUND_CHECKLIST_USER = 'Nenhum checklist encontrado!';
    public const MSG_ALREADY_EXISTS = 'O registro %s já existe na base de dados';
    public const ID_SUPER_ADMIN = 1;
    public const ID_ADMIN = 2;

    public function defineRole($value): string
    {
        return 'ROLE_'.Str::upper(Str::slug($value, '_'));
    }
}
