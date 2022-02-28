<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!--main content start-->
<style type="text/css">
    .spinner {
        display: inline-block;
        opacity: 0;
        width: 0;

        -webkit-transition: opacity 0.25s, width 0.25s;
        -moz-transition: opacity 0.25s, width 0.25s;
        -o-transition: opacity 0.25s, width 0.25s;
        transition: opacity 0.25s, width 0.25s;
    }

    .has-spinner.active {
        cursor:progress;
    }

    .has-spinner.active .spinner {
        opacity: 1;
        width: auto; /* This doesn't work, just fix for unkown width elements */
    }

    .has-spinner.btn-mini.active .spinner {
        width: 10px;
    }

    .has-spinner.btn-small.active .spinner {
        width: 13px;
    }

    .has-spinner.btn.active .spinner {
        width: 16px;
    }

    .has-spinner.btn-large.active .spinner {
        width: 19px;
    }




</style>
<section class="main-content container">

    <!--page header end-->
    <div class="row">
        <div class="col-sm-6">

            <!-- START panel-->
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>
                <div class="panel-body">
                    <p><?php echo isset($err_msg) ? $err_msg : '' ?></p>
                    <form class="form-horizontal" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Username/ID</label>
                            <div class="col-lg-10">
                                <input type="text"  placeholder="Member ID" name="username" class="form-control" value="" size="25">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Password </label>
                            <div class="col-lg-10">
                                <input type="text"  placeholder="Password" name="pwd" class="form-control" value="" size="25">
                            </div>
                        </div>

                        <div class="col-sm-6 text-center" align="center"> 
                            <input type="submit" value="Login" name="btnLogin" /> 
                        </div>

                    </form>
                </div>
            </div>
            <!-- END panel-->
        </div>