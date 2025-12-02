<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        $watermark = base64_encode(file_get_contents(public_path('images/bg-doc.png')));
        $logo = base64_encode(file_get_contents(public_path('images/logo-doc.jpg'))); // se for adicionar

        $data = [
            'nome_paciente'   => 'NOME COMPLETO DA PESSOA ATENDIDA',
            'dias_horarios'   => 'às segundas e quartas-feiras, das 14h às 16h',
            'previsao_termino'=> null,
            'nome_psicologo'  => 'NOME COMPLETO DO PSICÓLOGO',
            'crp'             => 'XX/000000',
            'cidade'          => 'Santana de Parnaíba',
            'data_formatada'  => now()->format('d \d\\e F \d\\e Y'),
            'watermark'       => $watermark,
            'logo'            => $logo,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documents.declaracao', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('declaracao.pdf');
    }

}
