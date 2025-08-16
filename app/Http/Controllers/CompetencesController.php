<?php

namespace App\Http\Controllers;

use App\Models\Competence;
use App\Models\Domain;
use App\Models\Level;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Log;

class CompetencesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Competence::class);

        // Verificar se os filtros foram enviados na requisição e armazenar na sessão
        if ($request->has('level_id') || $request->has('domain_id')) {
            // Se os filtros forem passados, salvá-los na sessão
            session([
                'level_id' => $request->level_id,
                'domain_id' => $request->domain_id,
            ]);
        }

        // Recuperar os filtros da sessão, se existirem
        $level_id = session('level_id', null);
        $domain_id = session('domain_id', null);

        // Inicializa a query básica para Competence
        $query = Competence::query();

        // Aplica o filtro de level_id se estiver presente na sessão
        if (!empty($level_id)) {
            $query->where('level_id', $level_id);
        }

        // Aplica o filtro de domain_id se estiver presente na sessão
        if (!empty($domain_id)) {
            $query->where('domain_id', $domain_id);
        }

        // Executa a query e retorna os resultados
        $competences = $query->get();

        // Filtra os domains disponíveis com base no level_id, se presente
        $domains = Domain::query();

        if (!empty($level_id)) {
            // Usa a tabela intermediária domain_level para filtrar os domínios pelo level
            $domains->whereHas('levels', function ($query) use ($level_id) {
                $query->where('level_id', $level_id);
            });
        }

        // Carrega os domains com ou sem o filtro aplicado
        $domains = $domains->get();

        // Carrega todos os níveis para os filtros
        $levels = Level::all();

        // Retorna a view com os dados filtrados
        return view('competences.index', compact('competences', 'levels', 'domains'));
    }

    public function clearFilters()
    {
        // Limpa os filtros da sessão
        session()->forget(['level_id', 'domain_id']);

        // Redireciona para a rota de competências
        return redirect()->route('competences.index');
    }

    public function getDomainsByLevel($level_id)
    {
        // Verifica se o level_id é válido
        $levelExists = Level::where('id', $level_id)->exists();
        if (!$levelExists) {
            return response()->json(['error' => 'Level not found'], 404);
        }

        // Busca os domínios relacionados ao nível através da tabela intermediária
        $domains = Domain::join('domain_level', 'domains.id', '=', 'domain_level.domain_id')
            ->where('domain_level.level_id', $level_id)
            ->select('domains.id', 'domains.name')
            ->distinct()
            ->get();

        return response()->json($domains);
    }

    public function update(Request $request, $id)
    {
        $competence = Competence::findOrFail($id);
        $this->authorize('update', $competence);

        // Validação dos dados recebidos
        $request->validate([
            'percentil_25' => 'required|numeric|min:1|max:72',
            'percentil_50' => 'required|numeric|min:1|max:72',
            'percentil_75' => 'required|numeric|min:1|max:72',
            'percentil_90' => 'required|numeric|min:1|max:72',
        ]);

        try {
            // Busca da competência pelo ID

            // Dados a serem atualizados
            $data = [
                'percentil_25' => $request->percentil_25,
                'percentil_50' => $request->percentil_50,
                'percentil_75' => $request->percentil_75,
                'percentil_90' => $request->percentil_90,
            ];

            // Atualiza a competência com os dados validados
            $competence->update($data);

            // Preserva os filtros de level_id e domain_id da requisição
            $filters = [];
            if ($request->has('level_id')) {
                $filters['level_id'] = $request->level_id;
            }
            if ($request->has('domain_id')) {
                $filters['domain_id'] = $request->domain_id;
            }

            $message = label_case('Update Competence - ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . ' (ID:' . auth()->user()->id . ') ';
            Log::info($message, $data);

            // Redireciona com mensagem de sucesso, preservando os filtros na URL
            flash(self::MSG_UPDATE_SUCCESS)->success();

            return redirect()->route('competences.index', $filters);
        } catch (ModelNotFoundException $e) {
            // Loga erro caso a competência não seja encontrada
            Log::error('Competence not found', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Competence not found');
        } catch (\Exception $e) {
            // Loga erro genérico
            Log::error('Error updating competence', ['message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Error updating competence: ' . $e->getMessage());
        }
    }

    public function show(Competence $competence)
    {
        //
    }

    public function edit(Competence $competence)
    {
        //
    }

    public function destroy(Competence $competence)
    {
        //
    }
}
