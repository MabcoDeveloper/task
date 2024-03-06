<?php
header("Content-Type: text/html; charset=UTF-8");
header("Content-Security-Policy: frame-ancestors ".$cfg->getAllowIframes().";");

$title = ($ost && ($title=$ost->getPageTitle()))
    ? $title : ('osTicket :: '.__('Staff Control Panel'));

if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html<?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo ' dir="rtl" class="rtl"';
if ($lang) {
    echo ' lang="' . Internationalization::rfc1766($lang) . '"';
}
?>>
<style>

#msg_warningForm {
  margin: 0;
  padding: 5px 10px 5px 36px;
  /* height: 20px;
  line-height: 20px; */
  margin-bottom: 10px;
  border: 1px solid #f26522;
  background: url('scp/images/icons/alert.png') 10px 50% no-repeat #ffffdd;
}
</style>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="x-pjax-version" content="<?php echo GIT_VERSION; ?>">
    <title><?php echo Format::htmlchars($title); ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->

    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-3.4.0.min.js?a5d898b"></script>
    <script src="https://www.gstatic.com/firebasejs/7.6.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.6.2/firebase-analytics.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.6.2/firebase-messaging.js"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/firebase-app.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script> -->
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/thread.css?a5d898b" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/scp.css?a5d898b" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?a5d898b" media="screen"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/typeahead.css?a5d898b" media="screen"/>
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?a5d898b"
         rel="stylesheet" media="screen" />
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/jquery-ui-timepicker-addon.css?a5d898b" media="all"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?a5d898b"/>
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome-ie7.min.css?a5d898b"/>
    <![endif]-->
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/dropdown.css?a5d898b"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/loadingbar.css?a5d898b"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?a5d898b"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/select2.min.css?a5d898b"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?a5d898b"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH ?>scp/css/translatable.css?a5d898b"/>
    <!-- Favicons -->
    <link rel="icon" type="image/png" href="<?php echo ROOT_PATH ?>images/favicon.gif" sizes="32x32" />
    <?php
        if($ost && ($headers=$ost->getExtraHeaders())) {
            echo "\n\t".implode("\n\t", $headers)."\n";
        }
        $GetGeneralForms="SELECT `content` , `duration` , `created_at` FROM `ost_general_informs` WHERE date(`created_at`)=DATE(NOW()) AND  `Flag_agent`=1";
        // echo $GetGeneralForms;
        if (($GetGeneralForms_Res = db_query($GetGeneralForms)) && db_num_rows($GetGeneralForms_Res)) {
        while (list($CC1_,$Du_,$Ca_) = db_fetch_row($GetGeneralForms_Res)) {
            $CC1= $CC1_;  
            $Du =  $Du_;
            $Ca =  $Ca_;
            }   
        }
        // echo $CC1;
        // $CreatedDate = strtotime($Ca->format('Y-m-d H:i:s'));
        $new_time =  date('Y-m-d H:i:s',strtotime('+'.$Du.' hour ',strtotime($Ca)));
        $NowDateD = new DateTime("Asia/Damascus");
        $NowDate = date('Y-m-d H:i:s',strtotime('+2 hour ',strtotime(date('Y-m-d H:i:s'))));
        $DateDiff = $NowDate - $new_time;
        // echo $NowDate ;
        // echo "<br>";
        // echo $new_time;
        if($NowDate  <  $new_time ){

        
    ?>
    <div id="msg_warning"><?php echo  substr($CC1, 0, 155); ?></div>
    <!-- <script>
   setTimeout(function() {
        $('#msg_warning').fadeOut('fast');
    }, $Du*3600000); // <-- time in milliseconds
   
 </script> -->
 <?php }?>
</head>
<body> 
<?php $notifi_check=false;?>
<div id="container">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div id="header">
        <p id="info" class="pull-right no-pjax"><?php echo sprintf(__('Welcome, %s.'), '<strong>'.$thisstaff->getFirstName().'</strong>'); ?>
           <?php
            if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="<?php echo ROOT_PATH ?>scp/admin.php" class="no-pjax"><?php echo __('Admin Panel'); ?></a>
            <?php }else{ ?>
            | <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax"><?php echo __('Agent Panel'); ?></a>
            <?php } ?>
            | <a href="<?php echo ROOT_PATH ?>scp/profile.php"><?php echo __('Profile'); ?></a>
            | <a href="<?php echo ROOT_PATH ?>scp/logout.php?auth=<?php echo $ost->getLinkToken(); ?>" class="no-pjax"><?php echo __('Log Out'); ?></a>
            | <a id="allow_notifications_but" href="javascript:void(0)"><?php echo __('Allow Notifications'); ?></a>
            <?php 
             $z = array();
             $GetAllStaff = "SELECT `staff_id` FROM `ost_staff` WHERE `Flag_general_forms`=1";
             if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                 while (list($RecurringTaskID) = db_fetch_row($GetRecurringTasks_Res)) {
                     array_push($z, $RecurringTaskID);
                     
                 }}
            
            
            
            if(in_array($thisstaff->getId(), $z)){
                ?> | <a  href="?cgf=cgf"><?php echo __('Create general Informs'); ?></a><?php
                }?>
        
        </p>
        <a href="<?php echo ROOT_PATH ?>scp/index.php" class="no-pjax" id="logo">
            <span class="valign-helper"></span>
            <img src="<?php echo ROOT_PATH ?>scp/logo.php?<?php echo strtotime($cfg->lastModified('staff_logo_id')); ?>" alt="osTicket &mdash; <?php echo __('Customer Support System'); ?>"/>
        </a>
    </div>
    <div id="pjax-container" class="<?php if ($_POST) echo 'no-pjax'; ?>">
<?php } else {
    header('X-PJAX-Version: ' . GIT_VERSION);
    if ($pjax = $ost->getExtraPjax()) { ?>
    <script type="text/javascript">
    <?php foreach (array_filter($pjax) as $s) echo $s.";"; ?>
    </script>
    <?php }
    foreach ($ost->getExtraHeaders() as $h) {
        if (strpos($h, '<script ') !== false)
            echo $h;
    } ?>
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'osTicket :: '.__('Staff Control Panel'); ?></title><?php
} # endif X_PJAX ?>
    <ul id="nav">
<?php include STAFFINC_DIR . "templates/navigation.tmpl.php"; ?>
    </ul>
    <?php include STAFFINC_DIR . "templates/sub-navigation.tmpl.php"; ?>

        <div id="content">
        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php }
        foreach (Messages::getMessages() as $M) { ?>
            <div class="<?php echo strtolower($M->getLevel()); ?>-banner"><?php
                echo (string) $M; ?></div>
<?php   }

echo "<script> 
    $(function(){
        $('#myForm').submit(function() {
          $('#loader').show(); 
          return true;
        });
      });
       </script>";
if(isset($_POST["Submit1"])){
    
   }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $Agents=0;
    $Users=0;
    $name=null;
    $website=null;
    $notifi=0; 
        if (empty($_POST["website"])) {
            $websiteErr = "Duration is required";
            
        } else {
            $website = $_POST["website"];
        }
        if (empty($_POST["Agents"]) && empty($_POST["Users"])) {
                $genderErr = "Agents or Users  is required";
                
            } 
              if (!empty($_POST["Agents"])) {
                $Agents = 1;
              }
              if (!empty($_POST["Users"])) {
                $Users = 1;
              } 
            if (empty($_POST["name"])) {
                $nameErr = "Name is required";
            } 
            else {
                
                $name = $_POST["name"];}
            if($name != null && $website != null && ($Agents != 0 || $Users!=0)){

                
                $sql='INSERT INTO `ost_general_informs`( `content`, `duration`, `staff_id`, `created_at`, `updated_at`,`Flag_user`,`Flag_agent`) VALUES ("'.$name.'",'.$website.','.$thisstaff->getId().',Now(),Now(),'.$Users.','.$Agents.')';
                //echo $sql; 
                
                if(db_query($sql)){
                    unset($_GET["cgf"]);
                    echo '<script>modal.style.display = "none";</script>';
    

                }
                if (empty($_POST["Notification"])) {
                    $notifi = 0;
                } 
                else {
                    $notifi = 1;
                    if ($Agents == 1  ) {
                        $GetTeamMembersQ = "SELECT `staff_id` ,`fcm_token` FROM `ost_staff` ";
                        // echo '<script>modal.style.display = "none";</script>';
                        if (($GetTeamMembers_Res = db_query($GetTeamMembersQ)) && db_num_rows($GetTeamMembers_Res)) {
                            while (list($StaffID,$StaffToken) = db_fetch_row($GetTeamMembers_Res)) {
                                Ticket::SendPushNotificationGen( $name,0,$StaffID); 
                            }
                        }
                    
                    }
                    if($Users == 1 ){
                        $GetTeamMembersQ = "SELECT `id` FROM `ost_user`";
                                
                        if (($GetTeamMembers_Res = db_query($GetTeamMembersQ)) && db_num_rows($GetTeamMembers_Res)) {
                            while (list($StaffID) = db_fetch_row($GetTeamMembers_Res)) {
                                Ticket::SendPushNotificationGen( $name,$StaffID,0); 
                            }
                        }
                    }   
                }  
                
            
        }
            }
            
             
if(isset($_GET["cgf"] ) ){
    ?>
            <style>
                body {
                    font-family: Arial, Helvetica, sans-serif;
                }

                /* The Modal (background) */
                .modal {
                    display: none;
                    /* Hidden by default */
                    position:absolute;
                    overflow:scroll;
                    /* Stay in place */
                    z-index: 1;
                    /* Sit on top */
                    
                    /* Location of the box */
                    left: 0;
                    top: 0;
                    width: 100%;
                    /* Full width */
                    height: 300%;
                    /* Full height */
                    /* Enable scroll if needed */
                    background-color: rgb(0, 0, 0);
                    /* Fallback color */
                    background-color: rgba(0, 0, 0, 0.4);
                    /* Black w/ opacity */
                    white-space: normal;
                }

                /* Modal Content */
                .modal-content {
                    position: relative;
                    background-color: #fefefe;
                    margin: auto;
                    padding: 0;
                    border: 1px solid #888;
                    width: 50%;
                    height: 50%;
                    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                    -webkit-animation-name: animatetop;
                    -webkit-animation-duration: 0.4s;
                    animation-name: animatetop;
                    animation-duration: 0.4s
                }

                /* Add Animation */
                @-webkit-keyframes animatetop {
                    from {
                        top: -300px;
                        opacity: 0
                    }

                    to {
                        top: 0;
                        opacity: 1
                    }
                }

                @keyframes animatetop {
                    from {
                        top: -300px;
                        opacity: 0
                    }

                    to {
                        top: 0;
                        opacity: 1
                    }
                }

                /* The Close Button */
                .close {
                    color: white;
                    float: right;
                    font-size: 28px;
                    font-weight: bold;
                }

                .close:hover,
                .close:focus {
                    color: #000;
                    text-decoration: none;
                    cursor: pointer;
                }

                .modal-header {
                    padding: 2px 16px;
                    background-color: #0492D0;
                    color: white;



                }

                .h2class {
                    color: #fefefe;
                    padding-top: 20px;

                }

                .modal-body {
                    padding: 2px 16px;
                    width: 500px;
                }

                .dropbtn {
                    background-color: #3498DB;
                    color: white;
                    padding: 16px;
                    font-size: 16px;
                    border: none;
                    cursor: pointer;
                }

                .dropbtn:hover,
                .dropbtn:focus {
                    background-color: #2980B9;
                }

                .dropdown {
                    position: relative;
                    display: inline-block;
                }

                .dropdown-content {
                    display: none;
                    position: absolute;
                    background-color: #f1f1f1;
                    min-width: 160px;
                    overflow: auto;
                    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                    z-index: 1;
                }

                .dropdown-content a {
                    color: black;
                    padding: 12px 16px;
                    text-decoration: none;
                    display: block;
                }

                .dropdown a:hover {
                    background-color: #ddd;
                }

                .show {
                    display: block;
                }
                .error {color: #FF0000;}
               
                #loader { 
            border: 12px solid #f3f3f3; 
            border-radius: 50%; 
            border-top: 12px solid #444444; 
            width: 70px; 
            height: 70px; 
            animation: spin 1s linear infinite; 
            display:none
            
        } 
          
        @keyframes spin { 
            100% { 
                transform: rotate(360deg); 
            } 
        } 
          
        .center { 
            position: absolute; 
            top: 0; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            margin: auto; 
        }
            </style>
            </head>

            <body>
                <!-- The Modal -->
                <div id="myModal" class="modal">
                
                    <!-- Modal content -->
                    <div class="modal-content">
                    
                        <div class="modal-header">
                            <span class="close">&times;</span>
                            <h2 class="h2class">Create General Informs</h2>
                        </div>
                        <div class="modal-body">
                        
                    <p><span class="error">* required field</span></p>
                            <form method="post" action="" id="myForm">  
                            <?php csrf_token(); ?>
                            
                            Content: 
                            <textarea name="name" id="task-response" cols="50"
                        data-signature-field="signature"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __( 'Start writing your General Informs.'); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    
     ?>></textarea>
                            
                            
                            
                            <span class="error">* <?php echo $nameErr;?></span>
                            <div id="loader" class="center"></div> 
                            <br><br>
                            Duration IN Hour: <input type="text" name="website" value="<?php echo $website;?>">
                            <span class="error">* <?php echo $websiteErr;?></span>
                            <br><br>
                            This General Form Is For :
                            <input type="checkbox" name="Agents" <?php if ($Agents==1 && $Agents=="Agents") echo "checked";?> value="Agents">Agents
                            <input type="checkbox" name="Users" <?php if ($Users==1 && $Users=="Users") echo "checked";?> value="Users">Users  
                            <span class="error">* <?php echo $genderErr;?></span>
                           
                            <br><br>
                            This General Form With Notification Take 3 - 5 Minutes :
                                <br><br>
                            <input type="checkbox" name="Notification" <?php if ($notifi==1) echo "checked";?> value="Notification"  >Check to send with notification
                            <br><br>
                            <input type="submit" name="Submit1" value="Submit" id="btn-submit"   onclick="return confirm('Are you sure? Please Review your General Informs ')">  
                            </form>
                            <!-- <button type="button" id="btnclick" onclick="my_button_click_handler()">Choose !!</button> -->
                        </div>

                        <script>
                            /* When the user clicks on the button, 
                            toggle between hiding and showing the dropdown content */
                            function myFunction() {
                                document.getElementById("myDropdown").classList.toggle("show");
                            }

                            // Close the dropdown if the user clicks outside of it
                            window.onclick = function(event) {
                                if (!event.target.matches('.dropbtn')) {
                                    var dropdowns = document.getElementsByClassName("dropdown-content");
                                    var i;
                                    for (i = 0; i < dropdowns.length; i++) {
                                        var openDropdown = dropdowns[i];
                                        if (openDropdown.classList.contains('show')) {
                                            openDropdown.classList.remove('show');
                                        }
                                    }
                                }
                            }
                        </script>

                    </div>

                </div>

                </div>

                <script>
                    // Get the modal
                    var modal = document.getElementById("myModal");

                    // Get the button that opens the modal
                    // var btn = document.getElementById("myBtn");

                    // Get the <span> element that closes the modal
                    var span = document.getElementsByClassName("close")[0];
                    var btnclick = document.getElementById("btnclick");
                    // When the user clicks the button, open the modal 

                    modal.style.display = "block";


                    // When the user clicks on <span> (x), close the modal
                    span.onclick = function() {
                        modal.style.display = "none";
                    }
                    // When the user clicks anywhere outside of the modal, close it
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = "none";
                        }
                    }
                </script>

                
        <?php


            // echo "<script>console.log('Debug Objects: " . $name . "' );</script>";
            // echo "<script>console.log('Debug Objects: " . $website . "' );</script>";
}
?>
<script>if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>

