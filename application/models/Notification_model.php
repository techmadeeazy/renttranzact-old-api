<?php

defined('BASEPATH') or exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!class_exists('PHPMailer\PHPMailer\Exception')) {
  require APPPATH . 'third_party/PHPMailer6/src/Exception.php';
  require APPPATH . 'third_party/PHPMailer6/src/PHPMailer.php';
  require APPPATH . 'third_party/PHPMailer6/src/SMTP.php';
}

class Notification_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function testSendEmail($toEmail)
  {
    $fullName = 'Joe Doe';
    $subject = "[Email Test] -  RenTranzact";
    $body = "Dear $fullName, <br> "
      . "<br>Use the code to verify your email address : " . date('Y:m:d H:i:s')
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@RenTranzact.com"
      . "<br><br>Regards,<br><br> RenTranzact Team.";
    $this->sendMailTest($toEmail, $fullName, $subject, $body);
  }

  public function sendVerifyEmail($toEmail, $fullName, $code)
  {
    $subject = "[Verification Code] -  RenTranzact";
    $body = "Dear $fullName, <br> "
      . "<br>Use the code to verify your email address : $code"
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@RenTranzact.com"
      . "<br><br>Regards,<br><br> RenTranzact Team.";
    $this->sendMail($toEmail, $fullName, $subject, $body);
  }

  public function sendPasswordResetEmail($toEmail, $fullName, $code)
  {
    $subject = "[Password Reset] -  RenTranzact";
    $body = "Dear $fullName, <br> Your  registered  e-mail address
        on RenTranzact requested for your login details."
      . "<br>Use the code to reset your password : $code"
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@RenTranzact.com"
      . "<br><br>Regards,<br><br> RenTranzact Team.";
    $this->sendMail($toEmail, $fullName, $subject, $body);
  }

  public function sendRegistrationEmail($toEmail, $fullName, $reference)
  {
    $mailBody = "Dear $fullName, <br> Thank you for registering on RenTranzact"
      . "<br> Click here to confirm your email address.<br> Here is the link: $reference"
      . "<br>For any enquiries contact  Support Team at customersupport@RenTranzact.com"
      . "<br><br>Regards,<br><br> RenTranzact Team.";
    //$altMailBody = $mail->Body;

    $this->sendMail($toEmail, $fullName, "[Confirm Your Email] -  RenTranzact", $mailBody);
  }

  public function sendBookingEmail($bookingData, $propertyData)
  {
    $this->load->model('UserAuth_model');
    $hostData = $this->UserAuth_model->getById($bookingData['host_id']);
    $inspectorData = $this->UserAuth_model->getById($bookingData['inspector_id']);
    //Send acknowledgement email to user
    $inspectorMailBody = "Dear {$inspectorData['username']},
    <br><br>We've received your booking inspection request on the property (" . $propertyData['title'] . "). A member of our team will reach out to you shortly to schedule the inspection of the property.
    <br><br>
    Please note that you may be required to provide additional information or documents to enable us to verify your identity and protect our landlords and property managers from fraudulent potential tenants.
    <br><br>
    You can also reach out to our support staff to expedite this action through: customersupport@rentranzact.com
    <br><br>Regards,<br><br> RenTranzact Team.<br>www.rentranzact.com";

    $this->sendMail($inspectorData['email_address'], $inspectorData['username'], "[Inspection Booking] -  {$propertyData['title']}", $inspectorMailBody);
    //Send mail to admin
    $adminMailBody = "Dear Admin,
<br><br>A new inspection booking is awaiting approval on a property. Please review the inspection request and schedule a call with the property owner/manager.<br>
    <br>Username: {$inspectorData['username']}
    <br>Property Name: {$propertyData['title']}
    <br>Property Manager: {$hostData['username']}
    <br><br>For any enquiries contact  Support Team at customersupport@rentranzact.com
    <br><br>Regards,<br><br> RenTranzact Team.";
    //$altMailBody = $mail->Body;
    //$hostData['email_address']
    $this->sendMail('customersupport@rentranzact.com', $hostData['username'], "[Inspection Booking] -  {$propertyData['title']}", $adminMailBody);
  }

  /**
   * Easy access to user data
   */
  private function getUserById($id)
  {
    $query = $this->db->query("SELECT * FROM users WHERE id = '$id'");
    return $query->row_array();
  }

  public function sendMail($toEmail, $toName, $subject, $body)
  {
    $mail = new PHPMailer();
    $mail->SMTPDebug = 0;                               // Enable verbose debug output outlook.smtp.com
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.sendgrid.net';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'apikey';                 // SMTP username
    $mail->Password = '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';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to
    //$mail->addBCC('');
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->setFrom('noreply@rentranzact.com', 'RenTranzact');
    $mail->addAddress($toEmail, $toName);
    $mail->Subject = $subject;
    $mail->Body = $body;
    if (!$mail->send()) {
      log_message('error', 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
      return false;
    } else {
      log_message('info', 'Message has been sent');
      return true;
    }
  }
  public function sendMailTest($toEmail, $toName, $subject, $body)
  {
    $mail = new PHPMailer();
    $mail->SMTPDebug = 3;                               // Enable verbose debug output outlook.smtp.com
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.sendgrid.net';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'apikey';                 // SMTP username
    $mail->Password = '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';                           // SMTP password
    $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465;                                    // TCP port to connect to
    //$mail->addBCC('');
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->setFrom('noreply@rentranzact.com', 'RenTranzact');

    $mail->addAddress($toEmail, $toName);
    $mail->Subject = $subject;
    $mail->Body = $body;
    log_message('debug', 'Simulate email sending');
    if (!$mail->send()) {
      log_message('error', 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
      return false;
    } else {
      log_message('info', 'Message has been sent');
      return true;
    }
  }
}
