<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Jobs\SendKidUpdateJob;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Domain;
use App\Models\Kid;
use App\Models\Plane;
use App\Models\Responsible;
use App\Models\User;
use App\Util\MyPdf;
use Auth;
use Exception;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Request;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role as SpatieRole;

class KidsController extends Controller
{
    public function index()
    {
        $message = label_case('Index Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::debug($message);
        $kids = Kid::getKids();
        return view('kids.index', compact('kids'));
    }

    public function index_data()
    {
        $data = Kid::getKids();

        return Datatables::of($data)
            ->addColumn('action', function ($data) {

                if (request()->user()->can('edit kids')) {

                    $html = '<div class="dropdown">';
                    $html .= '<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear"></i></button><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                    $html .= '<li><a class="dropdown-item" href="' . route('kids.edit', $data->id) . '"><i class="bi bi-pencil"></i> Editar</a></li>';

                    if ($data->checklists()->count() > 0) {
                        $html .= '<li><a class="dropdown-item" href="' . route('kids.radarChart2', ['kidId' => $data->id, 'levelId' => 1, 'checklist' => null])
                        . '"><i class="bi bi-check2-square"></i> Análise Geral</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                }
            })
            ->editColumn('photo', function ($data) {
                // Verifica se a criança tem uma foto. Se não tiver, gera um avatar aleatório.
                if ($data->photo) {
                    $photoUrl = asset('storage/' . $data->photo);
                } else {
                    $randomAvatarNumber = rand(1, 13); // Gera um número aleatório entre 1 e 13
                    $photoUrl = asset('storage/kids_avatars/avatar' . $randomAvatarNumber . '.png');
                }

                $html = '<img src="' . $photoUrl . '" class="rounded-img" style="width: 50px; height: 50px;">';
                return $html;
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('birth_date', function ($data) {
                return $data->birth_date . ' (' . $data->months . ' meses)';
            })
            ->editColumn('checklists', function ($data) {
                return '<span class="badge bg-success"><i class="bi bi-check"></i> ' . $data->checklists->count() . ' Checklist(s) </span>';
            })
            ->editColumn('profession_id', function ($data) {
                // Exibe o nome do professional ou 'Não atribuído' caso não tenha um professional
                return $data->professional ? $data->professional->name : 'Não atribuído';
            })
            ->editColumn('responsible_id', function ($data) {
                // Exibe o nome do responsável ou 'Não atribuído' caso não tenha um responsável
                return $data->responsible ? $data->responsible->name : 'Não atribuído';
            })
            ->rawColumns(['photo', 'name', 'checklists', 'responsible', 'action'])
            //->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', Kid::class);

        $message = label_case('Create Kids') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        return view('kids.create');
    }

    public function store(KidRequest $request)
    {
        $this->authorize('create', Kid::class);

        DB::beginTransaction();
        try {
            $message = label_case('Store Kids ' . self::MSG_CREATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);


            $kidData = [
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'created_by' => auth()->user()->id,
            ];

            if (Auth::user()->hasRole('professional')) {
                $kidData['profession_id'] = Auth::user()->id;
            }

            $kid = Kid::forProfessional()->create($kidData);

            Log::info('Kid created: ' . $kid->id . ' created by: ' . auth()->user()->id);

            flash(self::MSG_CREATE_SUCCESS)->success();

            DB::commit();

            return redirect()->route('kids.index');
        } catch (Exception $e) {
            DB::rollBack();
            flash(self::MSG_CREATE_ERROR)->warning();
            $message = label_case('Store Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('kids.index');
        }
    }

    public function show(Kid $kid){

    }
    public function showPlane(Kid $kid, $checklistId = null)
    {
        $this->authorize('view', $kid);

        try {
            Log::info('show', [
                'user' => auth()->user()->name,
                'id' => auth()->user()->id,
            ]);

            if ($kid->checklists()->count() === 0) {
                flash(self::MSG_NOT_FOUND_CHECKLIST_USER)->warning();

                return redirect()->back();
            }

            $checklists = $kid->checklists()->orderBy('created_at', 'DESC')->get();
            $kid->months = $kid->months;

            $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
            $ageInMonths = $birthdate->diffInMonths(Carbon::now());

            if ($checklistId) {
                $checklist = Checklist::findOrFail($checklistId);
            } else {
                $checklist = $checklists[0];
            }

            $data = [
                'kid' => $kid,
                'profession' => $kid->professional->name,
                'checklists' => $checklists,
                'checklist' => $checklist,
                'checklist_id' => $checklists[0]->id,
                'level' => $checklists[0]->level,
                'countChecklists' => $kid->checklists()->count(),
                'countPlanes' => $kid->planes()->count(),
                'ageInMonths' => $ageInMonths,
            ];

            return view('kids.show', $data);
        } catch (Exception $e) {
            $message = label_case('Error show kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $kid = Kid::findOrFail($id);
        $this->authorize('update', $kid);
        try {
            $message = label_case('Edit Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            // Verificando se o papel 'pais' existe, caso contrário lançar exceção
            $parentRole = SpatieRole::where('name', 'pais')->first();
            if (!$parentRole) {
                throw new Exception("O papel 'pais' não existe no sistema.");
            }

            // Verificando se o papel 'professional' existe, caso contrário lançar exceção
            $professionalRole = SpatieRole::where('name', 'professional')->first();
            if (!$professionalRole) {
                throw new Exception("O papel 'professional' não existe no sistema.");
            }

            // Buscando usuários com o papel 'pais'
            $responsibles = User::whereHas('roles', function ($query) {
                $query->where('name', 'pais');
            })->get();

            // Buscando usuários com o papel 'professional'
            $professions = User::whereHas('roles', function ($query) {
                $query->where('name', 'professional');
            })->get();


            return view('kids.edit', compact('kid', 'responsibles', 'professions'));
        } catch (Exception $e) {
            flash($e->getMessage())->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function eyeKid($id)
    {
        $kid = Kid::findOrFail($id);
        $this->authorize('view', $kid);

        try {
            $message = label_case('Edit Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            // Verificando se o papel 'pais' existe, caso contrário lançar exceção
            $parentRole = SpatieRole::where('name', 'pais')->first();
            if (!$parentRole) {
                throw new Exception("O papel 'pais' não existe no sistema.");
            }

            // Verificando se o papel 'professional' existe, caso contrário lançar exceção
            $professionalRole = SpatieRole::where('name', 'professional')->first();
            if (!$professionalRole) {
                throw new Exception("O papel 'professional' não existe no sistema.");
            }

            // Buscando usuários com o papel 'pais'
            $responsibles = User::whereHas('roles', function ($query) {
                $query->where('name', 'pais');
            })->get();

            // Buscando usuários com o papel 'professional'
            $professions = User::whereHas('roles', function ($query) {
                $query->where('name', 'professional');
            })->get();


            return view('kids.eye', compact('kid', 'responsibles', 'professions'));
        } catch (Exception $e) {
            flash($e->getMessage())->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(KidRequest $request, Kid $kid)
    {
        // Verifica se o usuário está autorizado a atualizar o registro
        $this->authorize('update', $kid);

        try {
            // Loga a tentativa de atualização
            $message = label_case('Update Kids ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            // Coleta os dados do request
            $data = $request->all();

            // Removida a verificação de papel 'isProfessional'
            // O tratamento para usuários profissionais será feito futuramente

            // Atualiza os dados da criança
            $kid->update($data);

            // Opcional: Disparar job de atualização de criança
            // SendKidUpdateJob::dispatch($kid)->onQueue('emails');

            // Mensagem de sucesso
            flash(self::MSG_UPDATE_SUCCESS)->success();

            // Redireciona para a página de edição
            return redirect()->route('kids.edit', $kid->id);
        } catch (Exception $e) {
            // Loga o erro em caso de falha
            $message = label_case('Update Kids Error' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            // Mensagem de erro
            flash(self::MSG_UPDATE_ERROR)->warning();

            // Redireciona de volta para a página anterior
            return redirect()->back();
        }
    }

    public function destroy(Kid $kid)
    {
        $this->authorize('delete', $kid);
        try {
            $message = label_case('Destroy Kids ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);


            $kid->delete();
            flash(self::MSG_DELETE_SUCCESS)->success();

            return redirect()->route('kids.index');
        } catch (Exception $e) {

            $message = label_case('Destroy Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            flash(self::MSG_NOT_FOUND)->warning();

            return redirect()->back();
        }
    }

    public function pdfPlane($id)
    {
        try {
            $plane = Plane::findOrFail($id);
            $kid_id = $plane->kid()->first()->id;
            $kid = Kid::findOrFail($kid_id);
            $nameKid = $plane->kid()->first()->name;
            $therapist = $kid->professional->name;
            $date = $plane->first()->created_at;
            $arr = [];

            foreach ($plane->competences()->get() as $c => $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->preferences($pdf, $kid, $therapist, $plane->id, $date->format('d/m/Y H:i'));

            $totalDomain = count($arr);
            $countDomain = 1;

            foreach ($arr as $initial => $v) {
                $countCompetences = 1;
                $pdf->AddPage();

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 14);

                // Domain
                $domain = $v['domain']->name;
                $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

                foreach ($v['competences'] as $k => $competence) {

                    if ($countCompetences == 8) {
                        $pdf->AddPage();
                        $countCompetences = 1;
                    }
                    $countCompetences++;

                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', 'B', 10);
                    $txt = $competence->level_id . $v['domain']->initial . $competence->code . ' - ' . $competence->description;
                    $pdf->Ln(5);
                    $pdf->Write(0, $txt, '', 0, 'L', true);

                    $pdf->Ln(1);
                    $pdf->SetFont('helvetica', 'I', 8);
                    $pdf->Write(0, '"' . $competence->description_detail . '"', '', 0, 'L', true);

                    $pdf->Ln(4);
                    $pdf->SetFont('helvetica', '', 9);
                    $etapas = 'Etapa 1.:_____        Etapa 2.:_____       Etapa 3.:_____       Etapa 4.:_____       Etapa 5.:_____';
                    $pdf->Write(0, $etapas, '', 0, 'L', true);
                }
                //                ++$countDomain;
                //                if ($countDomain < $totalDomain) {
                //                    $pdf->AddPage();
                //                }
            }

            $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            $message = label_case('Plane Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error('Exibe Plano Erro', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            dd($e->getMessage());

            flash(self::MSG_NOT_FOUND)->warning();

            //return redirect()->route('kids.index');
        }
    }

    private function preferences(&$pdf, $kid, $therapist, $plane_id, $date)
    {
        $preferences = [
            'HideToolbar' => true,
            'HideMenubar' => true,
            'HideWindowUI' => true,
            'FitWindow' => true,
            'CenterWindow' => true,
            'DisplayDocTitle' => true,
            'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
            'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
            'PrintScaling' => 'AppDefault', // None, AppDefault
            'Duplex' => 'DuplexFlipLongEdge', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
            'PickTrayByPDFSize' => true,
            'PrintPageRange' => [1, 1, 2, 3],
            'NumCopies' => 2,
        ];

        $pdf->setViewerPreferences($preferences);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 18);
        $pdf->Cell(0, 75, 'PLANO DE INTERVENÇÃO N.: ' . $plane_id, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $txt = 'Profissional: ' . $therapist . ',  Data: ' . $date;
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(15);
        $pdf->SetFont('helvetica', '', 18);
        $pdf->Write(0, $kid->name, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->Write(0, $kid->FullNameMonths, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 14);
    }

    public function uploadPhoto(HttpRequest $request, Kid $kid)
    {
        //$this->authorize('update', $kid);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        DB::beginTransaction();

        try {
            // Remove a foto anterior se houver uma
            if ($kid->photo) {
                Storage::disk('public')->delete($kid->photo);
            }

            // Armazena a nova foto
            $photoPath = $request->file('photo')->store('kids_photos', 'public');

            // Atualiza o caminho da foto no banco de dados
            $kid->update(['photo' => $photoPath]);

            // Confirma a transação
            DB::commit();

            // Retorna com sucesso
            flash('Foto atualizada com sucesso!')->success();
        } catch (\Exception $e) {
            // Reverte a transação em caso de falha
            DB::rollBack();

            // Loga o erro
            Log::error('Erro ao fazer upload da foto da criança: ' . $e->getMessage());

            // Retorna mensagem de erro
            flash('Houve um erro ao atualizar a foto. Por favor, tente novamente.')->error();
        }

        return redirect()->route('kids.edit', $kid->id);
    }


    public function teste1()
    {
        // Dados da criança
        $kidId = 2;
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        if (!$kid) {
            return redirect()->back()->with('error', 'Criança não encontrada.');
        }

        // Idade da criança em meses
        $ageInMonths = 25;

        // Competências a avaliar (Nível 2 - Independência Pessoal Higiene)
        $competences = Competence::where('level_id', 2)->where('domain_id', 13)->get();


        // Simulação de percentis e notas
        $simulatedCompetences = [];
        $i = 1;
        foreach ($competences as $competence) {
            // Atribuir percentis simulados
            $competence->percentil_25 = 19 + $i;
            $competence->percentil_50 = 21 + $i;
            $competence->percentil_75 = 23 + $i;
            $competence->percentil_90 = 25 + $i;

            // Simular notas
            $simulatedNotes = [
                1 => 3,
                2 => 2,
                3 => 1,
                4 => 3,
                5 => 2,
                6 => 1,
                7 => 2,
                8 => 3,
                9 => 1,
                10 => 2,
            ];

            $competence->note = $simulatedNotes[$i] ?? null;

            $simulatedCompetences[] = $competence;
            $i++;
        }

        // Preparar os resultados
        $results = [];
        $somaNotas = 0;
        $numAvaliacoes = 0;

        foreach ($simulatedCompetences as $competence) {
            $note = $competence->note;

            // Determinar o status
            $status = '';

            if ($note == 1) {
                // Incapaz
                $status = 'Incapaz';
            } elseif ($note == 2) {
                // Parcial - verificar percentis
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Adiantada';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado';
                } elseif ($ageInMonths >= $competence->percentil_75) {
                    $status = 'Atrasada';
                }
            } elseif ($note == 3) {
                // Adquirido - verificar percentis
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Adiantada';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado';
                } elseif ($ageInMonths >= $competence->percentil_75) {
                    $status = 'Atrasada';
                }
            } elseif ($note == 0) {
                // Não Observado
                $status = 'Não Observado';
            } else {
                $status = 'Não Avaliada';
            }

            // Somar as notas para cálculo da média
            if ($note !== null && $note !== 0) {
                $somaNotas += $note;
                $numAvaliacoes++;
            }

            $results[] = [
                'competence' => $competence->description,
                'note' => $note,
                'status' => $status,
            ];
        }

        // Calcular a média das notas
        if ($numAvaliacoes > 0) {
            $mediaNotas = $somaNotas / $numAvaliacoes;
        } else {
            $mediaNotas = null;
        }

        // Determinar o status geral
        if ($mediaNotas !== null) {
            if ($mediaNotas < 2) {
                $statusGeral = 'Atrasada';
            } elseif ($mediaNotas >= 2 && $mediaNotas < 3) {
                $statusGeral = 'Em processo';
            } elseif ($mediaNotas == 3) {
                $statusGeral = 'Adiantada';
            } else {
                $statusGeral = 'Indeterminado';
            }
        } else {
            $statusGeral = 'Sem avaliações';
        }

        // Preparar dados para o gráfico de radar
        $domains = DB::table('domains')->get();

        // Supondo que temos pontuações para outros domínios, vamos simular
        $radarData = [];
        foreach ($domains as $domain) {
            // Simulação de pontuação média para cada domínio
            $domainAverage = rand(1, 3);

            $radarData[] = [
                'domain' => $domain->initial,
                'average' => $domainAverage,
            ];
        }

        // Atualizar a pontuação do domínio "IPH" com a média real
        foreach ($radarData as &$data) {
            if ($data['domain'] == 'IPH') {
                $data['average'] = $mediaNotas;
                break;
            }
        }

        // Retornar a view com os resultados
        return view('kids.isabela_evaluation', compact('kid', 'results', 'statusGeral', 'mediaNotas', 'radarData'));
    }

    public function teste4()
    {
        $kidId = 2;
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);
        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter o checklist mais recente da criança
        $checklist = Checklist::where('kid_id', $kidId)->orderBy('created_at', 'desc')->first();

        if (!$checklist) {
            return redirect()->back()->with('error', 'Nenhum checklist encontrado para esta criança.');
        }

        // Obter todos os domínios
        $domains = DB::table('domains')->get(); // Supondo que você tenha uma tabela 'domains'

        // Preparar os dados
        $domainResults = [];
        $overallSum = 0;
        $overallCount = 0;

        foreach ($domains as $domain) {
            dd($domain);
            // Obter as competências deste domínio
            $competences = Competence::where('domain_id', $domain->id)->get();

            // Obter as avaliações da criança para essas competências
            $evaluations = DB::table('checklist_competence')
                ->where('checklist_id', $checklist->id)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->get()
                ->keyBy('competence_id');

            // Preparar os resultados por domínio
            $domainData = [];
            $domainSum = 0;
            $domainCount = 0;

            foreach ($competences as $competence) {
                // Obter a nota da avaliação para esta competência
                $evaluation = $evaluations->get($competence->id);

                if ($evaluation) {
                    $note = $evaluation->note;

                    // Determinar o status
                    $status = '';

                    if ($note == 1) {
                        // Incapaz
                        $status = 'Incapaz';
                    } elseif ($note == 2) {
                        // Parcial - verificar percentis
                        if ($ageInMonths < $competence->percentil_25) {
                            $status = 'Adiantada';
                        } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                            $status = 'Dentro do esperado';
                        } elseif ($ageInMonths >= $competence->percentil_75) {
                            $status = 'Atrasada';
                        }
                    } elseif ($note == 3) {
                        // Adquirido - verificar percentis
                        if ($ageInMonths < $competence->percentil_25) {
                            $status = 'Adiantada';
                        } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                            $status = 'Dentro do esperado';
                        } elseif ($ageInMonths >= $competence->percentil_75) {
                            $status = 'Atrasada';
                        }
                    } elseif ($note == 0) {
                        // Não Observado
                        $status = 'Não Observado';
                    } else {
                        $status = 'Não Avaliada';
                    }

                    // Adicionar aos resultados do domínio
                    $domainData[] = [
                        'competence' => $competence->description,
                        'note' => $note,
                        'status' => $status,
                    ];

                    // Somar as notas para cálculo das médias
                    if ($note !== null && $note !== 0) {
                        $domainSum += $note;
                        $domainCount++;
                        $overallSum += $note;
                        $overallCount++;
                    }
                }
            }

            // Calcular a média do domínio
            if ($domainCount > 0) {
                $domainAverage = $domainSum / $domainCount;
            } else {
                $domainAverage = null;
            }

            $domainResults[] = [
                'domain' => $domain->name,
                'competences' => $domainData,
                'average' => $domainAverage,
            ];
        }

        // Calcular a média geral
        if ($overallCount > 0) {
            $overallAverage = $overallSum / $overallCount;
        } else {
            $overallAverage = null;
        }

        // Determinar o status geral
        if ($overallAverage !== null) {
            if ($overallAverage < 2) {
                $statusGeral = 'Atrasado';
            } elseif ($overallAverage >= 2 && $overallAverage < 3) {
                $statusGeral = 'Em processo';
            } elseif ($overallAverage == 3) {
                $statusGeral = 'Adiantado';
            } else {
                $statusGeral = 'Indeterminado';
            }
        } else {
            $statusGeral = 'Sem avaliações';
        }

        // Retornar a view com os resultados
        return view('kids.evaluation_all_domains', compact('kid', 'checklist', 'domainResults', 'statusGeral', 'overallAverage'));
    }

    public function teste3()
    {
        try {
            $kidId = 1;
            // Obter a criança pelo ID
            $kid = Kid::findOrFail($kidId);

            // Idade da criança em meses
            $birthdate = new \DateTime($kid->birth_date);
            $today = new \DateTime();
            $ageInMonths = $birthdate->diff($today)->y * 12 + $birthdate->diff($today)->m;

            // Obter o checklist mais recente da criança
            $checklist = Checklist::where('kid_id', $kidId)->orderBy('created_at', 'desc')->first();

            if (!$checklist) {
                throw new Exception('A criança não tem nenhum checklist.');
            }

            $competences = Competence::where('level_id', $checklist->level)->where('domain_id', 3)->get(); // domain_id 3 = Comunicação Expressiva


            // Obter as avaliações da criança para essas competências
            $evaluations = DB::table('checklist_competence')
                ->where('checklist_id', $checklist->id)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->get()
                ->keyBy('competence_id');

            // Preparar os resultados
            $results = [];
            $somaResultados = 0;
            $numCompetencias = 0;

            foreach ($competences as $competence) {
                // Obter a nota da avaliação para esta competência
                $evaluation = $evaluations->get($competence->id);

                if ($evaluation) {
                    $note = $evaluation->note;
                    $somaResultados += $note;
                    $numCompetencias++;

                    // Determinar o status com base na nota e nos percentis
                    $status = '';
                    if ($note == 1) {
                        // N - Incapaz
                        $status = 'Incapaz';
                    } elseif ($note == 2) {
                        // P - Parcial, verificar com base nos percentis
                        if ($ageInMonths < $competence->percentil_25) {
                            $status = 'Adiantada';  // A criança está parcial, mas está adiantada em relação à faixa esperada
                        } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                            $status = 'Dentro do esperado';  // A criança está parcial e dentro da faixa esperada
                        } elseif ($ageInMonths >= $competence->percentil_75) {
                            $status = 'Atrasada';  // A criança está parcial, mas já passou da faixa esperada
                        }
                    } elseif ($note == 3) {
                        // A - Adquirido, verificar com base nos percentis
                        if ($ageInMonths < $competence->percentil_25) {
                            $status = 'Adiantada';  // A criança adquiriu a competência antes do esperado
                        } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_75) {
                            $status = 'Dentro do esperado';  // A criança adquiriu a competência dentro da faixa esperada
                        } elseif ($ageInMonths >= $competence->percentil_75) {
                            $status = 'Atrasada';  // A criança adquiriu a competência, mas tardiamente
                        }
                    } elseif ($note == 0) {
                        // X - Não Observado
                        $status = 'Não Observado';
                    } else {
                        $status = 'Resultado Desconhecido';
                    }
                } else {
                    $note = null;
                    $status = 'Não Avaliada';
                }

                $results[] = [
                    'competence' => $competence->description,
                    'note' => $note,
                    'status' => $status,
                ];
            }

            // Calcular a média geral se houver avaliações
            if ($numCompetencias > 0) {
                $media = $somaResultados / $numCompetencias;
                $statusGeral = $media < 2 ? 'Em processo' : 'Adquirido';
            } else {
                $media = null;
                $statusGeral = 'Sem Avaliações';
            }

            dd($results, $media, $statusGeral);
        } catch (Exception $e) {
            echo $e->getMessage();
            dd($e->getTraceAsString());
        }
    }

    public function teste2()
    {
        // Obter a criança pelo ID
        $child = Kid::findOrFail(6);

        // Idade da criança em meses
        $ageInMonths = 28; // Para este exemplo, fixamos em 28 meses

        // Dados fictícios das competências
        $competenceLabels = [
            'Comunicação Receptiva',
            'Comunicação Expressiva',
            'Motricidade Grossa',
            'Cognição',
            'Imitação',
            'Social',
            'Independência Pessoal: Alimentação',
            'Independência Pessoal: Vestir',
            'Independência Pessoal: Higiene',
            'Independência Pessoal: Tarefas',
            'Comportamento',
            'Motricidade Fina',
            'Jogo',
            'Comportamento Social',
            'Autonomia',
            'Resolução de Problemas',
        ];

        $childScores = [
            2, // Comunicação Receptiva - A
            1, // Comunicação Expressiva - P
            2, // Motricidade Grossa - A
            1, // Cognição - P
            2, // Imitação - A
            0, // Social - N
            1, // Independência Pessoal: Alimentação - P
            2, // Independência Pessoal: Vestir - A
            1, // Independência Pessoal: Higiene - P
            2, // Independência Pessoal: Tarefas - A
            1, // Comportamento - P
            2, // Motricidade Fina - A
            1, // Jogo - P
            0, // Comportamento Social - N
            1, // Autonomia - P
            2, // Resolução de Problemas - A
        ];

        $percentil25 = [
            22, // Comunicação Receptiva
            23, // Comunicação Expressiva
            24, // Motricidade Grossa
            25, // Cognição
            26, // Imitação
            27, // Social
            24, // Independência Pessoal: Alimentação
            25, // Independência Pessoal: Vestir
            26, // Independência Pessoal: Higiene
            27, // Independência Pessoal: Tarefas
            28, // Comportamento
            25, // Motricidade Fina
            26, // Jogo
            28, // Comportamento Social
            27, // Autonomia
            29, // Resolução de Problemas
        ];

        $percentil50 = [
            24, // Comunicação Receptiva
            25, // Comunicação Expressiva
            26, // Motricidade Grossa
            27, // Cognição
            28, // Imitação
            29, // Social
            26, // Independência Pessoal: Alimentação
            27, // Independência Pessoal: Vestir
            28, // Independência Pessoal: Higiene
            29, // Independência Pessoal: Tarefas
            30, // Comportamento
            27, // Motricidade Fina
            28, // Jogo
            30, // Comportamento Social
            29, // Autonomia
            31, // Resolução de Problemas
        ];

        $percentil75 = [
            26, // Comunicação Receptiva
            27, // Comunicação Expressiva
            28, // Motricidade Grossa
            29, // Cognição
            30, // Imitação
            31, // Social
            28, // Independência Pessoal: Alimentação
            29, // Independência Pessoal: Vestir
            30, // Independência Pessoal: Higiene
            31, // Independência Pessoal: Tarefas
            32, // Comportamento
            29, // Motricidade Fina
            30, // Jogo
            32, // Comportamento Social
            31, // Autonomia
            33, // Resolução de Problemas
        ];

        $percentil90 = [
            28, // Comunicação Receptiva
            29, // Comunicação Expressiva
            30, // Motricidade Grossa
            31, // Cognição
            32, // Imitação
            33, // Social
            30, // Independência Pessoal: Alimentação
            31, // Independência Pessoal: Vestir
            32, // Independência Pessoal: Higiene
            33, // Independência Pessoal: Tarefas
            34, // Comportamento
            31, // Motricidade Fina
            32, // Jogo
            34, // Comportamento Social
            33, // Autonomia
            35, // Resolução de Problemas
        ];

        // Calcular o status para cada competência
        $status = [];
        foreach ($competenceLabels as $index => $competence) {
            $age = $ageInMonths;
            if ($age <= $percentil25[$index]) {
                $status[] = 'Adiantada';
            } elseif ($age > $percentil25[$index] && $age <= $percentil75[$index]) {
                $status[] = 'No Prazo';
            } else {
                $status[] = 'Atrasada';
            }
        }

        // Definir cores com base no status
        $pointColors = [];
        foreach ($status as $s) {
            if ($s == 'Adiantada') {
                $pointColors[] = 'rgb(75, 192, 192)'; // Verde-água
            } elseif ($s == 'No Prazo') {
                $pointColors[] = 'rgb(54, 162, 235)'; // Azul
            } else { // 'Atrasada'
                $pointColors[] = 'rgb(255, 99, 132)'; // Vermelho
            }
        }

        // Preparar dados para o gráfico de pizza (Nível 2)
        $level = 2; // Nível específico para a análise
        // Supondo que as competências do nível 2 são as últimas 13 (indices 3 a 15)
        $level2Competences = array_slice($competenceLabels, 3, 13);
        $level2ChildScores = array_slice($childScores, 3, 13);

        // Contar quantas competências estão Adiantadas, No Prazo ou Atrasadas no Nível 2
        $level2StatusCounts = [
            'Adiantada' => 0,
            'No Prazo' => 0,
            'Atrasada' => 0,
        ];

        foreach ($level2ChildScores as $index => $score) {
            // Mapeamento de score para status
            if ($score == 2) {
                $level2StatusCounts['Adiantada'] += 1;
            } elseif ($score == 1) {
                $level2StatusCounts['No Prazo'] += 1;
            } else {
                $level2StatusCounts['Atrasada'] += 1;
            }
        }

        // Preparar dados para o gráfico de barras
        $idealPercentiles = $percentil50; // Usando percentil50 como ideal
        // Calcular o percentil atual da criança para cada competência
        $childPercentiles = [];
        foreach ($competenceLabels as $index => $competence) {
            $age = $ageInMonths;
            if ($age <= $percentil25[$index]) {
                $childPercentiles[] = 90; // Adiantada
            } elseif ($age > $percentil25[$index] && $age <= $percentil50[$index]) {
                $childPercentiles[] = 75;
            } elseif ($age > $percentil50[$index] && $age <= $percentil75[$index]) {
                $childPercentiles[] = 50;
            } elseif ($age > $percentil75[$index] && $age <= $percentil90[$index]) {
                $childPercentiles[] = 25;
            } else {
                $childPercentiles[] = 10; // Atrasada
            }
        }

        // Calcular a média dos percentis atuais para a linha de evolução
        $averageChildPercentile = array_sum($childPercentiles) / count($childPercentiles);

        return view('kids.teste', compact(
            'child',
            'competenceLabels',
            'childScores',
            'percentil25',
            'percentil50',
            'percentil75',
            'percentil90',
            'ageInMonths',
            'status',
            'pointColors',
            'level2Competences',
            'level2StatusCounts',
            'idealPercentiles',
            'childPercentiles',
            'averageChildPercentile'
        ));
    }

    public function showRadarChart($kidId, $levelId)
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter os domínios para o nível selecionado
        $domainLevels = DB::table('domain_level')->where('level_id', $levelId)->pluck('domain_id');
        $domains = Domain::whereIn('id', $domainLevels)->get();

        // Preparar os dados para o radar geral por domínios
        $radarDataDomains = [];
        foreach ($domains as $domain) {
            // Obter as competências do domínio e nível selecionados
            $competences = Competence::where('domain_id', $domain->id)->where('level_id', $levelId)->get();

            // Obter as avaliações da criança para essas competências
            $evaluations = DB::table('checklist_competence')
                ->join('checklists', 'checklist_competence.checklist_id', '=', 'checklists.id')
                ->where('checklists.kid_id', $kidId)
                ->whereIn('competence_id', $competences->pluck('id'))
                ->select('competence_id', 'note')
                ->get()
                ->keyBy('competence_id');

            // Calcular a média das notas para o domínio
            $sumNotes = 0;
            $countNotes = 0;

            foreach ($competences as $competence) {
                $evaluation = $evaluations->get($competence->id);

                if ($evaluation) {
                    $note = $evaluation->note;

                    if ($note !== null && $note !== 0) {
                        $sumNotes += $note;
                        $countNotes++;
                    }
                }
            }

            if ($countNotes > 0) {
                $average = $sumNotes / $countNotes;
            } else {
                $average = null;
            }

            $radarDataDomains[] = [
                'domain' => $domain->initial,
                'average' => $average,
            ];
        }

        // Retornar a view com os dados do radar geral
        return view('kids.radar_chart', compact('kid', 'ageInMonths', 'levelId', 'radarDataDomains', 'domains'));
    }

    public function showDomainDetails($kidId, $levelId, $domainId, $checklistId = null)
    {
        //dd($kidId, $levelId, $domainId, $checklistId);

        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter o domínio
        $domain = Domain::findOrFail($domainId);

        // Obter o checklist atual (mais recente)
        $currentChecklist = Checklist::where('kid_id', $kidId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Obter o checklist de comparação, se um ID foi fornecido
        if ($checklistId) {
            $previousChecklist = Checklist::find($checklistId);
        } else {
            $previousChecklist = null;
        }

        // Obter as competências do domínio e nível selecionados
        $competences = Competence::where('domain_id', $domainId)
            ->where('level_id', $levelId)
            ->get();

        // Preparar os dados para as avaliações de ambos os checklists
        $radarDataCompetences = [];
        foreach ($competences as $competence) {
            // Inicializar as notas
            $currentNote = null;
            $previousNote = null;

            // Obter a avaliação para o checklist atual
            if ($currentChecklist) {
                $currentEvaluation = DB::table('checklist_competence')
                    ->where('checklist_id', $currentChecklist->id)
                    ->where('competence_id', $competence->id)
                    ->select('note')
                    ->first();
                $currentNote = $currentEvaluation ? $currentEvaluation->note : null;
            }

            // Obter a avaliação para o checklist anterior
            if ($previousChecklist) {
                $previousEvaluation = DB::table('checklist_competence')
                    ->where('checklist_id', $previousChecklist->id)
                    ->where('competence_id', $competence->id)
                    ->select('note')
                    ->first();
                $previousNote = $previousEvaluation ? $previousEvaluation->note : null;
            }

            // Determinar os status
            $currentStatusValue = $this->getStatusValue($currentNote);
            $previousStatusValue = $this->getStatusValue($previousNote);

            // Verificar se a criança deveria passar a competência com base nos percentis
            $shouldPass = [
                '25' => $ageInMonths >= $competence->percentil_25,
                '50' => $ageInMonths >= $competence->percentil_50,
                '75' => $ageInMonths >= $competence->percentil_75,
                '90' => $ageInMonths >= $competence->percentil_90,
            ];

            $shouldPassPercentil = [
                '25' => $ageInMonths >= $competence->percentil_25,
                '50' => $ageInMonths >= $competence->percentil_50,
                '75' => $ageInMonths >= $competence->percentil_75,
                '90' => $ageInMonths >= $competence->percentil_90,
            ];

            // Determinar o progresso em termos de percentil
            // Definir status e cor inicial
            $status = 'Dentro do esperado';
            $statusColor = 'blue';

            // Analisar com base nos percentis e status
            // Analisar com base nos percentis e status
            // Aplicar a lógica para todos os percentis
            if ($currentStatusValue === 3) {
                // Adquirido
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Adiantada'; // Adquirido antes do esperado
                    $statusColor = 'blue';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_50) {
                    $status = 'Adiantada'; // Adquirido entre 25% e 50%, ainda adiantada
                    $statusColor = 'blue';
                } elseif ($ageInMonths >= $competence->percentil_50 && $ageInMonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado'; // Adquirido dentro da faixa normal (50% - 75%)
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_75 && $ageInMonths < $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Adquirido dentro da faixa normal (75% - 90%)
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Adquirido depois do percentil 90, mas adquirido
                    $statusColor = 'orange';
                }
            } elseif ($currentStatusValue === 2) {
                // Em processo
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Dentro do esperado'; // Em processo, mas ainda dentro da faixa esperada (<25%)
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_50) {
                    $status = 'Dentro do esperado'; // Em processo entre 25% e 50%
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_50 && $ageInMonths < $competence->percentil_75) {
                    $status = 'Dentro do esperado'; // Em processo entre 50% e 75%
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_75 && $ageInMonths < $competence->percentil_90) {
                    $status = 'Dentro do esperado'; // Em processo entre 75% e 90%
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_90) {
                    $status = 'Atrasada'; // Em processo após o percentil 90, deveria ter adquirido
                    $statusColor = 'red';
                }
            } elseif ($currentStatusValue === 1) {
                
                // Incapaz ou não avaliado
                if ($ageInMonths < $competence->percentil_25) {
                    $status = 'Dentro do esperado'; // Incapaz, mas ainda dentro da faixa esperada (<25%)
                    $statusColor = 'orange';
                } elseif ($ageInMonths >= $competence->percentil_25 && $ageInMonths < $competence->percentil_50) {
                    $status = 'Atrasada'; // Incapaz entre 25% e 50%
                    $statusColor = 'red';
                } elseif ($ageInMonths >= $competence->percentil_50 && $ageInMonths < $competence->percentil_75) {
                    $status = 'Atrasada'; // Incapaz entre 50% e 75%
                    $statusColor = 'red';
                } elseif ($ageInMonths >= $competence->percentil_75 && $ageInMonths < $competence->percentil_90) {
                    $status = 'Atrasada'; // Incapaz entre 75% e 90%
                    $statusColor = 'red';
                } elseif ($ageInMonths >= $competence->percentil_90) {
                    $status = 'Atrasada'; // Incapaz após o percentil 90, deveria ter adquirido
                    $statusColor = 'red';
                }
            }

            // Determinar o progresso em termos de percentil
            $percentComplete = 0;
            if ($ageInMonths < $competence->percentil_25) {
                $percentComplete = ($ageInMonths / $competence->percentil_25) * 25;
            } elseif ($ageInMonths < $competence->percentil_50) {
                $percentComplete = 25 + (($ageInMonths - $competence->percentil_25) / ($competence->percentil_50 - $competence->percentil_25)) * 25;
            } elseif ($ageInMonths < $competence->percentil_75) {
                $percentComplete = 50 + (($ageInMonths - $competence->percentil_50) / ($competence->percentil_75 - $competence->percentil_50)) * 25;
            } elseif ($ageInMonths < $competence->percentil_90) {
                $percentComplete = 75 + (($ageInMonths - $competence->percentil_75) / ($competence->percentil_90 - $competence->percentil_75)) * 15;
            } else {
                $percentComplete = 90 + (($ageInMonths - $competence->percentil_90) / ($competence->percentil_90)) * 10;
            }
            
            $radarDataCompetences[] = [
                'competence' => $competence->code,
                'description' => $competence->description,
                'currentStatusValue' => $currentStatusValue,
                'previousStatusValue' => $previousStatusValue,
                'shouldPass' => $shouldPass,
                'statusColor' => $statusColor,
                'status' => $status,
                'percentil_25' => $competence->percentil_25,
                'percentil_50' => $competence->percentil_50,
                'percentil_75' => $competence->percentil_75,
                'percentil_90' => $competence->percentil_90
            ];
        }

        // Retornar a view com os dados do radar detalhado
        return view('kids.domain_details', compact(
            'kid',
            'ageInMonths',
            'levelId',
            'domain',
            'radarDataCompetences',
            'currentChecklist',
            'previousChecklist'
        ));
    }

    public function showRadarChart2($kidId, $levelId, $checklistId = null)
    {
        // Obter a criança pelo ID
        $kid = Kid::findOrFail($kidId);

        // Calcular a idade da criança em meses
        $birthdate = Carbon::createFromFormat('d/m/Y', $kid->birth_date);
        $ageInMonths = $birthdate->diffInMonths(Carbon::now());

        // Obter os domínios para o nível selecionado
        $domainLevels = DB::table('domain_level')->where('level_id', $levelId)->pluck('domain_id');
        $domains = Domain::whereIn('id', $domainLevels)->get();

        // Obter o checklist atual (mais recente)
        $currentChecklist = Checklist::where('kid_id', $kidId)
            ->orderBy('id', 'desc')
            ->first();

        // Verificar se existe um checklist atual
        if (!$currentChecklist) {
            // Tratar o caso em que não há checklists para a criança
            throw new ('Nenhum checklist encontrado!');
        }

        // Obter o checklist de comparação, se um ID foi fornecido
        if ($checklistId) {
            $previousChecklist = Checklist::find($checklistId);
        } else {
            $previousChecklist = null;
        }

        // Obter todos os checklists para o combobox, excluindo o atual
        $allChecklists = Checklist::where('kid_id', $kidId)
            ->where('id', '<>', $currentChecklist->id)
            ->orderBy('id', 'desc')
            ->get();

        // Obter os dois checklists mais recentes da criança
        $checklists = Checklist::where('kid_id', $kidId)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        // Preparar os dados para o radar geral por domínios
        $radarDataDomains = [];
        $levels = [];
        foreach ($domains as $domain) {
            // Obter as competências do domínio e nível selecionados
            $competences = Competence::where('domain_id', $domain->id)
                ->where('level_id', $levelId)
                ->get();

            // Inicializar as médias como null
            $currentAverage = null;
            $previousAverage = null;

            // Calcular a média para o checklist atual, se existir
            if ($currentChecklist) {
                $currentEvaluations = DB::table('checklist_competence')
                    ->where('checklist_id', $currentChecklist->id)
                    ->whereIn('competence_id', $competences->pluck('id'))
                    ->select('competence_id', 'note')
                    ->get()
                    ->keyBy('competence_id');

                $currentSumNotes = 0;
                $currentCountNotes = 0;

                foreach ($competences as $competence) {
                    $evaluation = $currentEvaluations->get($competence->id);

                    if ($evaluation) {
                        $note = $evaluation->note;

                        if ($note !== null && $note !== 0) {
                            $currentSumNotes += $note;
                            $currentCountNotes++;
                        }
                    }
                }

                $currentAverage = $currentCountNotes > 0 ? $currentSumNotes / $currentCountNotes : null;
            }

            // Calcular a média para o checklist anterior, se existir
            if ($previousChecklist) {
                $previousEvaluations = DB::table('checklist_competence')
                    ->where('checklist_id', $previousChecklist->id)
                    ->whereIn('competence_id', $competences->pluck('id'))
                    ->select('competence_id', 'note')
                    ->get()
                    ->keyBy('competence_id');

                $previousSumNotes = 0;
                $previousCountNotes = 0;

                foreach ($competences as $competence) {
                    $evaluation = $previousEvaluations->get($competence->id);

                    if ($evaluation) {
                        $note = $evaluation->note;

                        if ($note !== null && $note !== 0) {
                            $previousSumNotes += $note;
                            $previousCountNotes++;
                        }
                    }
                }

                $previousAverage = $previousCountNotes > 0 ? $previousSumNotes / $previousCountNotes : null;
            }

            $radarDataDomains[] = [
                'domain' => $domain->initial,
                'currentAverage' => $currentAverage,
                'previousAverage' => $previousAverage,
            ];


            for ($i = 1; $i <= $currentChecklist->level; $i++) {
                $levels[$i] = $i;
            }
        }
        $countPlanes = 1;
        $countChecklists = Checklist::where('kid_id', $kidId)->count();

        // Retornar a view com os dados do radar geral
        return view('kids.radar_chart2', compact(
            'kid',
            'ageInMonths',
            'levelId',
            'radarDataDomains',
            'domains',
            'currentChecklist',
            'previousChecklist',
            'allChecklists',
            'levels',
            'countChecklists',
            'countPlanes'
        ));
    }

    private function getStatusValue($note)
    {
        if ($note == 1) {
            return 1; // Incapaz
        } elseif ($note == 2) {
            return 2; // Em Processo
        } elseif ($note == 3) {
            return 3; // Adquirido
        } else {
            return 0; // Não Avaliado
        }
    }
}
