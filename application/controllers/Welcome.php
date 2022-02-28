<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

   /**
    * Index Page for this controller.
    *
    * Maps to the following URL
    * 		http://example.com/index.php/welcome
    * 	- or -
    * 		http://example.com/index.php/welcome/index
    * 	- or -
    * Since this controller is set as the default controller in
    * config/routes.php, it's displayed at http://example.com/
    *
    * So any other public methods not prefixed with an underscore will
    * map to /index.php/welcome/<method_name>
    * @see https://codeigniter.com/user_guide/general/urls.html
    */
   public function index()
   {
      $this->load->view('welcome_message');
   }

   public function test()
   {
      $this->load->library('Pdf');
      // create new PDF document
      $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

      // set document information
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Coronation');
      //$pdf->SetTitle('Coranation receipt');
      $pdf->SetSubject('Coranation receipt');
      $pdf->SetKeywords('Coronation, Finance, Insurance, Asset');

      // set default header data
      //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

      // set header and footer fonts
      $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
      //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

      // set default monospaced font
      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

      // set margins
      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

      // set auto page breaks
      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

      // set image scale factor
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

      // set some language-dependent strings (optional)
      if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
         require_once(dirname(__FILE__) . '/lang/eng.php');
         $pdf->setLanguageArray($l);
      }

      // ---------------------------------------------------------
      
      //$pdf->addTTFfont('Lato-Light.ttf');
      // set font
      $pdf->SetFont('helvetica', '', 10);

      // add a page
      $pdf->AddPage();

      // writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
      // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

      // create some HTML content
      $html = '<style>'.file_get_contents(FCPATH.'/assets/css/bootstrap.min.css').'</style>';
      $html .= '<div class="container">
      <div class="row">
         <div class="well col-xs-10 col-sm-10 col-md-6 col-xs-offset-1 col-sm-offset-1 col-md-offset-3">
            <div class="row">
               <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                  <p>
                     <em><b>Transaction Date:</b>August 3, 2021</em>
                  </p>
                  <p>
                     <em><b>Receipt Number:</b>00000501</em>
                  </p>
                  <p>
                     <em><b>Order Number:</b>#234</em>
                  </p>
               </div>
            </div>
            <div class="card-body">
               <div class="form-group row">
                  <label for="customerName" class="col-md-4 col-form-label text-md-right">Customer Name</label>
                  <div class="col-md-6">
                     Joe Olu
                  </div>
               </div>
               <div class="form-group row">
                  <label for="productPurchased" class="col-md-4 col-form-label text-md-right">Product Purchased</label>
                  <div class="col-md-6">
                     Motor Insurance
                  </div>
               </div>
               <div class="form-group row">
                  <label for="amountPaid" class="col-md-4 col-form-label text-md-right">Amount Paid</label>
                  <div class="col-md-6">
                     5000
                  </div>
               </div>
            </div>
            <div class="text-center">
               <em>Other Documents to be sent to you by email</em><br>
               <em>Thank you for choosing us</em>
            </div>
         </div>
      </div>';
      $pdf->writeHTML($html, true, false, true, false, '');

      // reset pointer to the last page
      $pdf->lastPage();
      // ---------------------------------------------------------

      //Close and output PDF document
      $fileName = "C:\laragon\www\api_hub\public\download\coronation_" . rand() . '.pdf';
      $pdf->Output($fileName, 'I');
     // echo "File: "   . $fileName;
      //============================================================+
      // END OF FILE
      //============================================================+   
   }

   public function test2()
   {
    //$data = 'some more and more data';
      $this->load->helper('file');
      //write_file(APPPATH . 'config/hrms.php', $data);

      //$string = file_get_contents(APPPATH . 'config/hrms.php');
      $fileInfo = get_file_info(APPPATH . 'config/hrms.php');

      echo  date('Y:m:d h i s',$fileInfo['date']);
      echo '<br>';
      echo 'Now:'.time();
      echo '<br> Then:'. $fileInfo['date']. '<br>';
      echo (time() - $fileInfo['date']) > 6;//72000 seconds is 20 hours
      //echo $string;
   }
   public function test3(){
      //echo FCPATH;
      $html = '<style>'.file_get_contents(FCPATH.'/assets/css/bootstrap.min.css').'</style>';
      echo $html;
   }
}
