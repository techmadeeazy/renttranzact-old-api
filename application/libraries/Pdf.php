<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'third_party/tcpdf/tcpdf.php');

class Pdf extends TCPDF
{
    function __construct()
    {
        parent::__construct(); 
    }

    public function Header() {
        
        $image_file = FCPATH.'assets/images/coronation.png';
        $this->SetFont(PDF_FONT_NAME_MAIN, 'B', 9);
        $this->SetTextColor(167, 147, 68);
        $this->Image($image_file, 125, 3);
        //$this->Cell(0, 10, 'https://www.coronation.ng/', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    public function Footer() {
        $this->SetY(-10);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(0, 10, 'https://www.coronation.ng/', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */ 