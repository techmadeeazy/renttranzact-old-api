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
    //$this->load->database();
  }
  public function sendPasswordResetEmail($toEmail, $fullName, $code)
  {
    $mail = new PHPMailer();
    //$mail->SMTPDebug = 3;                               // Enable verbose debug output outlook.smtp.com
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'mail.renttranzact.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'noreply@renttranzact.com';                 // SMTP username
    $mail->Password = '!sp?5WmVn)LC';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to
    //$mail->addBCC('');
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->setFrom('noreply@renttranzact.com', 'RentTranzact');
    $mail->addAddress($toEmail, $fullName);
    $mail->Subject = "[Password Reset] -  RentTranzact";
    $mail->Body = "Dear $fullName, <br> Your  registered  e-mail address
        on RentTranzact requested for your login details."
      . "<br>Use the code to reset your password : $code"
      . "<br>Expires in 15 minutes.<br><br>For any enquiries contact  Support Team at customersupport@renttranzact.com"
      . "<br><br>Regards,<br><br> RentTranzact Team.";
    $mail->AltBody = $mail->Body;
    //echo $mail->AltBody;
    if (!$mail->send()) {
      //this should be logged
      // echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
      log_message('error', 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
    } else {
      // echo 'Message has been sent';
      log_message('info', 'Message has been sent');
    }
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

  public function initiateNotification($type, $toUserId, $otherData)
  {
    $userData = $this->getUserById($toUserId);
    switch ($type) {
      case 'money_received':
        $this->moneyReceivedEmail(
          $userData['email_address'],
          $userData['first_name'] . ' ' . $userData['last_name'],
          $otherData['sender_name'],
          $otherData['amount']
        );
        break;
    }
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
    //$mail->SMTPDebug = 3;                               // Enable verbose debug output outlook.smtp.com
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'mail.renttranzact.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'noreply@renttranzact.com';                 // SMTP username
    $mail->Password = '!sp?5WmVn)LC';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to
    //$mail->addBCC('');
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->setFrom('noreply@renttranzact.com', 'RentTranzact');

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
}
