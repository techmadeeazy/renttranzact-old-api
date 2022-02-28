<?php defined('BASEPATH') OR exit('No direct script access allowed');  ?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Pay via Paystack</title>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
	<!-- !!! use this URL for production:
		<script src="https://cdn.rawgit.com/unconditional/jquery-table2excel/master/src/jquery.table2excel.js"></script>
		-->
		
		
		
	<!-- 	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.jss"></script> -->
	
    </head>
    <body>
	 <!--top bar start-->
        <div class="top-bar light-top-bar"><!--by default top bar is dark, add .light-top-bar class to make it light-->
            <div class="container-fluid">
                <div class="row">
				
                    <div class="col-xs-6">
			<!-- login user session -->
				<?php// if (isset($_SESSION['username']) && $_SESSION['logged_in'] === true) : ?>
                        <a href="#" class="admin-logo">
						
				<?php// endif; ?>
                          
                        </a>
				
                        <div class="left-nav-toggle visible-xs visible-sm">
                            <a href="#">
                                <i class="glyphicon glyphicon-menu-hamburger"></i>
                            </a>
                        </div><!--end nav toggle icon-->
                        <!--start search form-->
                       <!-- <div class="search-form hidden-xs">
                            <form>
                                <input type="text" class="form-control" placeholder="Search for...">
                                <button type="button" class="btn-search"><i class="fa fa-search"></i></button>
                            </form>
                        </div>-->
                        <!--end search form-->
                    </div>
					
				
                    <div class="col-xs-6">
                        <ul class="list-inline top-right-nav">
                     
							
						 
							
                           
							 </li> 
 </li> 
                        </ul> 
                    </div>
                </div>
            </div>
        </div>
        <!-- top bar end-->