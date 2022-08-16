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
    $subject = "[Email Test] -  RentTranzact";
    $body = "Dear $fullName, <br> "
      . "<br>Use the code to verify your email address : " . date('Y:m:d H:i:s')
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@renttranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    $this->sendMailTest($toEmail, $fullName, $subject, $body);
  }

  public function sendVerifyEmail($toEmail, $fullName, $code)
  {
    $subject = "[Verification Code] -  RentTranzact";
    $body = "Dear $fullName, <br> "
      . "<br>Use the code to verify your email address : $code"
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@renttranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    $this->sendMail($toEmail, $fullName, $subject, $body);
  }

  public function sendPasswordResetEmail($toEmail, $fullName, $code)
  {
    $subject = "[Password Reset] -  RentTranzact";
    $body = "Dear $fullName, <br> Your  registered  e-mail address
        on RentTranzact requested for your login details."
      . "<br>Use the code to reset your password : $code"
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@renttranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    $this->sendMail($toEmail, $fullName, $subject, $body);
  }

  public function sendRegistrationEmail($toEmail, $fullName, $reference)
  {
    $mailBody = "Dear $fullName, <br> Thank you for registering on RentTranzact"
      . "<br> Click here to confirm your email address.<br> Here is the link: $reference"
      . "<br>For any enquiries contact  Support Team at customersupport@renttranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    //$altMailBody = $mail->Body;

    $this->sendMail($toEmail, $fullName, "[Confirm Your Email] -  RentTranzact", $mailBody);
  }

  public function sendBookingEmail($bookingData)
  {
    $this->load->model('User_model');
    $hostData = $this->User_model->getUserById($bookingData['user_auth_id']);
    $mailBody = "Your property [" . $bookingData['title'] . "] has just being booked for inspection.
    <br>Please check your app and respond to the request within 72hours.
    <br>For any enquiries contact  Support Team at customersupport@rentranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    //$altMailBody = $mail->Body;
    //$hostData['email_address']
    $this->sendMail('customersupport@rentranzact.com', $hostData['username'], "[Inspection Booking] -  RentTranzact", $mailBody);
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
    $mail->setFrom('noreply@rentranzact.com', 'RentTranzact');
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
    $mail->setFrom('noreply@rentranzact.com', 'RentTranzact');

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
