<?php

namespace App\Http\Controllers;

use App\Http\Requests\KidRequest;
use App\Models\Kid;
use App\Models\Plane;
use App\Models\Responsible;
use App\Models\User;
use App\Util\MyPdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Yajra\DataTables\DataTables;
use Elibyy\TCPDF\Facades\TCPDF;

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
        $data = Kid::getKids();
        return Datatables::of($data)
            ->addColumn('action', function ($data) {

                if (request()->user()->can('kids.update') || request()->user()->can('kids.store')) {

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
            ->editColumn('user_id', function ($data) {
                return $data->user->name;
            })
            ->editColumn('responsible_id', function ($data) {
                return '?';
            })
            ->rawColumns(['name', 'checklists', 'user_id', 'responsible_id', 'action'])
            ->orderColumns(['id'], '-:column $1')
            ->make(true);
    }

    public function create()
    {
        $message = label_case('Create Kids') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
        Log::info($message);

        $users = User::scopeListUsers();
        $responsibles = Responsible::all();

        return view('kids.create', compact('users', 'responsibles'));
    }

    public function store(KidRequest $request)
    {
        try {
            $message = label_case('Store Kids ' . self::MSG_CREATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            Kid::create($request->all());
            flash(self::MSG_CREATE_SUCCESS)->success();
            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_CREATE_ERROR)->warning();
            $message = label_case('Store Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('kids.index');
        }
    }

    public function show(Kid $kid)
    {
        try {
            $message = label_case('Show Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            if ($kid->checklists()->count() === 0) {
                flash(self::MSG_NOT_FOUND_CHECKLIST_USER)->warning();
                return redirect()->back();
            }

            $checklists = $kid->checklists()->orderBy('created_at', 'DESC')->get();

            $data = [
                'kid' => $kid,
                'checklists' => $checklists,
                'checklist_id' => $checklists[0]->id,
                'level' => $checklists[0]->level
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
        try {
            $message = label_case('Edit Kids ') . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $users = User::scopeListUsers();
            $responsibles = Responsible::all();

            return view('kids.edit', compact('kid', 'users', 'responsibles'));
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function update(KidRequest $request, Kid $kid)
    {
        try {
            $message = label_case('Update Kids ' . self::MSG_UPDATE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);

            $data = $request->all();
            $kid->update($data);
            flash(self::MSG_UPDATE_SUCCESS)->success();
            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_UPDATE_ERROR)->warning();
            $message = label_case('Update Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->back();
        }
    }

    public function destroy(Kid $kid)
    {
        try {
            $message = label_case('Destroy Kids ' . self::MSG_DELETE_SUCCESS) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::info($message);
            $kid->delete();
            flash(self::MSG_DELETE_SUCCESS)->success();
            return redirect()->route('kids.index');
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Destroy Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

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
            $therapist = $kid->user()->first()->name;
            $date = $plane->first()->created_at;
            $arr = [];

            foreach ($plane->competences()->get() as $c => $competence) {
                $initial = $competence->domain()->first()->initial;
                $arr[$initial]['domain'] = $competence->domain()->first();
                $arr[$initial]['competences'][] = $competence;
            }

            $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $this->preferences($pdf, $nameKid, $therapist, $plane->id, $date->format('d/m/Y H:i:s'));

            $totalDomain = count($arr);
            $countDomain = 1;
            $countCompetences = 1;

            foreach ($arr as $initial => $v) {

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 14);

                // Domain
                $domain = $v['domain']->name;
                $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

                foreach ($v['competences'] as $k => $competence) {

                    if ($countCompetences == 7) {
                        $pdf->AddPage();
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
                    $etapas = "Etapa 1.:_____        Etapa 2.:_____       Etapa 3.:_____       Etapa 4.:_____       Etapa 5.:_____";
                    $pdf->Write(0, $etapas, '', 0, 'L', true);
                }
                ++$countDomain;
                if ($countDomain <= $totalDomain) {
                    $pdf->AddPage();
                }
            }

            $pdf->Output($nameKid . '_' . $date->format('dmY') . '_' . $plane->id . '.pdf', 'I');
        } catch (Exception $e) {
            flash(self::MSG_NOT_FOUND)->warning();
            $message = label_case('Plane Kids ' . $e->getMessage()) . ' | User:' . auth()->user()->name . '(ID:' . auth()->user()->id . ')';
            Log::error($message);

            return redirect()->route('kids.index');
        }
    }

    private function preferences(&$pdf, $nameKid, $therapist, $plane_id, $date)
    {
        $preferences = array(
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
            'PrintPageRange' => array(1, 1, 2, 3),
            'NumCopies' => 2
        );

        $pdf->setViewerPreferences($preferences);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 16);
        $pdf->Cell(0, 15, 'PLANO DE INTERVENÇÃO N.: ' . $plane_id, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $txt = 'Terapeuta: ' . $therapist . ',  Data: ' . $date;
        $pdf->Cell(0, 2, $txt, 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 16);
        $pdf->Write(0, $nameKid, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', '', 14);
    }
}
