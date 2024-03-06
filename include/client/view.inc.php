<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
/* The popup form - hidden by default */
.form-popup {
  display: none;
  position: fixed;

  border: 3px solid #f1f1f1;
  z-index: 1;
  /* Sit on top */
  padding-top: 100px;
   /* Location of the box */
   left: 100;
   top: 0;
    width: 100%;
    /* Full width */
    height: 100%;
    /* Full height */
    overflow: auto;
}

/* Add styles to the form container */
.form-container {
  max-width: 300px;
  padding: 10px;
  background-color: white;
}

/* Full-width input fields */
.form-container input[type=text], .form-container input[type=password] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  border: none;
  background: #f1f1f1;
}

/* When the inputs get focus, do something */
.form-container input[type=text]:focus, .form-container input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

/* Set a style for the submit/login button */
.form-container .btn {
  background-color: #6495ED;
  color: white;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  width: 85%;
  margin-bottom:10px;
  opacity: 0.8;
}

/* Add a red background color to the cancel button */
.form-container .cancel {
  background-color: red;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  width: 85%;
  margin-bottom:10px;
  opacity: 0.8;
}

/* Add some hover effects to buttons */
.form-container .btn:hover, .open-button:hover {
  opacity: 1;
}
</style>
<?php
include_once(INCLUDE_DIR.'class.email.php');
include_once(INCLUDE_DIR.'class.csrf.php');
if(!defined('OSTCLIENTINC') || !$thisclient || !$ticket || !$ticket->checkUserAccess($thisclient)) die('Access Denied!');

$info=($_POST && $errors)?Format::htmlchars($_POST):array();

$dept = $ticket->getDept();

if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(__('%s is marked as closed and cannot be reopened.'), __('This ticket'));

//Making sure we don't leak out internal dept names
if(!$dept || !$dept->isPublic())
    $dept = $cfg->getDefaultDept();

if ($thisclient && $thisclient->isGuest()
    && $cfg->isClientRegistrationEnabled()) { ?>

<div id="msg_info">
    <i class="icon-compass icon-2x pull-left"></i>
    <strong><?php echo __('Looking for your other tickets?'); ?></strong><br />
    <a href="<?php echo ROOT_PATH; ?>login.php?e=<?php
        echo urlencode($thisclient->getEmail());
    ?>" style="text-decoration:underline"><?php echo __('Sign In'); ?></a>
    <?php echo sprintf(__('or %s register for an account %s for the best experience on our help desk.'),
        '<a href="account.php?do=create" style="text-decoration:underline">','</a>'); ?>
    </div>

<?php } 
  if (isset($_POST["str1"])) {
    if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "restaurant activation"){
        $type='R';
        }
        if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "driver activation"){
            $type='D';
            }
    if($_SESSION['External_s'] == 6 && trim(explode("/", $ticket->getHelpTopic())[1]," ") == "restaurant activation"  ){
        $x=array();
        $y=array();
        $sql_all="SELECT `body`, `ost_ticket`.`number` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` inner join `ost_ticket` on `ost_ticket`.`ticket_id`=`ost_thread`.`object_id` WHERE `ost_thread`.`object_id`= ".$ticket->getId()." AND `ost_thread`.`object_type`='T'";
        if (($sql_all_Res = db_query($sql_all)) && db_num_rows($sql_all_Res)) {
            while (list($body, $numb) = db_fetch_row($sql_all_Res)) {
                
                array_push($x, $body);
                array_push($y, $numb);
            }
        }
    $sql_get_last_thread="SELECT `body`  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`created`=(SELECT MAX(`ost_thread_entry`.`created`)  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T')";
    if (($sql_get_last_thread_Res = db_query($sql_get_last_thread)) && db_num_rows($sql_get_last_thread_Res)) {

        $thread = db_fetch_row($sql_get_last_thread_Res);
    }
    // echo $thread[0];
    $errors=array();
    $email=null;
    $email=Email::lookup(7);
    if(!$errors && $email){
        if($email->send("onboarding@beeorder.com","Lost , Ticket Number is ".$y[0],
        json_encode($x),
                null, array('reply-tag'=>false),'samer.zarzar@mabco.biz')) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
            "onboarding@beeorder.com"));
            Draft::deleteForNamespace('email.diag');

            
        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
elseif($_SESSION['External_s'] == 5 && trim(explode("/", $ticket->getHelpTopic())[1]," ") == "restaurant activation" ){
    $x=array();
    $y=array();
    $sql_all="SELECT `body`, `ost_ticket`.`number` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` inner join `ost_ticket` on `ost_ticket`.`ticket_id`=`ost_thread`.`object_id` WHERE `ost_thread`.`object_id`= ".$ticket->getId()." AND `ost_thread`.`object_type`='T'";
    if (($sql_all_Res = db_query($sql_all)) && db_num_rows($sql_all_Res)) {
        while (list($body, $numb) = db_fetch_row($sql_all_Res)) {
            
            array_push($x, $body);
            array_push($y, $numb);
        }
    }
    $sql_get_last_thread="SELECT `body`  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`created`=(SELECT MAX(`ost_thread_entry`.`created`)  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T')";
    if (($sql_get_last_thread_Res = db_query($sql_get_last_thread)) && db_num_rows($sql_get_last_thread_Res)) {

        $thread = db_fetch_row($sql_get_last_thread_Res);
    }
    // echo $thread[0];
    $errors=array();
    $email=null;
    $email=Email::lookup(7);
    $thread_attachments = array();
    foreach (Attachment::objects()->filter(array(
        'thread_entry__thread__id' => $ticket->getThreadId(),
    )) as $att) {
        $thread_attachments[] = $att;
    }
    if(!$errors && $email){
        if($email->send("contracts@beeorder.com","Won , Ticket Number is ".$y[0],
        json_encode($x),
                $thread_attachments, array('reply-tag'=>false),'samer.zarzar@mabco.biz')) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
            "contracts@beeorder.com"));
            Draft::deleteForNamespace('email.diag');

            

        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
elseif($_SESSION['External_s'] == 6 && trim(explode("/", $ticket->getHelpTopic())[1]," ") == "driver activation" ){
    $x=array();
    $y=array();
    $sql_all="SELECT `body`, `ost_ticket`.`number` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` inner join `ost_ticket` on `ost_ticket`.`ticket_id`=`ost_thread`.`object_id` WHERE `ost_thread`.`object_id`= ".$ticket->getId()." AND `ost_thread`.`object_type`='T'";
    if (($sql_all_Res = db_query($sql_all)) && db_num_rows($sql_all_Res)) {
        while (list($body, $numb) = db_fetch_row($sql_all_Res)) {
            
            array_push($x, $body);
            array_push($y, $numb);
        }
    }

    $sql_get_last_thread="SELECT `body`  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`created`=(SELECT MAX(`ost_thread_entry`.`created`)  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T')";
    if (($sql_get_last_thread_Res = db_query($sql_get_last_thread)) && db_num_rows($sql_get_last_thread_Res)) {

        $thread = db_fetch_row($sql_get_last_thread_Res);
    }
    // echo $thread[0];
    $errors=array();
    $email=null;
    $email=Email::lookup(7);
    if(!$errors && $email){
        if($email->send("delivery@beeorder.com","Lost , Ticket Number is ".$y[0],
        json_encode($x),
        null, array('reply-tag'=>false),'samer.zarzar@mabco.biz')) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
            "delivery@beeorder.com"));
            Draft::deleteForNamespace('email.diag');

           
        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
elseif($_SESSION['External_s'] == 5 && trim(explode("/", $ticket->getHelpTopic())[1]," ") == "driver activation"){
    $x=array();
    $y=array();
    $sql_all="SELECT `body`, `ost_ticket`.`number` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` inner join `ost_ticket` on `ost_ticket`.`ticket_id`=`ost_thread`.`object_id` WHERE `ost_thread`.`object_id`= ".$ticket->getId()." AND `ost_thread`.`object_type`='T'";
    if (($sql_all_Res = db_query($sql_all)) && db_num_rows($sql_all_Res)) {
        while (list($body, $numb) = db_fetch_row($sql_all_Res)) {
            
            array_push($x, $body);
            array_push($y, $numb);
        }
    }
    $sql_get_last_thread="SELECT `body`  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`created`=(SELECT MAX(`ost_thread_entry`.`created`)  FROM `ost_thread_entry` 
    INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
    WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T')";
    if (($sql_get_last_thread_Res = db_query($sql_get_last_thread)) && db_num_rows($sql_get_last_thread_Res)) {

        $thread = db_fetch_row($sql_get_last_thread_Res);
    }
    // echo $thread[0];
    $errors=array();
    $email=null;
    $email=Email::lookup(7);
    $thread_attachments = array();
    foreach (Attachment::objects()->filter(array(
        'thread_entry__thread__id' => $ticket->getThreadId(),
    )) as $att) {
        $thread_attachments[] = $att;
    }
    if(!$errors && $email){
        if($email->send("delivery@beeorder.com","Won , Ticket Number is ".$y[0],
        json_encode($x),
                $thread_attachments, array('reply-tag'=>false),'samer.zarzar@mabco.biz')) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
            "delivery@beeorder.com"));
            Draft::deleteForNamespace('email.diag');

            
        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
    $sqlClose="UPDATE `ost_ticket` SET `closed`=Now(),`status_id`=3  WHERE `ticket_id`=".$ticket->getId();
            db_query($sqlClose);

            $sqll="SELECT `status_id`  FROM `ost_connect_status` WHERE `ticket_id` =".$ticket->getId();
    if (($sql_Res = db_query($sqll)) && db_num_rows($sql_Res)) {
    
        $St = db_fetch_row($sql_Res);
    }
   
        $sql_in="INSERT INTO `ost_connect_status`( `ticket_id`, `status_id`,`user_id`) VALUES (".$ticket->getId().",".$_SESSION['External_s']." , ". $thisclient->getId().")";
        if(db_query($sql_in)){
            echo "<script>
                                             
                                         if ( window.history.replaceState ) {
                                            window.history.replaceState( null, null, window.location.href );
                                            
                                          }
                                          window.location.reload();
                                             </script>";
        }
  
}
if(isset($_POST['External_s'])){


    $_SESSION['External_s']=$_POST['External_s'];
    if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "restaurant activation"){
        $type='R';
        }
        if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "driver activation"){
            $type='D';
            }
    if($_POST['External_s'] == 7 || $_POST['External_s'] == 8 ){
        $sql="UPDATE `ost_ticket` SET `current_step`= 1  WHERE `ticket_id`=".$ticket->getId();
        if(db_query($sql)){
            echo "<script>
                                         
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
        }
    }
    elseif($_POST['External_s'] == 5 && $type='R'  ){
        ?>
        <div class="form-popup" id="myForm" style="display :block ">
        <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
        <?php csrf_token(); ?>
          <label ><b>This status will closed the ticket are you sure you want to continue?</b></label>
          <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
          <input type="submit" class="btn" name="str1" value="Yes" />
          <button type="button" class=" cancel" onclick="closeForm()">Cancel</button>
        </form>
      </div>
      
      <script>
      function openForm() {
      //   document.getElementById("myForm").style.display = "none";
      <?php
      
      
      ?>
      }
      
      function closeForm() {
        document.getElementById("myForm").style.display = "none";
        if ( window.history.replaceState ) {
                                            window.history.replaceState( null, null, window.location.href );
                                            
                                          }
                                          window.location.reload();
      }
      </script>
      <?php
    }
    elseif($_POST['External_s'] == 5 && $type='D'){
        ?>
        <div class="form-popup" id="myForm" style="display :block ">
        <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
        <?php csrf_token(); ?>
          <label ><b>This status will closed the ticket are you sure you want to continue?</b></label>
          <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
          <input type="submit" class="btn" name="str1" value="Yes" />
          <button type="button" class=" cancel" onclick="closeForm()">Cancel</button>
        </form>
      </div>
      
      <script>
      function openForm() {
      //   document.getElementById("myForm").style.display = "none";
      <?php
      
      
      ?>
      }
      
      function closeForm() {
        document.getElementById("myForm").style.display = "none";
        if ( window.history.replaceState ) {
                                            window.history.replaceState( null, null, window.location.href );
                                            
                                          }
                                          window.location.reload();
      }
      </script>
      <?php
}

    elseif($_POST['External_s'] == 6 && $type='R'  ){
        ?>
        <div class="form-popup" id="myForm" style="display :block ">
        <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
        <?php csrf_token(); ?>
          <label ><b>This status will closed the ticket are you sure you want to continue?</b></label>
          <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
          <input type="submit" class="btn" name="str1" value="Yes" />
          <button type="button" class="cancel" onclick="closeForm()">Cancel</button>
        </form>
      </div>
      
      <script>
      function openForm() {
      //   document.getElementById("myForm").style.display = "none";
      <?php
      
      
      ?>
      }
      
      function closeForm() {
        document.getElementById("myForm").style.display = "none";
        if ( window.history.replaceState ) {
                                            window.history.replaceState( null, null, window.location.href );
                                            
                                          }
                                          window.location.reload();
      }
      </script>
      <?php
    }

    elseif($_POST['External_s'] == 6 && $type='D'  ){
        ?>
        <div class="form-popup" id="myForm" style="display :block ">
        <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
        <?php csrf_token(); ?>
          <label ><b>This status will closed the ticket are you sure you want to continue?</b></label>
          <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
          <input type="submit" class="btn" name="str1" value="Yes" />
          <button type="button" class=" cancel" onclick="closeForm()">Cancel</button>
        </form>
      </div>
      
      <script>
      function openForm() {
      //   document.getElementById("myForm").style.display = "none";
      <?php
      
      
      ?>
      }
      
      function closeForm() {
        document.getElementById("myForm").style.display = "none";
        if ( window.history.replaceState ) {
                                            window.history.replaceState( null, null, window.location.href );
                                            
                                          }
                                          window.location.reload();
      }
      </script>
      <?php
      
}
$sqll="SELECT `ost_external_status`.`status`  FROM `ost_connect_status`
INNER JOIN `ost_external_status` ON  `ost_external_status`.`id`=`ost_connect_status`.`status_id`
WHERE  `ticket_id`=".$ticket->getId()." order by `ost_connect_status`.`id` desc" ;
    if (($sql_Res = db_query($sqll)) && db_num_rows($sql_Res)) {
    
        $St = db_fetch_row($sql_Res);
    }
if($_POST['External_s'] != 5 && $_POST['External_s'] != 6){   

    $sql_in="INSERT INTO `ost_connect_status`( `ticket_id`, `status_id`,`user_id`) VALUES (".$ticket->getId().",".$_SESSION['External_s']." , ". $thisclient->getId().")";
    if(db_query($sql_in)){
        echo "<script>
                                         
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
    }

}
} 
?>
<table width="100%" cellpadding="1" cellspacing="0" border="0" id="ticketInfo">
    <tr>
        <td colspan="2" width="100%">
            <h1>
                <a href="tickets.php?id=<?php echo $ticket->getId(); ?>" title="<?php echo __('Reload'); ?>"><i class="refresh icon-refresh"></i></a>
                <b>
                <?php $subject_field = TicketForm::getInstance()->getField('subject');
                    echo $subject_field->display($ticket->getSubject()); ?>
                </b>
                <small>#<?php echo $ticket->getNumber(); ?></small>
<div class="pull-right">
      <a class="action-button" href="tickets.php?a=print&id=<?php
          echo $ticket->getId(); ?>"><i class="icon-print"></i> <?php echo __('Print'); ?></a>

<?php if ($ticket->hasClientEditableFields()
        // Only ticket owners can edit the ticket details (and other forms)
        && $thisclient->getId() == $ticket->getUserId()) { ?>
                <a class="action-button" href="tickets.php?a=edit&id=<?php
                     echo $ticket->getId(); ?>"><i class="icon-edit"></i> <?php echo __('Edit'); ?></a>
<?php } ?>
</div>
            </h1>
            <?php 
            $ID=array();
$sqql="SELECT `user_id`  FROM `ost_thread_collaborator` 
INNER JOIN `ost_thread` ON `ost_thread`.`id` = `ost_thread_collaborator`.`thread_id`
WHERE `ost_thread`.`object_type` LIKE 'T' AND `ost_thread`.`object_id`= ".$ticket->getId();
if (($sql_Res = db_query($sqql)) && db_num_rows($sql_Res)) {
    while (list($ID_) = db_fetch_row($sql_Res)) {
                    
        array_push($ID, $ID_);
    // $ID = db_fetch_row($sql_Res);
}
}

if(in_array($thisclient->getId(),$ID)){
$sql="UPDATE `ost_staff_calendar` SET  `Staff_id`=".$thisclient->getId()."  WHERE `id` = 2";
db_query($sql);
?>
            <button style="font-size:15px;float: right;"><a href="https://task.mabcoonline.com:444/calendar_0-user/?ticket_id=<?php echo $thisclient->getId() ; ?>&TID=<?php echo $ticket->getId() ; ?>"> View Calender  </a><i class="fa fa-calendar"></i></button>
            <!-- <button style="font-size:15px;float: right;"><a href="http://localhost/calendar_0-user/?ticket_id=<?php echo $thisclient->getId() ; ?>"> View Calender  </a><i class="fa fa-calendar"></i></button> -->
<?php }
$arr_users=array();
$sql_get_users="SELECT u.username from ost_help_topic_flow f inner join ost_user_account u on u.user_id =f.user_id  where ticket_id =".$ticket->getId();
if (($sql_Res = db_query($sql_get_users)) && db_num_rows($sql_Res)) {
   while (list($names) = db_fetch_row($sql_Res)) {
                   
       array_push($arr_users, $names);
   // $ID = db_fetch_row($sql_Res);
}
}
?>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <table class="infoTable" cellspacing="1" cellpadding="3" width="100%" border="0">
                <thead>
                    <tr><td class="headline" colspan="2">
                        <?php echo __('Basic Ticket Information'); ?>
                    </td></tr>
                </thead>
                <tr>
                    <th width="100"><?php echo __('Ticket Status');?>:</th>
                    <td><?php echo ($S = $ticket->getStatus()) ? $S->getLocalName() : ''; ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Department');?>:</th>
                    <td><?php echo Format::htmlchars($dept instanceof Dept ? $dept->getName() : ''); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Create Date');?>:</th>
                    <td><?php echo Format::datetime($ticket->getCreateDate()); ?></td>
                </tr>
                <?php if (isset($arr_users[0])){ ?>
                <tr>
                    <th><?php echo __('user 1 ');?>:</th>
                    <td><?php  echo $arr_users[0];?></td>
                </tr>
                <tr>
                    <th><?php echo __('user 2 ');?>:</th>
                    <td><?php  echo $arr_users[1];?></td>
                </tr>
                <?php 
                }?>
                <?php
                  $arr_s=array();
                  $sql_get_s="SELECT `ost_external_status`.`status`  FROM `ost_connect_status`
                 INNER JOIN `ost_external_status` ON  `ost_external_status`.`id`=`ost_connect_status`.`status_id`
                 WHERE  `ticket_id`=".$ticket->getId()." order by `ost_connect_status`.`id` desc" ;
                  if (($sql_Res = db_query($sql_get_s)) && db_num_rows($sql_Res)) {
                     while (list($ID_) = db_fetch_row($sql_Res)) {
                                     
                         array_push($arr_s, $ID_);
                     // $ID = db_fetch_row($sql_Res);
                 }
                 }
                 if($arr_s[0] !== null){ ?>
 
                <tr style="background: #FFD2E1;">
                     <th><?php echo __('external status');?>:</th>
                    
                         <td><b><label  id="contact-label" ><?php echo $arr_s[0]; ?></label></b></td>
                     
                     
                  </tr>
                  <?php } 
                  ?>
           </table>
       </td>
       <td width="50%">
           <table class="infoTable" cellspacing="1" cellpadding="3" width="100%" border="0">
                <thead>
                    <tr><td class="headline" colspan="2">
                        <?php echo __('User Information'); ?>
                    </td></tr>
                </thead>
               <tr>
                   <th width="100"><?php echo __('Name');?>:</th>
                   <td><?php echo mb_convert_case(Format::htmlchars($ticket->getName()), MB_CASE_TITLE); ?></td>
               </tr>
               <tr>
                   <th width="100"><?php echo __('Email');?>:</th>
                   <td><?php echo Format::htmlchars($ticket->getEmail()); ?></td>
               </tr>
               <tr>
                   <th><?php echo __('Phone');?>:</th>
                   <td><?php echo $ticket->getPhoneNumber(); ?></td>
               </tr>
            </table>
       </td>
    </tr>
    <tr>
        <td colspan="2">
<!-- Custom Data -->
<?php
$sections = $forms = array();
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $i=>$form) {
    // Skip core fields shown earlier in the ticket view
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority'),
        Q::not(array('field__flags__hasbit' => DynamicFormField::FLAG_CLIENT_VIEW)),
    )));
    // Skip display of forms without any answers
    foreach ($answers as $j=>$a) {
        if ($v = $a->display())
            $sections[$i][$j] = array($v, $a);
    }
    // Set form titles
    $forms[$i] = $form->getTitle();
}
foreach ($sections as $i=>$answers) {
    ?>
        <table class="custom-data" cellspacing="0" cellpadding="4" width="100%" border="0">
        <tr><td colspan="2" class="headline flush-left"><?php echo $forms[$i]; ?></th></tr>
<?php foreach ($answers as $A) {
    list($v, $a) = $A; ?>
        <tr>
            <th><?php
echo $a->getField()->get('label');
            ?>:</th>
            <td><?php
echo $v;
            ?></td>
        </tr>
<?php } ?>
        </table>
    <?php
} ?>
    </td>
</tr>
</table>
<br>
  <?php
    $email = $thisclient->getUserName();
    $clientId = TicketUser::lookupByEmail($email)->getId();

    $ticket->getThread()->render(array('M', 'R', 'user_id' => $clientId), array(
                    'mode' => Thread::MODE_CLIENT,
                    'html-id' => 'ticketThread')
                );
  ?>

<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php }



if (!$ticket->isClosed() /* || $ticket->isReopenable() */ ) { 
    $ID=array();
$sql_get_hp="SELECT `topic` FROM `ost_help_topic` WHERE `dept_id` = 26";
if (($sql_Res = db_query($sql_get_hp)) && db_num_rows($sql_Res)) {
    while (list($ID_) = db_fetch_row($sql_Res)) {
                    
        array_push($ID, $ID_);
    // $ID = db_fetch_row($sql_Res);
}
}
if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "restaurant activation"){
$type='R';
}
if( trim(explode("/", $ticket->getHelpTopic())[1]," ") == "driver activation"){
    $type='D';
    }
if(in_array(trim(explode("/", $ticket->getHelpTopic())[1]," "),$ID) || in_array(trim(explode("/", $ticket->getHelpTopic())[0]," "),$ID)){
$S=array();
$SId=array();
$sql_get_S="SELECT `id`,`status`  FROM `ost_external_status` WHERE  `user` = 'U' AND  `type`='R'";
if (($sql_Res = db_query($sql_get_S)) && db_num_rows($sql_Res)) {
    while (list($ID_,$St) = db_fetch_row($sql_Res)) {
                    
        array_push($SId, $ID_);
        array_push($S, $St);
    // $ID = db_fetch_row($sql_Res);
}
}
if($type == 'D'){
    $sql_get_S="SELECT `id`,`status`  FROM `ost_external_status` WHERE  `user` = 'U' AND  `type`='D' ";
    if (($sql_Res = db_query($sql_get_S)) && db_num_rows($sql_Res)) {
        while (list($ID_,$St) = db_fetch_row($sql_Res)) {
                        
            array_push($SId, $ID_);
            array_push($S, $St);
        // $ID = db_fetch_row($sql_Res);
    }
    }
}
?>
        <label><b><?php echo __('External Status');?>:</b></label>
        <form action="#" method="post">
<?php csrf_token(); ?>
        <select name="External_s">
        <option disabled selected value> -- select a status -- </option>  
            <?php foreach ($S as $index => $item) {


?>
    <option value="<?php echo $SId[$index]; ?>"><?php echo $item; ?></option>
<?php } ?> 
</select>
<input style=" width: 3em;  height: 1.5em;" type="submit" name="submitExternal" value="Go"/>
        </form>
<?php } ?>
<form id="reply" action="tickets.php?id=<?php echo $ticket->getId();
?>#reply" name="reply" method="post" enctype="multipart/form-data">
    <?php csrf_token(); ?>
    <h2><?php echo __('Post a Reply');?></h2>
    <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
    <input type="hidden" name="a" value="reply">
    <div>
        <p><em><?php
         echo __('To best assist you, we request that you be specific and detailed'); ?></em>
        <font class="error">*&nbsp;<?php echo $errors['message']; ?></font>
        </p>
        <textarea name="message" id="message" cols="50" rows="9" wrap="soft"
            class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                ?> draft" <?php
list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.client', $ticket->getId(), $info['message']);
echo $attrs; ?>><?php echo $draft ?: $info['message'];
            ?></textarea>
    <?php
    if ($messageField->isAttachmentsEnabled()) {
        print $attachments->render(array('client'=>true));
    } ?>
    </div>
<?php
  if ($ticket->isClosed() && $ticket->isReopenable()) { ?>
    <div class="warning-banner">
        <?php echo __('Ticket will be reopened on message post'); ?>
    </div>
<?php } ?>
     <!-- Yaseen -->
     <p style="text-align:center">
            <input type="submit" value="<?php echo __('Post Reply'); ?>">
            <input type="reset" value="<?php echo __('Reset'); ?>">
            <input type="button" value="<?php echo __('Cancel'); ?>" onClick="history.go(-1)">
            <?php $GetCurrentAgents_Q = "select count(*) from ost_ticket t inner join ost_help_topic_flow h on h.ticket_id=t.ticket_id and t.current_step=h.step_number where t.ticket_id=" . $ticket->getid() . " and h.user_id=" . $thisclient->getId();

if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
    $Res = db_fetch_row($GetCurrentAgents_Res);
    $CurrentAgents = $Res[0];
}
$count=array();
$Userid=array();
$GetUser = "select count(*),f.user_id,f.step_number as curr_step from ost_help_topic_flow f, ost_ticket t where f.step_number = t.current_step and   f.ticket_id=t.ticket_id and t.ticket_id=". $ticket->getid() . "";


if (($GetUser_Res = db_query($GetUser)) && db_num_rows($GetUser_Res)) {
    while (list($Scount,$SUser_id,$Scurr_step) = db_fetch_row($GetUser_Res)) {
                    
        array_push($count, $Scount);
        array_push($Userid, $SUser_id);
        array_push($curr_step, $Scurr_step);

    // $ID = db_fetch_row($sql_Res);
}
}
 

if ($CurrentAgents > 0) { 
  if (isset($_GET["done"]))
  {
    if ($curr_step[0]==3)
    {
      $sql02 = "UPDATE ost_ticket set `status_id`=3 ,current_step =-1 , closed =current_timestamp() ,isoverdue=0  where ticket_id=" . $ticket->getid() . " ";
      $sqlClose="UPDATE `ost_ticket` SET `staff_id`=21  WHERE `ticket_id`=".$ticket->getId();
      db_query($sqlClose);
    }
    else 
    {
      $sql02 = "UPDATE ost_ticket set current_step = current_step + 1 , user_id =case WHEN " . $count[0] . " > 0 then $Userid[0]  else to_user_id end where ticket_id=" . $ticket->getid() . " ";
    }
       db_query($sql02);
       echo '<script type="text/javascript">',
       'document.getElementById("message").value = sessionStorage.getItem("message");',
       ' const myForm = document.getElementById("reply");  myForm.submit();',
    '</script>';
   }?> 
<input type="button" id="btn_done" class="button" value ="done from me" />
<?php } ?>
        </p>
    </form>
<?php
} else {
    echo "<h1 style='float:right'>" . ".الحالية في حال وجود مشكلة Ticket جديدة و وضع رقم ال Ticket لطفا فتح,Ticket تم انهاء هذه ال" . '</h1><br />';
} ?>
    <!-- Yaseen -->
<script>
     document.getElementById('btn_done').addEventListener('click', function(){
      let taVal = document.getElementById('message').value;
      if(taVal == ''){
        alert('message field is required')
    }
    else 
    {
        sessionStorage.setItem("message", taVal);
        location.href='tickets.php?id=<?php echo $ticket->getId();?>&done=true'
    }});
    </script>
<script type="text/javascript">
<?php
// Hover support for all inline images
$urls = array();
foreach (AttachmentFile::objects()->filter(array(
    'attachments__thread_entry__thread__id' => $ticket->getThreadId(),
    'attachments__inline' => true,
)) as $file) {
    $urls[strtolower($file->getKey())] = array(
        'download_url' => $file->getDownloadUrl(['type' => 'H']),
        'filename' => $file->name,
    );
} ?>
showImagesInline(<?php echo JsonDataEncoder::encode($urls); ?>);
</script>
