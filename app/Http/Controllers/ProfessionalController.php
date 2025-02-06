<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\WelcomeNotification;
use App\Http\Requests\ProfessionalRequest;

class ProfessionalController extends Controller
{
    public function index()
    {
        $professionals = Professional::with(['user', 'specialty', 'kids'])
            ->whereHas('user', function($q) {
                $q->whereHas('roles', function($q) {
                    $q->where('name', 'professional');
                });
            })
            ->get();

        return view('professionals.index', compact('professionals'));
    }

    public function show(Professional $professional)
    {
        $professional->load(['user', 'specialty', 'kids']);
        return view('professionals.show', compact('professional'));
    }

    public function edit(Professional $professional)
    {
        try {
            $professional->load(['user', 'specialty']);
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
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Criar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => bcrypt(Str::random(10)), // Senha temporária
                'allow' => $request->has('allow'),
                'created_by' => auth()->id()
            ]);

            // Atribuir role de profissional
            $user->assignRole('professional');

            // Criar o profissional
            $professional = Professional::create([
                'specialty_id' => $validated['specialty_id'],
                'registration_number' => $validated['registration_number'],
                'bio' => $validated['bio'],
                'created_by' => auth()->id()
            ]);

            // Vincular usuário ao profissional
            $professional->user()->attach($user->id);

            // Enviar email com credenciais
            $password = Str::random(10);
            $user->update(['password' => bcrypt($password)]);
            $user->notify(new WelcomeNotification($user, $password));

            DB::commit();
            flash('Profissional criado com sucesso.')->success();
            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar profissional: ' . $e->getMessage());
            flash('Erro ao criar profissional.')->error();
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request, Professional $professional)
    {
        try {
            DB::beginTransaction();

            // Validar os dados
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'specialty_id' => 'required|exists:specialties,id',
                'registration_number' => 'required|string|max:50',
                'bio' => 'nullable|string',
                'allow' => 'boolean'
            ]);

            // Atualizar o usuário associado
            $user = $professional->user->first();
            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'allow' => $request->has('allow'),
                'updated_by' => auth()->id()
            ]);

            // Atualizar o profissional
            $professional->update([
                'specialty_id' => $validated['specialty_id'],
                'registration_number' => $validated['registration_number'],
                'bio' => $validated['bio'],
                'updated_by' => auth()->id()
            ]);

            DB::commit();
            flash('Profissional atualizado com sucesso.')->success();
            return redirect()->route('professionals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar profissional: ' . $e->getMessage());
            flash('Erro ao atualizar profissional.')->error();
            return redirect()->back()->withInput();
        }
    }

    public function deactivate(Professional $professional)
    {
        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            $user->update([
                'allow' => false,
                'updated_by' => auth()->id()
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
        try {
            DB::beginTransaction();

            $user = $professional->user->first();
            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            $user->update([
                'allow' => true,
                'updated_by' => auth()->id()
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
