<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Jobs\SendKidUpdateJob;
use App\Models\Kid;
use App\Models\Plane;
use App\Models\Responsible;
use App\Models\User;
use App\Util\MyPdf;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Spatie\Permission\Models\Role as SpatieRole;

class KidsController extends Controller
{
    public function index()
    {
        $message = label_case('Index Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::debug($message);

        return view('kids.index');
    }

    public function index_data()
    {

        $data = Kid::All();

        return Datatables::of($data)
            ->addColumn('action', function ($data) {

                if (request()->user()->can('edit kids')) {

                    $html = '<div class="dropdown">';
                    $html .= '<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-gear"></i></button><ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
                    $html .= '<li><a class="dropdown-item" href="' . route('kids.edit', $data->id) . '"><i class="bi bi-pencil"></i> Editar</a></li>';

                    if ($data->checklists()->count() > 0) {
                        $html .= '<li><a class="dropdown-item" href="' . route('kids.show', $data->id) . '"><i class="bi bi-check2-square"></i> Checklist</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                }
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('birth_date', function ($data) {
                return $data->birth_date;
            })
            ->editColumn('checklists', function ($data) {
                return '<span class="badge bg-success"><i class="bi bi-check"></i> ' . $data->checklists->count() . ' Checklist(s) </span>';
            })
            ->editColumn('profession_id', function ($data) {
                // Exibe o nome do profissional ou 'Não atribuído' caso não tenha um profissional
                return $data->professional ? $data->professional->name : 'Não atribuído';
            })
            ->editColumn('responsible_id', function ($data) {
                // Exibe o nome do responsável ou 'Não atribuído' caso não tenha um responsável
                return $data->responsible ? $data->responsible->name : 'Não atribuído';
            })
            ->rawColumns(['name', 'checklists', 'responsible', 'action'])
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
            ];

            // removido is professional

            $kid = Kid::create($kidData);
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

    public function show(Kid $kid)
    {
        try {
            //dd('show kids');
            Log::info('', [
                'user' => auth()->user()->name,
                'id' => auth()->user()->id,
            ]);

            if ($kid->checklists()->count() === 0) {
                flash(self::MSG_NOT_FOUND_CHECKLIST_USER)->warning();

                return redirect()->back();
            }

            $checklists = $kid->checklists()->orderBy('created_at', 'DESC')->get();
            $kid->months = $kid->months;
            //$kid->profession = $kid->user->name;
            $data = [
                'kid' => $kid,
                'profession' => $kid->professional->name,
                'checklists' => $checklists,
                'checklist_id' => $checklists[0]->id,
                'level' => $checklists[0]->level,
                'countChecklists' => $kid->checklists()->count(),
                'countPlanes' => $kid->planes()->count(),
            ];

            return view('kids.show', $data);
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Show Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function edit(Kid $kid)
    {
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
        $txt = 'Terapeuta: ' . $therapist . ',  Data: ' . $date;
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
}
