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
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($q) {
                    $q->where('name', 'professional');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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

            // Atribuir role de profissional (se existir)
            if (\Spatie\Permission\Models\Role::where('name', 'professional')->exists()) {
                $user->assignRole('professional');
            } else {
                Log::warning('Role "professional" não existe. Profissional criado sem role específica.', [
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

    public function deactivate(Professional $professional)
    {
        $this->authorize('update', $professional);

        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (! $user) {
                throw new \Exception('Usuário não encontrado');
            }

            $user->update([
                'allow' => false,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            flash('Profissional desativado com sucesso.')->success();

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

            $user->update([
                'allow' => true,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();
            flash('Profissional ativado com sucesso.')->success();

            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ativar profissional: ' . $e->getMessage());
            flash('Erro ao ativar profissional.')->error();

            return redirect()->back();
        }
    }
}
