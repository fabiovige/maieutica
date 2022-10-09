<?php

namespace App\Util;

use TCPDF;

class MyPdf extends TCPDF {


    protected $image_file = '';

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        $this->image_file = '/var/www/public/images/logo_login.png';

        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Fabio Vige <fabiovige@gmail.com>');
        $this->SetTitle(config('app.name') .' '. config('app.description'));
        $this->SetSubject(config('app.name') .' '. config('app.description'));

        // set default header data
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 009', PDF_HEADER_STRING);

        // set header and footer fonts
        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // quality image
        $this->setJPEGQuality(72);
    }

    //Page header
    public function Header() {

        // Logo
        $this->Image($this->image_file, 90, 10, 0, 15, 'PNG', '', 'C', false, 72, '', false, false, 0, 'C', false, false);

        // Set font
        //$this->SetFont('helvetica', '', 8);

        // Title
        //$this->Cell(0, 0, config('app.name') . ' - '. config('app.description'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-10);

        // Set font
        $this->SetFont('helvetica', '', 7);

        $name = config('app.name') . ' - '. config('app.description');

        $txt = <<<EOD
            A descrição das etapas é apenas uma sugestão e que pode ser alterada de acordo com a prática do profissional e adequada às necessidades específicas da criança.<br>

            Os objetivos aqui descritos visam facilitar a consulta, sobretudo pelos pais, e segue fielmente o conteúdo do checklist original com<br>

            Copyright © 2010 The Guiford Press, com direitos de publicação, em lingua portuguesa a Lidel Edições Técnicas Lda, dos autores Sally J. Rogers e Geraldine Dawson<br>

        EOD;

        // Page number
        $this->Cell(0, 5, $name , 0, false, 'C', 0, '', 0, false, 'T', 'M');

    }

    /** Print chapter
    * @param $num (int) chapter number
    * @param $title (string) chapter title
    * @param $file (string) name of the file containing the chapter body
    * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
    * @public
    */
    public function PrintChapter($num, $title, $file, $mode=false) {
        // add a new page
        $this->AddPage();
        // disable existing columns
        $this->resetColumns();
        // print chapter title
        $this->ChapterTitle($num, $title);
        // set columns
        $this->setEqualColumns(3, 57);
        // print chapter body
        $this->ChapterBody($file, $mode);
    }

    /**
     * Set chapter title
     * @param $num (int) chapter number
     * @param $title (string) chapter title
     * @public
     */
    public function ChapterTitle($num, $title) {
        $this->SetFont('helvetica', '', 14);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(180, 6, 'Chapter '.$num.' : '.$title, 0, 1, '', 1);
        $this->Ln(4);
    }

    /**
     * Print chapter body
     * @param $file (string) name of the file containing the chapter body
     * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
     * @public
     */
    public function ChapterBody($file, $mode=false) {
        $this->selectColumn();
        // get esternal file content
        $content = file_get_contents($file, false);
        // set font
        $this->SetFont('times', '', 9);
        $this->SetTextColor(50, 50, 50);
        // print content
        if ($mode) {
            // ------ HTML MODE ------
            $this->writeHTML($content, true, false, true, false, 'J');
        } else {
            // ------ TEXT MODE ------
            $this->Write(0, $content, '', 0, 'J', true, 0, false, true, 0);
        }
        $this->Ln();
    }
}
