<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfessionalRequest;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfessionalController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Professional::class);

        $professionals = Professional::with(['user', 'specialty', 'kids'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('professionals.index', compact('professionals'));
    }

    public function show(Professional $professional)
    {
        $this->authorize('view', $professional);

        $professional->load(['user', 'specialty', 'kids']);

        return view('professionals.show', compact('professional'));
    }

    public function edit(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            $specialties = Specialty::orderBy('name')->get();

            return view('professionals.edit', compact('professional', 'specialties'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar profissional: ' . $e->getMessage());
            flash('Erro ao carregar dados do profissional.')->error();

            return redirect()->route('professionals.index');
        }
    }

    public function create()
    {
        $this->authorize('create', Professional::class);

        try {
            $specialties = Specialty::orderBy('name')->get();

            return view('professionals.create', compact('specialties'));
        } catch (\Exception $e) {
            Log::error('Erro ao criar profissional: ' . $e->getMessage());
            flash('Erro ao carregar formulário de criação.')->error();

            return redirect()->route('professionals.index');
        }
    }

    public function store(ProfessionalRequest $request)
    {
        $this->authorize('create', Professional::class);

        DB::beginTransaction();
        try {
            $validated = $request->validated();

            // Gerar senha temporária
            $temporaryPassword = Str::random(10);

            // Criar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt($temporaryPassword),
                'allow' => $request->has('allow'),
                'created_by' => auth()->id(),
            ]);

            // Atribuir role 'profissional' (para agrupar permissions automaticamente)
            // IMPORTANTE: O código usa APENAS can() para verificações, nunca hasRole()
            if (\App\Models\Role::where('name', 'profissional')->exists()) {
                $user->assignRole('profissional');
            } else {
                Log::warning('Role "profissional" não existe. User criado sem role.', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            // Criar o profissional
            $professional = Professional::create([
                'specialty_id' => $validated['specialty_id'],
                'registration_number' => $validated['registration_number'],
                'bio' => $validated['bio'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Vincular usuário ao profissional
            $professional->user()->attach($user->id);

            DB::commit();

            Log::notice('Profissional criado com sucesso.', [
                'professional_id' => $professional->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            flash('Profissional criado com sucesso.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar profissional: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            flash('Erro ao criar profissional: ' . $e->getMessage())->error();

            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $professional = Professional::with('user')->findOrFail($id);
        $this->authorize('update', $professional);

        try {
            $user = $professional->user->first();

            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Validação
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'specialty_id' => 'required',
                'registration_number' => 'required',
            ], [
                'phone.required' => 'O campo telefone é obrigatório.'
            ]);

            DB::beginTransaction();

            // Atualizar dados do usuário
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'allow' => $request->has('allow'),
                'updated_by' => auth()->id()
            ]);

            // Atualizar dados do profissional
            $professional->update([
                'specialty_id' => $request->specialty_id,
                'registration_number' => $request->registration_number,
                'bio' => $request->bio,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            flash('Profissional atualizado com sucesso!')->success();
            return redirect()->route('professionals.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar profissional: ' . $e->getMessage());
            flash('Erro ao atualizar o profissional')->warning();
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Professional $professional)
    {
        $this->authorize('delete', $professional);

        DB::beginTransaction();

        try {
            // Verifica se o profissional tem kids vinculados
            if ($professional->kids()->count() > 0) {
                throw new \Exception('Não é possível mover para lixeira, pois existem crianças vinculadas a este profissional.');
            }

            // Move para lixeira (soft delete)
            $professional->delete();

            DB::commit();
            flash('Profissional movido para a lixeira com sucesso.')->success();

            Log::notice('Professional moved to trash.', [
                'professional_id' => $professional->id,
                'user_id' => $professional->user->first()->id ?? null,
            ]);

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            flash($e->getMessage())->error();

            Log::error('Error while deleting professional: ' . $e->getMessage(), [
                'professional_id' => $professional->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back();
        }
    }

    public function trash()
    {
        $this->authorize('viewAny', Professional::class);

        $professionals = Professional::onlyTrashed()
            ->with(['user', 'specialty'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(5);

        Log::info('View Trash Professionals | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')');

        return view('professionals.trash', compact('professionals'));
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $professional = Professional::onlyTrashed()->findOrFail($id);

            $this->authorize('update', $professional);

            // Restaura o profissional da lixeira
            $professional->restore();

            DB::commit();

            flash('Profissional restaurado com sucesso.')->success();

            Log::notice('Professional restored.', [
                'professional_id' => $professional->id,
                'user_id' => $professional->user->first()->id ?? null,
            ]);

            return redirect()->route('professionals.trash');
        } catch (\Exception $e) {
            DB::rollBack();

            flash('Erro ao restaurar profissional: ' . $e->getMessage())->warning();

            Log::error('Error while restoring professional: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back();
        }
    }

    public function deactivate(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (! $user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Desativa o user vinculado
            $user->update([
                'allow' => false,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            flash('Profissional desativado com sucesso. O usuário vinculado também foi desativado.')->success();

            Log::notice('Professional e User vinculado desativados.', [
                'professional_id' => $professional->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desativar profissional: ' . $e->getMessage());
            flash('Erro ao desativar profissional.')->error();

            return redirect()->back();
        }
    }

    public function activate(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (! $user) {
                throw new \Exception('Usuário não encontrado');
            }

            // Ativa o user vinculado
            $user->update([
                'allow' => true,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            flash('Profissional ativado com sucesso. O usuário vinculado também foi ativado.')->success();

            Log::notice('Professional e User vinculado ativados.', [
                'professional_id' => $professional->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ativar profissional: ' . $e->getMessage());
            flash('Erro ao ativar profissional.')->error();

            return redirect()->back();
        }
    }
}
