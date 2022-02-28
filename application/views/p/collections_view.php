<section class="main-content container">
    
    <!--page header end-->
	<div class="row">
		<div class="col-md-12">
			<?php echo $email;?>
			<table class="table table-bordered table-hover">
				<thead>
					<tr>
						<th>Loan ID</th>
						<th>Repayment Details</th>
						<th>Total</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($repayments as $r):
						
						$total_repayment = number_format($r['principalDue'] + $r['interestDue'] + $r['feesDue'] + $r['penaltyDue'], 2);
						$loan_id = $r["loan_id"];

						if($total_repayment > 0):
					?>
						<tr>
							<td><?php echo $loan_id;?></td>
							<td>
								<ul>
								    <li><strong>principalDue</strong> : N <?php echo number_format($r["principalDue"], 2);?></li>
								    <li><strong>interestDue</strong> : N <?php echo number_format($r["interestDue"], 2);?></li>
								    <li><strong>feesDue</strong> : N <?php echo number_format($r["feesDue"], 2);?></li>
								    <li><strong>penaltyDue</strong> : N <?php echo number_format($r["penaltyDue"], 2);?></li>
								</ul>
							</td>
							<td>N <?php echo $total_repayment;?></td>
							<td>
								<form class="form-horizontal" method="post" action="<?php echo base_url();?>/collections/sendlink">
									<input type="text" value="<?php echo $total_repayment; ?>" name="amount"   />
									<input type="hidden" value="<?php echo $loan_id; ?>" name="loan_id" size="50"   />
									<input type="hidden" value="<?php echo $email; ?>" name="email" size="50"   />
									<input type="submit" value="Send Link" name="btnSendLink" class="btn btn-success" />
								</form>
							</td>
						</tr>

					<?php
						endif;
					endforeach ?>
					
				</tbody>
			</table>

		</div>
	</div>
</section>