<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            Log::error('Erro ao atualizar perfil: '.$e->getMessage());
            flash($e->getMessage() ?: self::MSG_UPDATE_ERROR)->error();

            return redirect()->back()->withInput();
        }
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:1024'], // max 1MB
        ]);

        $user = auth()->user();

        try {
            if ($request->hasFile('avatar')) {
                // Remove avatar antigo se existir
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }

                // Cria o diretório se não existir
                $path = public_path('images/avatars');
                if (! file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                // Salva novo avatar
                $file = $request->file('avatar');
                $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $file->move($path, $fileName);

                // Salva o caminho relativo no banco
                $user->avatar = 'images/avatars/'.$fileName;
                $user->save();

                flash('Foto de perfil atualizada com sucesso!')->success();
                Log::info('Avatar atualizado com sucesso', [
                    'user_id' => $user->id,
                    'path' => $user->avatar,
                ]);
            }

            return redirect()->route('profile.edit');
        } catch (Exception $e) {
            Log::error('Erro ao atualizar avatar: '.$e->getMessage());
            flash('Erro ao atualizar foto de perfil.')->error();

            return redirect()->back();
        }
    }
}
