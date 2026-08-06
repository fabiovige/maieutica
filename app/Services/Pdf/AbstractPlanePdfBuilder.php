<?php

namespace App\Services\Pdf;

use App\Models\Kid;
use App\Models\Plane;
use App\Util\MyPdf;

abstract class AbstractPlanePdfBuilder
{
    protected Kid $kid;

    protected string $therapist = '';

    protected Plane $plane;

    protected array $competencesByDomain = [];

    protected ?string $planeNameForDisplay = null;

    /**
     * Resolve (e, quando necessário, persiste) os dados do plano: $kid, $plane,
     * $therapist e $competencesByDomain. É o único passo que varia entre os
     * três fluxos de geração de PDF de plano.
     */
    abstract protected function resolvePlaneData(): void;

    public function build(): MyPdf
    {
        $this->resolvePlaneData();

        $pdf = new MyPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->applyPreferences($pdf);
        $this->renderDomains($pdf);

        return $pdf;
    }

    public function getKid(): Kid
    {
        return $this->kid;
    }

    public function getPlane(): Plane
    {
        return $this->plane;
    }

    protected function groupCompetencesByDomain(iterable $competences): void
    {
        $this->competencesByDomain = [];

        foreach ($competences as $competence) {
            $initial = $competence->domain()->first()->initial;
            $this->competencesByDomain[$initial]['domain'] = $competence->domain()->first();
            $this->competencesByDomain[$initial]['competences'][] = $competence;
        }
    }

    protected function resolveTherapistNames(Kid $kid): string
    {
        $professionalNames = [];
        foreach ($kid->professionals()->get() as $professional) {
            $professionalNames[] = $professional->user->first()->name.' - ('.$professional->specialty->name.')';
        }

        return implode("\n", $professionalNames);
    }

    protected function applyPreferences(MyPdf $pdf): void
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
        $pdf->Cell(0, 60, 'PLANO DE INTERVENÇÃO N.: '.$this->plane->id, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 16);
        $pdf->Write(0, $this->kid->name, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(2);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, $this->kid->FullNameMonths, '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(15);

        // Nome do(s) profissional(is) atualmente não impresso no PDF (mantido
        // desde a versão original dos três métodos do KidsController).
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Data: '.$this->plane->created_at->format('d/m/Y H:i'), '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln(15);

        if ($this->planeNameForDisplay) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Write(0, '('.$this->planeNameForDisplay.')', '', 0, 'C', true, 0, false, false, 0);
            $pdf->Ln(3);
        }
    }

    protected function renderDomains(MyPdf $pdf): void
    {
        foreach ($this->competencesByDomain as $v) {
            $countCompetences = 1;
            $pdf->AddPage();

            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 14);

            $domain = $v['domain']->name;
            $pdf->Cell(0, 0, $domain, 1, 1, 'L', 0, '', 0);

            foreach ($v['competences'] as $competence) {
                if ($countCompetences == 8) {
                    $pdf->AddPage();
                    $countCompetences = 1;
                }
                $countCompetences++;

                $pdf->Ln(5);
                $pdf->SetFont('helvetica', 'B', 10);
                $txt = $competence->level_id.$v['domain']->initial.$competence->code.' - '.$competence->description;
                $pdf->Ln(5);
                $pdf->Write(0, $txt, '', 0, 'L', true);

                $pdf->Ln(1);
                $pdf->SetFont('helvetica', 'I', 8);
                $pdf->Write(0, '"'.$competence->description_detail.'"', '', 0, 'L', true);

                $pdf->Ln(4);
                $pdf->SetFont('helvetica', '', 9);
                $etapas = 'Etapa 1.:_____        Etapa 2.:_____       Etapa 3.:_____       Etapa 4.:_____       Etapa 5.:_____';
                $pdf->Write(0, $etapas, '', 0, 'L', true);
            }
        }
    }
}
