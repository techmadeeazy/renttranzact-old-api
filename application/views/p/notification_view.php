<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <section class="main-content container"> 
   <!-- Modal -->
   <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header"> 
        <h4 class="modal-title">Notification!</h4>
        </div>
		<div class="modal-body">
		<div class="alert alert-warning alert-dismissable">
		<?php  if (!empty ($email_response)) { ?>
		 <strong>Message: </strong> <?php echo $email_response; ?><br><br><span>
		 <?php } ?>
		 
		 <?php  if (!empty ($sms_response)) { ?>
		 <strong>Message: </strong> <?php echo $sms_response; ?><br><br><span>
		 <?php } ?>
		 
		 <p> Thank you. </p>
		</div>
	   </div>
      </div>
     </div>
  </div>
  

			