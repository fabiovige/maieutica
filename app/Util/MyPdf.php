<?php

namespace App\Util;

use TCPDF;

class MyPdf extends TCPDF
{
    protected $image_file = '';

    protected $col = 0;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->image_file = '/var/www/public/images/logo_login.png';

        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Fabio Vige <fabiovige@gmail.com>');
        $this->SetTitle(config('app.name'));
        $this->SetSubject(config('app.description'));

        // set default header data
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 009', PDF_HEADER_STRING);

        // set header and footer fonts
        $this->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(2);
        $this->SetFooterMargin(2);

        // set auto page breaks
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    }

    // Page header
    public function Header($data = '')
    {
        // Set font
        $this->SetY(5);
        $this->SetFont('helvetica', '', 10);

        // Title
        $this->Cell(0, -10, config('app.name').' - '.config('app.description').'.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-22);

        // Set font
        $this->SetFont('helvetica', '', 9);

        $name = 'www.maieutica.com.br';

        $txt1 = 'A descrição das etapas é apenas uma sugestão e que pode ser alterada de acordo com a prática do professional e adequada às necessidades específicas da criança.';
        $txt2 = 'Os objetivos aqui descritos visam facilitar a consulta, sobretudo pelos pais, e segue fielmente o conteúdo do checklist original com';
        $txt3 = 'Copyright © 2010 The Guiford Press, com direitos de publicação, em lingua portuguesa a Lidel Edições Técnicas Lda, dos autores Sally J. Rogers e Geraldine Dawson';

        // Page number
        $this->Cell(0, 5, $name, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetFont('helvetica', '', 6);
        $this->ln(5);
        $this->Cell(0, 4, $txt1, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->ln(3);
        $this->Cell(0, 4, $txt2, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->ln(3);
        $this->Cell(0, 4, $txt3, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        $this->ln(2);
        $this->Cell(0, 10, 'Página.: '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }
}
