<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    const MSG_UPDATE_SUCCESS = 'Perfil atualizado com sucesso!';
    const MSG_UPDATE_ERROR = 'Erro ao atualizar perfil.';
    const MSG_PASSWORD_SUCCESS = 'Senha alterada com sucesso!';
    const MSG_PASSWORD_ERROR = 'Erro ao alterar senha.';

    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'postal_code' => $request->cep,
                'street' => $request->logradouro,
                'number' => $request->numero,
                'complement' => $request->complemento,
                'neighborhood' => $request->bairro,
                'city' => $request->cidade,
                'state' => $request->estado,
            ];

            $user->update($userData);

            // Se a senha foi fornecida, atualiza
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            DB::commit();
            flash(self::MSG_UPDATE_SUCCESS)->success();
            Log::info('Perfil atualizado com sucesso', ['user_id' => $user->id]);

            return redirect()->route('profile.edit');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            flash($e->getMessage() ?: self::MSG_UPDATE_ERROR)->error();
            return redirect()->back()->withInput();
        }
    }
}
