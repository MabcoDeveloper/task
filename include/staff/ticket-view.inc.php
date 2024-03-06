<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffPerm($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();
if (isset($_POST['qualify'])) {
    $get_ex="SELECT `qualify` FROM `ost_qualify` WHERE `ticket_id` = ".$ticket->getId();
    if (($get_ex_Res = db_query($get_ex)) && db_num_rows($get_ex_Res)) {
    
        $Ex = db_fetch_row($get_ex_Res);
    }

    $sql_in="INSERT INTO `ost_connect_status`( `ticket_id`, `status_id` , `staff_id `) VALUES (".$ticket->getId().",".$_POST['External_s']." , ".$thisstaff->getId().")";
    if(db_query($sql_in)){
        echo "<script>
                                         
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
    }
    // $test = $_POST['qualify'];
    
}
if($_POST){
    if(isset($_POST['Beenote']))
    {
       
        $Insert="INSERT INTO `ost_thread_entry`(`pid`, `thread_id`, `staff_id`, `user_id`, `type`, `flags`, `poster`, `body`, `format`, `ip_address`,  `created`, `updated`, `recipients`) 
        SELECT  
        (SELECT MAX(`ost_thread_entry`.`id`) FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'),
            
        (SELECT `id` FROM `ost_thread` WHERE `object_id`= ".$ticket->getId()." AND `object_type` ='T'),
            
        (".$thisstaff->getId()."),
            
         (SELECT `user_id` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`id`=(SELECT MAX(`ost_thread_entry`.`id`) FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `type` = 'R')),
            
         ('R'),
         (SELECT `flags` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`id`=(SELECT MAX(`ost_thread_entry`.`id`) FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `type` = 'R')),
         
         (SELECT CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) from `ost_staff` WHERE `ost_staff`.`staff_id`=".$thisstaff->getId()."),
         
         ('".$_POST["Beenote"]."'),
         ('html'),
         ('::1'),
         (Now()),
         (Now()),
         (SELECT `recipients` FROM `ost_thread_entry` WHERE `id` = (SELECT MAX(`ost_thread_entry`.`id`) FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'))";
         db_query($Insert);
        
    }
if(isset($_POST['External_s'])){
    if($_POST['External_s'] == 4 ){
        $sqlClose="UPDATE `ost_ticket` SET `current_step`= 2  WHERE `ticket_id`=".$ticket->getId();
        //rahaf
        $sql_get_last_thread="SELECT `body`  FROM `ost_thread_entry` 
        INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
        WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`created`=(SELECT MAX(`ost_thread_entry`.`created`)  FROM `ost_thread_entry` 
        INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
        WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T')";
        if (($sql_get_last_thread_Res = db_query($sql_get_last_thread)) && db_num_rows($sql_get_last_thread_Res)) {
    
            $thread = db_fetch_row($sql_get_last_thread_Res);
        }
        $ID=array();
        $sql_get_hp="SELECT `topic` FROM `ost_help_topic` WHERE `dept_id` = 26";
        if (($sql_Res = db_query($sql_get_hp)) && db_num_rows($sql_Res)) {
            while (list($ID_) = db_fetch_row($sql_Res)) {
                            
                array_push($ID, $ID_);
            // $ID = db_fetch_row($sql_Res);
        }
    }
        if(in_array(trim(explode("/", $ticket->getHelpTopic())[1]," "),$ID) || in_array(trim(explode("/", $ticket->getHelpTopic())[0]," "),$ID)){
            $get_D="SELECT `body`  FROM `ost_thread_entry` 
            INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
            WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'";
             if (($get_D_Res = db_query($get_D)) && db_num_rows($get_D_Res)) {
                
                $DD = db_fetch_row($get_D_Res);
            }
            $phone = explode(':',explode('نوع المركبة',explode('اسم السائق',explode('رقم الموبايل', $DD[0])[1])[0]) [0])[1];
        }
        $test=explode(' ',$thread[0]); 
        $msg="Mr . ".explode("اسم السائق",explode("رقم الموبايل", $DD[0])[0])[1]."you have an appointment in :  ".explode("/>",$test[1])[1]." ".$test[2].$test[3]." at showroom :   ".$test[4];
        CallAPI($phone,$msg)  ;
        //rahaf
        if(db_query($sqlClose)){
            echo "<script>
                                         
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
        }
    }
    $sqll="SELECT `status_id`  FROM `ost_connect_status` WHERE `ticket_id` =".$ticket->getId();
    if (($sql_Res = db_query($sqll)) && db_num_rows($sql_Res)) {
    
        $St = db_fetch_row($sql_Res);
    }
        
    $sqlevent = "INSERT INTO `ost_thread_event` (`id`, `thread_id`, `event_id`, `staff_id`, `team_id`, `dept_id`, `topic_id`, `data`, `username`, `uid`, `uid_type`, `annulled`, `timestamp`) VALUES
     (NULL, (select max(id) from ost_thread where object_id = ".$ticket->getId()."), (select id from ost_event where description = ".$_POST['External_s']."), ".$thisstaff->getId().", '154', '26', '161', NULL, (select username from ost_staff where staff_id = ".$thisstaff->getId()."), NULL, 'S', '0', Now());";
    db_query($sqlevent);

    $sql_in="INSERT INTO `ost_connect_status`( `ticket_id`, `status_id` , `staff_id`) VALUES (".$ticket->getId().",".$_POST['External_s']." , ".$thisstaff->getId().")";
    if(db_query($sql_in)){
        echo "<script>
                                         
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
    }


    
}}
//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$role  = $ticket->getRole($thisstaff);
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
if (!$lock && $cfg->getTicketLockMode() == Lock::MODE_ON_VIEW)
    $lock = $ticket->acquireLock($thisstaff->getId());
$mylock = ($lock && $lock->getStaffId() == $thisstaff->getId()) ? $lock : null;
$id    = $ticket->getId();    //Ticket ID.
$isManager = $dept->isManager($thisstaff); //Check if Agent is Manager
$canRelease = ($isManager || $role->hasPerm(Ticket::PERM_RELEASE)); //Check if Agent can release tickets
$canAnswer = ($isManager || $role->hasPerm(Ticket::PERM_REPLY)); //Check if Agent can mark as answered/unanswered

//Useful warnings and errors the user might want to know!
if ($ticket->isClosed() && !$ticket->isReopenable())
    $warn = sprintf(
            __('Current ticket status (%s) does not allow the end user to reply.'),
            $ticket->getStatus());
elseif ($ticket->isAssigned()
        && (($staff && $staff->getId()!=$thisstaff->getId())
            || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.= sprintf('&nbsp;&nbsp;<span class="Icon assignedTicket">%s</span>',
            sprintf(__('Ticket is assigned to %s'),
                implode('/', $ticket->getAssignees())
                ));

if (!$errors['err']) {

    if ($lock && $lock->getStaffId()!=$thisstaff->getId())
        $errors['err'] = sprintf(__('%s is currently locked by %s'),
                __('This ticket'),
                $lock->getStaffName());
    elseif (($emailBanned=Banlist::isBanned($ticket->getEmail())))
        $errors['err'] = __('Email is in banlist! Must be removed before any reply/response');
    elseif (!Validator::is_valid_email($ticket->getEmail()))
        $errors['err'] = __('EndUser email address is not valid! Consider updating it before responding');
}


?>
<script>
$(document).ready(
  function() {
    $('#qualify').change(
      function(){

        $('#contact-label').text($('option:selected',this).text());

      }
      );
  }
  );
</script>
<script>
$(document).ready(
  function() {
    $('#External_s').change(
      function(){

        $('#contact-label_external').text($('option:selected',this).text());

      }
      );
  }
  );
</script>
<?php
$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

?>
<div>
    <div class="sticky bar">
       <div class="content">
        <div class="pull-right flush-right">
<?php
        $sql="SELECT `id` FROM `ost_ticket_bookmark` WHERE `staff_id` = ".$thisstaff->getId()." AND `ticket_id` = ".$ticket->getId();
            //    echo $sql;
            if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
    
                $ID = db_fetch_row($sql_Res);
            }
                
if($ID[0] == null){
    ?>
    <!-- Mark task as Important -->
    <span 
    class="action-button">
    <a 
        href=<?php echo sprintf('tickets.php?Mark=%d', $ticket->getId()) ?> 
        title="<?php echo __('Mark as important'); ?>">
        <i class="icon-bookmark"></i></a>

</span><?php
}
else{
    ?>
    <!-- UnMark task as Important -->
    <span style="background-color: red;"
    class="action-button">
    <a style="background-color: red;"
        href=<?php echo sprintf('tickets.php?UnMark=%d', $ID[0]) ?> 
        title="<?php echo __('Mark as important'); ?>">
        <i class="icon-bookmark" style="background-color: red;" ></i></a>

</span><?php
}
            
            if ($thisstaff->hasPerm(Email::PERM_BANLIST)
                    || $role->hasPerm(Ticket::PERM_EDIT)
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button pull-right" data-placement="bottom" data-dropdown="#action-dropdown-more" data-toggle="tooltip" title="<?php echo __('More');?>">
                <i class="icon-caret-down pull-right"></i>
                <span ><i class="icon-cog"></i></span>
            </span>
            <?php
            }

            if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
                <span class="action-button pull-right"><a data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Edit'); ?>" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i></a></span>
            <?php
            } ?>
            <span class="action-button pull-right" data-placement="bottom" data-dropdown="#action-dropdown-print" data-toggle="tooltip" title="<?php echo __('Print'); ?>">
                <i class="icon-caret-down pull-right"></i>
                <a id="ticket-print" aria-label="<?php echo __('Print'); ?>" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i></a>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> <?php echo __('Ticket Thread'); ?></a>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> <?php echo __('Thread + Internal Notes'); ?></a>
              </ul>
            </div>
            <?php
            // Transfer
            if ($role->hasPerm(Ticket::PERM_TRANSFER)) {?>
            <span class="action-button pull-right">
            <a class="ticket-action" id="ticket-transfer" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Transfer'); ?>"
                data-redirect="tickets.php"
                href="#tickets/<?php echo $ticket->getId(); ?>/transfer"><i class="icon-share"></i></a>
            </span>
            <?php
            } ?>

            <?php
            // Assign
            if ($ticket->isOpen() && $role->hasPerm(Ticket::PERM_ASSIGN)) {?>
            <span class="action-button pull-right"
                data-dropdown="#action-dropdown-assign"
                data-placement="bottom"
                data-toggle="tooltip"
                title=" <?php echo $ticket->isAssigned() ? __('Assign') : __('Reassign'); ?>"
                >
                <i class="icon-caret-down pull-right"></i>
                <a class="ticket-action" id="ticket-assign"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign"><i class="icon-user"></i></a>
            </span>
            <div id="action-dropdown-assign" class="action-dropdown anchor-right">
              <ul>
                <?php
                // Agent can claim team assigned ticket
                if (!$ticket->getStaff()
                        && (!$dept->assignMembersOnly()
                            || $dept->isMember($thisstaff))
                        ) { ?>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php?id=<?php echo
                    $ticket->getId(); ?>"
                    href="#tickets/<?php echo $ticket->getId(); ?>/claim"><i
                    class="icon-chevron-sign-down"></i> <?php echo __('Claim'); ?></a>
                <?php
                } ?>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign/agents"><i
                    class="icon-user"></i> <?php echo __('Agent'); ?></a>
                 <li><a class="no-pjax ticket-action"
                    data-redirect="tickets.php"
                    href="#tickets/<?php echo $ticket->getId(); ?>/assign/teams"><i
                    class="icon-group"></i> <?php echo __('Team'); ?></a>
              </ul>
            </div>
            <?php
            } ?>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
                    <li><a class="change-user" href="#tickets/<?php
                    echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> <?php
                    echo __('Change Owner'); ?></a></li>
                <?php
                 }

                 if ($ticket->isAssigned() && $canRelease) { ?>
                        <li><a href="#tickets/<?php echo $ticket->getId();
                            ?>/release" class="ticket-action"
                             data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>" >
                               <i class="icon-unlock"></i> <?php echo __('Release (unassign) Ticket'); ?></a></li>
                 <?php
                 }
                 if($ticket->isOpen() && $isManager) {
                    if(!$ticket->isOverdue()) { ?>
                        <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> <?php
                            echo __('Mark as Overdue'); ?></a></li>
                    <?php
                    }
                 }
                 if($ticket->isOpen() && $canAnswer) {
                    if($ticket->isAnswered()) { ?>
                    <li><a href="#tickets/<?php echo $ticket->getId();
                        ?>/mark/unanswered" class="ticket-action"
                            data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>">
                            <i class="icon-circle-arrow-left"></i> <?php
                            echo __('Mark as Unanswered'); ?></a></li>
                    <?php
                    } else { ?>
                    <li><a href="#tickets/<?php echo $ticket->getId();
                        ?>/mark/answered" class="ticket-action"
                            data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>">
                            <i class="icon-circle-arrow-right"></i> <?php
                            echo __('Mark as Answered'); ?></a></li>
                    <?php
                    }
                } ?>

                <?php
                if ($role->hasPerm(Ticket::PERM_REFER)) { ?>
                <li><a href="#tickets/<?php echo $ticket->getId();
                    ?>/referrals" class="ticket-action"
                     data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>" >
                       <i class="icon-exchange"></i> <?php echo __('Manage Referrals'); ?></a></li>
                <?php
                } ?>
                <?php
                if ($role->hasPerm(Ticket::PERM_EDIT)) { ?>
                <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i> <?php echo __('Manage Forms'); ?></a></li>
                <?php
                }

                if ($role->hasPerm(Ticket::PERM_REPLY)) {
                    ?>
                <li>

                    <?php
                    $recipients = __(' Manage Collaborators');

                    echo sprintf('<a class="collaborators manage-collaborators"
                            href="#thread/%d/collaborators"><i class="icon-group"></i>%s</a>',
                            $ticket->getThreadId(),
                            $recipients);
                   ?>
                </li>
                <?php
                } ?>


<?php           if ($thisstaff->hasPerm(Email::PERM_BANLIST)
                    && $role->hasPerm(Ticket::PERM_REPLY)) {
                     if(!$emailBanned) {?>
                        <li><a class="confirm-action" id="ticket-banemail"
                            href="#banemail"><i class="icon-ban-circle"></i> <?php echo sprintf(
                                Format::htmlchars(__('Ban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a  class="confirm-action" id="ticket-banemail"
                            href="#unbanemail"><i class="icon-undo"></i> <?php echo sprintf(
                                Format::htmlchars(__('Unban Email <%s>')),
                                $ticket->getEmail()); ?></a></li>
                    <?php
                     }
                  }
                  if ($role->hasPerm(Ticket::PERM_DELETE)) {
                     ?>
                    <li class="danger"><a class="ticket-action" href="#tickets/<?php
                    echo $ticket->getId(); ?>/status/delete"
                    data-redirect="tickets.php"><i class="icon-trash"></i> <?php
                    echo __('Delete Ticket'); ?></a></li>
                <?php
                 }
                ?>
              </ul>
            </div>
                <!-- <?php
                //if ($role->hasPerm(Ticket::PERM_REPLY)) { ?>
                <a href="#post-reply" class="post-response action-button"
                data-placement="bottom" data-toggle="tooltip"
                title="<?php //echo __('Post Reply'); ?>"><i class="icon-mail-reply"></i></a>
                <?php
                //} ?> -->
                <a href="#post-note" id="post-note" class="post-response action-button"
                data-placement="bottom" data-toggle="tooltip"
                title="<?php echo __('Post Internal Note'); ?>"><i class="icon-file-text"></i></a>
                <?php // Status change options
                echo TicketStatus::status_options();
                ?>
           </div>
           <?php if(isset( $_SESSION["ticket_page_number"])){
?>
<button type="button"><a href="tickets.php?sort=&order=&p=<?php echo  $_SESSION["ticket_page_number"]; ?>">Back</a></button>
<?php
}
else{
  ?>
  <button type="button"><a href="tickets.php">Back</a></button>
  <?php
}
?>
<br>
<br>
        <div class="flush-left">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="<?php echo __('Reload'); ?>"><i class="icon-refresh"></i>
             <?php echo sprintf(__('Ticket #%s'), $ticket->getNumber()); ?></a>
            </h2>
        </div>
    </div>
  </div>
</div>
<div class="clear tixTitle has_bottom_border">
<?php
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
$get_D="SELECT `body`  FROM `ost_thread_entry` 
INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
WHERE `ost_thread`.`object_id`=".$ticket->getId()." AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'";
 if (($get_D_Res = db_query($get_D)) && db_num_rows($get_D_Res)) {
    
    $DD = db_fetch_row($get_D_Res);
}
echo explode("اسم السائق",explode("رقم الموبايل", $DD[0])[0])[1];
?>
<form action="#" method="post">
<?php csrf_token(); ?>
<input style="float: right; width: 1em;  height: 1.5em;" type="submit" name="submit" value="Go"/>
<select style="float: right; margin-left: 10px;" name="qualify" id="qualify">  
  <option value="0">Select qualify</option>
  <option value="1">مؤهل</option>  
  <option value="2">غر مؤهل </option>    
</select>
<!-- <input style="float: right; margin-left: 10px;" type="submit" name="submit" value="Go"/> -->
</form> 

<!-- <button style="font-size:15px;float: right;"><a href="https://task.mabcoonline.com:444/calendar_0/?ticket_id=<?php //echo $ticket->getId() ; ?>&driver=<?php //echo  explode("اسم السائق",explode("رقم الموبايل", $DD[0])[0])[1] ; ?>"> View Calender  </a><i class="fa fa-calendar"></i></button> -->
<button style="float: right;"><a   onclick="window.open('https://task.mabcoonline.com:444/calendar_0/?ticket_id=<?php echo $ticket->getId() ; ?>&type=<?php echo $type ; ?>&staff_id=<?php echo $thisstaff->getId();?>&driver=<?php echo  explode('اسم السائق',explode('رقم الموبايل', $DD[0])[0])[1] ; ?>','_blank');window.close();return false"  href="#"> View Calender  </a><i class="fa fa-calendar"></i></button>

<?php
}
?>
<!-- <button style="float: right;border-radius: 12px; background-color: #FFAA71; " type="button"><a href="http://localhost/calendar_0/">View Calender</a></button> -->
    <h3>
    <?php $subject_field = TicketForm::getInstance()->getField('subject');
        echo $subject_field ? $subject_field->display($ticket->getSubject())
            : Format::htmlchars($ticket->getSubject()); ?>
    </h3>
</div>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('Status');?>:</th>
                    <?php
                         if ($role->hasPerm(Ticket::PERM_CLOSE)) {?>
                    <td>
                      <a class="tickets-action" data-dropdown="#action-dropdown-statuses" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Change Status'); ?>"
                          data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                          href="#statuses">
                          <?php echo $ticket->getStatus(); ?>
                      </a>
                    </td>
                      <?php } else { ?>
                          <td><?php echo ($S = $ticket->getStatus()) ? $S->display() : ''; ?></td>
                      <?php } ?>
                </tr>
                <tr>
                    <th><?php echo __('Priority');?>:</th>
                      <?php
                         if ($role->hasPerm(Ticket::PERM_EDIT)) {?>
                           <td>
                             <a class="ticket-action" id="inline-update" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                                 data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                                 href="#tickets/<?php echo $ticket->getId(); ?>/field/priority/edit">
                                 <?php echo $ticket->getPriority(); ?>
                             </a>
                           </td>
                      <?php } else { ?>
                           <td><?php echo $ticket->getPriority(); ?></td>
                      <?php } ?>
                </tr>
                <tr>
                    <th><?php echo __('Department');?>:</th>
                    <?php
                    if ($role->hasPerm(Ticket::PERM_TRANSFER)) {?>
                      <td>
                        <a class="ticket-action" id="ticket-transfer" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Transfer'); ?>"
                          data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                          href="#tickets/<?php echo $ticket->getId(); ?>/transfer"><?php echo Format::htmlchars($ticket->getDeptName()); ?>
                        </a>
                      </td>
                    <?php
                  }else {?>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                  <?php } ?>
                </tr>
                <tr>
                    <th><?php echo __('Create Date');?>:</th>
                    <td><?php echo Format::datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100"><?php echo __('User'); ?>:</th>
                    <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if ($user) { ?>
                            <a href="tickets.php?<?php echo Http::build_query(array(
                                'status'=>'open', 'a'=>'search', 'uid'=> $user->getId()
                            )); ?>" title="<?php echo __('Related Tickets'); ?>"
                            data-dropdown="#action-dropdown-stats">
                            (<b><?php echo $user->getNumTickets(); ?></b>)
                            </a>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open));

                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i
                                                class="icon-folder-close-alt icon-fixed-width"></i> %s</a></li>',
                                                $user->getId(), sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed));
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
<?php   if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
                                    <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i> <?php echo __('Manage User'); ?></a></li>
<?php   } ?>
                                </ul>
                            </div>
                            <?php
                            if ($role->hasPerm(Ticket::PERM_EDIT)) {
                            $numCollaborators = $ticket->getThread()->getNumCollaborators();
                             if ($ticket->getThread()->getNumCollaborators())
                                $recipients = sprintf(__('%d'),
                                        $numCollaborators);
                            else
                              $recipients = 0;

                             echo sprintf('<span><a class="manage-collaborators preview"
                                    href="#thread/%d/collaborators"><span><i class="icon-group"></i> (<span id="t%d-collaborators">%s</span>)</span></a></span>',
                                    $ticket->getThreadId(),
                                    $ticket->getThreadId(),
                                    $recipients);
                             }?>
<?php                   } # end if ($user) ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo __('Email'); ?>:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
<?php   if ($user->getOrganization()) { ?>
                <tr>
                    <th><?php echo __('Organization'); ?>:</th>
                    <td><i class="icon-building"></i>
                    <?php echo Format::htmlchars($user->getOrganization()->getName()); ?>
                        <a href="tickets.php?<?php echo Http::build_query(array(
                            'status'=>'open', 'a'=>'search', 'orgid'=> $user->getOrgId()
                        )); ?>" title="<?php echo __('Related Tickets'); ?>"
                        data-dropdown="#action-dropdown-org-stats">
                        (<b><?php echo $user->getNumOrganizationTickets(); ?></b>)
                        </a>
                            <div id="action-dropdown-org-stats" class="action-dropdown anchor-right">
                                <ul>
<?php   if ($open = $user->getNumOpenOrganizationTickets()) { ?>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'status' => 'open', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-folder-open-alt icon-fixed-width"></i>
                                    <?php echo sprintf(_N('%d Open Ticket', '%d Open Tickets', $open), $open); ?>
                                    </a></li>
<?php   }
        if ($closed = $user->getNumClosedOrganizationTickets()) { ?>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'status' => 'closed', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-folder-close-alt icon-fixed-width"></i>
                                    <?php echo sprintf(_N('%d Closed Ticket', '%d Closed Tickets', $closed), $closed); ?>
                                    </a></li>
                                    <li><a href="tickets.php?<?php echo Http::build_query(array(
                                        'a' => 'search', 'orgid' => $user->getOrgId()
                                    )); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> <?php echo __('All Tickets'); ?></a></li>
<?php   }
        if ($thisstaff->hasPerm(User::PERM_DIRECTORY)) { ?>
                                    <li><a href="orgs.php?id=<?php echo $user->getOrgId(); ?>"><i
                                        class="icon-building icon-fixed-width"></i> <?php
                                        echo __('Manage Organization'); ?></a></li>
<?php   } ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
<?php   } # end if (user->org) ?>
                <tr>
                  <th><?php echo __('Source'); ?>:</th>
                  <td>
                  <?php
                         if ($role->hasPerm(Ticket::PERM_EDIT)) {?>
                    <a class="ticket-action" id="inline-update" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                        data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"

                        href="#tickets/<?php echo $ticket->getId(); ?>/field/source/edit">
                        <?php echo Format::htmlchars($ticket->getSource());
                        ?>
                    </a>
                      <?php
                         } else {
                            echo Format::htmlchars($ticket->getSource());
                        }

                    if (!strcasecmp($ticket->getSource(), 'Web') && $ticket->getIP())
                        echo '&nbsp;&nbsp; <span class="faded">('.Format::htmlchars($ticket->getIP()).')</span>';
                    ?>
                 </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100"><?php echo __('Assigned To');?>:</th>
                    <?php
                    if ($role->hasPerm(Ticket::PERM_ASSIGN)) {?>
                    <td>
                                                    <a class="ticket-action" id="ticket-assign"
                            data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                            href="#tickets/<?php echo $ticket->getId(); ?>/assign">
                            <?php
                                if($ticket->isAssigned())
                                    echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                                else
                                    echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                            ?>
                        </a>
                    </td>
                    <?php
                    } else { ?>
                    <td>
                      <?php
                      if($ticket->isAssigned())
                          echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                      else
                          echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                      ?>
                    </td>
                    <?php
                    } ?>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100"><?php echo __('Closed By');?>:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th><?php echo __('SLA Plan');?>:</th>
                    <td>
                    <?php
                         if ($role->hasPerm(Ticket::PERM_EDIT)) {?>
                      <a class="ticket-action" id="inline-update" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                          data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"

                          href="#tickets/<?php echo $ticket->getId(); ?>/field/sla/edit">
                          <?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?>
                      </a>
                      <?php } else { ?>
                        <?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; '.__('None').' &mdash;</span>'; ?>
                      <?php } ?>
                    </td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th><?php echo __('Due Date');?>:</th>
                    <?php
                         if ($role->hasPerm(Ticket::PERM_EDIT)) {?>
                           <td>
                      <a class="ticket-action" id="inline-update" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                          data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"

                          href="#tickets/<?php echo $ticket->getId();
                           ?>/field/duedate/edit">
                          <?php echo Format::datetime($ticket->getEstDueDate()); ?>
                      </a>
                    <td>
                      <?php } else { ?>
                           <td><?php echo Format::datetime($ticket->getEstDueDate()); ?></td>
                      <?php } ?>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th><?php echo __('Close Date');?>:</th>
                    <td><?php echo Format::datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                $ID1=array();
                $Id=array();
                $sql_get_hp="SELECT `topic` FROM `ost_help_topic` WHERE `dept_id` = 26";
                if (($sql_Res = db_query($sql_get_hp)) && db_num_rows($sql_Res)) {
                    while (list($ID_) = db_fetch_row($sql_Res)) {
                                    
                        array_push($ID1, $ID_);
                    // $ID = db_fetch_row($sql_Res);
                }
                }
                $sql_get_Id="SELECT `qualify`  FROM `ost_qualify` WHERE `ticket_id` =" . $ticket->getId();
                if (($sql_Res = db_query($sql_get_Id)) && db_num_rows($sql_Res)) {
                    while (list($ID_) = db_fetch_row($sql_Res)) {
                                    
                        array_push($Id, $ID_);
                    // $ID = db_fetch_row($sql_Res);
                }
                }
                if(in_array(trim(explode("/", $ticket->getHelpTopic())[1]," "),$ID1) || in_array(trim(explode("/", $ticket->getHelpTopic())[0]," "),$ID1)){
                 ?>

                 <tr style="background: #FFD2E1;">
                    <th><?php echo __('qualify');?>:</th>
                    <?php if($Id[0] == 0 ){ ?>
                        <td><b><label  id="contact-label" ><?php echo "غير محدد "; ?></label></b></td>
                    
                    <?php } elseif($Id[0] == 1 ){ ?>
                        <td><b><label  id="contact-label" ><?php echo "مؤهل "; ?></label></b></td>
                    <?php } elseif($Id[0] == 2 ){ ?>
                        <td><b><label  id="contact-label" ><?php echo " غير مؤهل"; ?></label></b></td>
                    <?php }  ?> 
                 </tr>
                 <?php } 
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
                   
                        <td><b><label  id="contact-label_external" ><?php echo $arr_s[0]; ?></label></b></td>
                    
                    
                 </tr>
                 <?php } 
                 ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100"><?php echo __('Help Topic');?>:</th>
                      <?php
                           if ($role->hasPerm(Ticket::PERM_EDIT)) {?>
                             <td>
                        <a class="ticket-action" id="inline-update" data-placement="bottom"
                            data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                            data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                            href="#tickets/<?php echo $ticket->getId(); ?>/field/topic/edit">
                            <?php echo $ticket->getHelpTopic() ?: __('None'); ?>
                        </a>
                      </td>
                        <?php } else { ?>
                             <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                        <?php } ?>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Message');?>:</th>
                    <td><?php echo Format::datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('Last Response');?>:</th>
                    <td><?php echo Format::datetime($ticket->getLastRespDate()); ?></td>
                </tr>
                <?php
  $arr_users=array();
  $sql_get_users="SELECT u.username from ost_help_topic_flow f inner join ost_user_account u on u.user_id =f.user_id  where ticket_id =".$ticket->getId();
  if (($sql_Res = db_query($sql_get_users)) && db_num_rows($sql_Res)) {
     while (list($names) = db_fetch_row($sql_Res)) {
                     
         array_push($arr_users, $names);
     // $ID = db_fetch_row($sql_Res);
 }
 }
 if (isset($arr_users[0])){
   ?>
                <tr>
                    <th nowrap><?php echo __('User 1');?>:</th>
                    <td><?php  echo $arr_users[0];?></td>
                </tr>
                <tr>
                    <th nowrap><?php echo __('User 2');?>:</th>
                    <td><?php  echo $arr_users[1];?></td>
                </tr>
                <?php
}
                ?>
<?php
$sql = "SELECT `external_approval` FROM `ost_ticket` WHERE `ticket_id`=" . $ticket->getId();
                                if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                                    while (list($CC1_) = db_fetch_row($sql_Res)) {
                                        $CC1= $CC1_;  
                                    }   
                                }
if($CC1 == 1 ){
   ?>
    <tr>
                    <th nowrap><?php echo __('External Approval');?>:</th>
                    <td><?php echo "Made with external approval"; ?></td>
                </tr>
   <?php
}

?>

               
            </table>
        </td>
    </tr>
</table>
<br>
<?php
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
   
    //Find fields to exclude if disabled by help topic
    $disabled = Ticket::getMissingRequiredFields($ticket, true);

    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('subject', 'priority'),
        'field__id__in' => $disabled,
    )));
    $displayed = array();
    foreach($answers as $a) {
        if (!$a->getField()->isVisibleToStaff())
            continue;
        $displayed[] = $a;
    }
    if (count($displayed) == 0)
        continue;
    ?>
    <table class="ticket_info custom-data" cellspacing="0" cellpadding="0" width="100%" border="0">
    <thead>
        <th colspan="2"><?php echo Format::htmlchars($form->getTitle()); ?></th>
    </thead>
    <tbody>
<?php
    foreach ($displayed as $a) {
        $id =  $a->getLocal('id');
        $label = $a->getLocal('label');
        $v = $a->display() ?: '<span class="faded">&mdash;' . __('Empty') .  '&mdash; </span>';
        $field = $a->getField();
        $isFile = ($field instanceof FileUploadField);
?>
        <tr>
            <td width="200"><?php echo Format::htmlchars($label); ?>:</td>
            <td>
            <?php if ($role->hasPerm(Ticket::PERM_EDIT)
                    && $field->isEditableToStaff()) {
                    $isEmpty = strpos($v, '&mdash;');
                    if ($isFile && !$isEmpty)
                        echo $v.'<br>'; ?>
              <a class="ticket-action" id="inline-update" data-placement="bottom" data-toggle="tooltip" title="<?php echo __('Update'); ?>"
                  data-redirect="tickets.php?id=<?php echo $ticket->getId(); ?>"
                  href="#tickets/<?php echo $ticket->getId(); ?>/field/<?php echo $id; ?>/edit">
                  <?php
                    if (is_string($v) && $isFile && !$isEmpty) {
                      echo "<i class=\"icon-edit\"></i>";
                    } elseif (strlen($v) > 200) {
                      echo Format::truncate($v, 200);
                      echo "<br><i class=\"icon-edit\"></i>";
                    }
                    else
                      echo $v;
                  ?>
              </a>
            <?php
            } else {
                echo $v;
            } ?>
            </td>
        </tr>
<?php } ?>
    </tbody>
    </table>
<?php } ?>
<div class="clear"></div>

<?php
$tcount = $ticket->getThreadEntries($types)->count();
?>
<ul  class="tabs clean threads" id="ticket_tabs" >
    <li class="active"><a id="ticket-thread-tab" href="#ticket_thread"><?php
        echo sprintf(__('Ticket Thread (%d)'), $tcount); ?></a></li>
    <li><a id="ticket-tasks-tab" href="#tasks"
            data-url="<?php
        echo sprintf('#tickets/%d/tasks', $ticket->getId()); ?>"><?php
        echo __('Tasks');
        if ($ticket->getNumTasks())
            echo sprintf('&nbsp;(<span id="ticket-tasks-count">%d</span>)', $ticket->getNumTasks());
        ?></a></li>
</ul>

<div id="ticket_tabs_container">
<div id="ticket_thread" class="tab_content">

<?php
    // Render ticket thread
    $ticket->getThread()->render(
            array('M', 'R', 'N'),
            array(
                'html-id'   => 'ticketThread',
                'mode'      => Thread::MODE_STAFF,
                'sort'      => $thisstaff->thread_view_order
                )
            );
?>
<div class="clear"></div>
<?php
if ($errors['err'] && isset($_POST['a'])) {
    // Reflect errors back to the tab.
    $errors[$_POST['a']] = $errors['err'];
} elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php
} elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php
} ?>
  <?php
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
$sql_get_S="SELECT `id`,`status`  FROM `ost_external_status` WHERE  `user` = 'A' and id <> 4";
if (($sql_Res = db_query($sql_get_S)) && db_num_rows($sql_Res)) {
    while (list($ID_,$St) = db_fetch_row($sql_Res)) {
                    
        array_push($SId, $ID_);
        array_push($S, $St);
    // $ID = db_fetch_row($sql_Res);
}
}
?>
<div class="sticky bar stop actions">
       
        <form style ="padding: 10px 5px;
  background: #f9f9f9;
  border: 1px solid #aaa;
    border-top-color: rgb(170, 170, 170);
    border-top-style: solid;
    border-top-width: 1px;" class="tab_content spellcheck exclusive save" action="#" method="post">
        <label><b><?php echo __('Post Reply');?>:</b></label>
    <table width="100%">
<tr width="100%">
    <td width="120">
   </td>
    <td width="80%"> <textarea name="Beenote" id="Beeinternal_note" cols="80"
    placeholder="<?php echo __('Note details'); ?>"
    rows="9" wrap="soft"
    class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';?> draft draft-delete" ></textarea>
<br/></td>
    </tr>
    <tr>
    <td width="120">
    <label><b><?php echo __('External Status');?>:</b></label>
   
    <?php csrf_token(); ?></td>
    <td>
    <select name="External_s">
    <option disabled selected value> -- select a status -- </option>  
    <?php foreach ($S as $index => $item) {
    ?>
    <option value="<?php echo $SId[$index]; ?>"><?php echo $item; ?></option>
    <?php } ?> 
</select>

</td>
</table>
<p style="text-align:center;">
           <input class="save pending" type="submit" value="<?php echo __('Post Note');?>">
           <input class="" type="reset" value="<?php echo __('Reset');?>">
       </p>
   </form>
   </div>
<?php } 
else 
{
?>
<div class="sticky bar stop actions" id="response_options">
    <ul class="tabs" id="response-tabs">
        <?php
        if ($role->hasPerm(Ticket::PERM_REPLY)) { ?>
        <li class="active <?php
            echo isset($errors['reply']) ? 'error' : ''; ?>"><a
            href="#reply" id="post-reply-tab"><?php echo __('Post Reply');?></a></li>
        <?php
        } ?>
        <li><a href="#note" <?php
            echo isset($errors['postnote']) ?  'class="error"' : ''; ?>
            id="post-note-tab"><?php echo __('Post internal Note');?></a></li>
    </ul>
    <?php
    if ($role->hasPerm(Ticket::PERM_REPLY)) {
        $replyTo = $_POST['reply-to'] ?: 'all';
        $emailReply = ($replyTo != 'none');
        ?>
    <form id="reply" class="tab_content spellcheck exclusive save"
        data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
        data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
        action="tickets.php?id=<?php
        echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if ($errors['reply']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['reply']; ?>&nbsp;</td></tr>
            <?php
            }?>
           <tbody id="to_sec">
           <tr>
               <td width="120">
                   <label><strong><?php echo __('From'); ?>:</strong></label>
               </td>
               <td>
                   <select id="from_email_id" name="from_email_id">
                     <?php
                     // Department email (default).
                     if (($e=$dept->getEmail())) {
                        echo sprintf('<option value="%s" selected="selected">%s</option>',
                                 $e->getId(),
                                 Format::htmlchars($e->getAddress()));
                     }
                     // Optional SMTP addreses user can send email via
                     if (($emails = Email::getAddresses(array('smtp' =>
                                 true), false)) && count($emails)) {
                         echo '<option value=""
                             disabled="disabled">&nbsp;</option>';
                         $emailId = $_POST['from_email_id'] ?: 0;
                         foreach ($emails as $e) {
                             if ($dept->getEmail()->getId() == $e->getId())
                                 continue;
                             echo sprintf('<option value="%s" %s>%s</option>',
                                     $e->getId(),
                                      $e->getId() == $emailId ?
                                      'selected="selected"' : '',
                                      Format::htmlchars($e->getAddress()));
                         }
                     }
                     ?>
                   </select>
               </td>
           </tr>
            </tbody>
            <tbody id="recipients">
             <tr id="user-row">
                <td width="120">
                    <label><strong><?php echo __('Recipients'); ?>:</strong></label>
                </td>
                <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                    onclick="javascript:
                        $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                function (user) {
                                    window.location = 'tickets.php?id='<?php $ticket->getId(); ?>
                                });
                        return false;
                        "><span ><?php
                            echo Format::htmlchars($ticket->getOwner()->getEmail()->getAddress());
                    ?></span></a>
                </td>
              </tr>
               <tr><td>&nbsp;</td>
                   <td>
                   <div style="margin-bottom:2px;">
                    <?php
                    if ($ticket->getThread()->getNumCollaborators())
                        $recipients = sprintf(__('(%d of %d)'),
                                $ticket->getThread()->getNumActiveCollaborators(),
                                $ticket->getThread()->getNumCollaborators());

                         echo sprintf('<span"><a id="show_ccs">
                                 <i id="arrow-icon" class="icon-caret-right"></i>&nbsp;%s </a>
                                 &nbsp;
                                 <a class="manage-collaborators
                                 collaborators preview noclick %s"
                                  href="#thread/%d/collaborators">
                                 %s</a></span>',
                                 __('Collaborators'),
                                 $ticket->getNumCollaborators()
                                  ? '' : 'hidden',
                                 $ticket->getThreadId(),
                                         sprintf('<span id="t%d-recipients">%s</span></a></span>',
                                             $ticket->getThreadId(),
                                             $recipients)
                         );
                    ?>
                   </div>
                   <div id="ccs" class="hidden">
                     <div>
                        <span style="margin: 10px 5px 1px 0;" class="faded pull-left"><?php echo __('Select or Add New Collaborators'); ?>&nbsp;</span>
                        <?php
                        if ($role->hasPerm(Ticket::PERM_REPLY)) { ?>
                        <span class="action-button pull-left" style="margin: 2px  0 5px 20px;"
                            data-dropdown="#action-dropdown-collaborators"
                            data-placement="bottom"
                            data-toggle="tooltip"
                            title="<?php echo __('Manage Collaborators'); ?>"
                            >
                            <i class="icon-caret-down pull-right"></i>
                            <a class="ticket-action" id="collabs-button"
                                data-redirect="tickets.php?id=<?php echo
                                $ticket->getId(); ?>"
                                href="#thread/<?php echo
                                $ticket->getThreadId(); ?>/collaborators">
                                <i class="icon-group"></i></a>
                         </span>
                         <?php
                        }  ?>
                         <span class="error">&nbsp;&nbsp;<?php echo $errors['ccs']; ?></span>
                        </div>
                        <?php
                        if ($role->hasPerm(Ticket::PERM_REPLY)) { ?>
                        <div id="action-dropdown-collaborators" class="action-dropdown anchor-right">
                          <ul>
                             <li><a class="manage-collaborators"
                                href="#thread/<?php echo
                                $ticket->getThreadId(); ?>/add-collaborator/addcc"><i
                                class="icon-plus"></i> <?php echo __('Add New'); ?></a>
                             <li><a class="manage-collaborators"
                                href="#thread/<?php echo
                                $ticket->getThreadId(); ?>/collaborators"><i
                                class="icon-cog"></i> <?php echo __('Manage Collaborators'); ?></a>
                          </ul>
                        </div>
                        <?php
                        } ?>
                     <div class="clear">
                      <select id="collabselection" name="ccs[]" multiple="multiple"
                          data-placeholder="<?php
                            echo __('Select Active Collaborators'); ?>">
                          <?php
                          $collabs = $ticket->getCollaborators();
                          foreach ($collabs as $c) {
                              echo sprintf('<option value="%s" %s class="%s">%s</option>',
                                      $c->getUserId(),
                                      $c->isActive() ?
                                      'selected="selected"' : '',
                                      $c->isActive() ?
                                      'active' : 'disabled',
                                      $c->getName());
                          }
                          ?>
                      </select>
                     </div>
                 </div>
                 </td>
             </tr>
             <tr>
                <td width="120">
                    <label><?php echo __('Reply To'); ?>:</label>
                </td>
                <td>
                    <?php
                    // Supported Reply Types
                    $replyTypes = array(
                            'all'   =>  __('All Active Recipients'),
                            'user'  =>  sprintf('%s (%s)',
                                __('Ticket Owner'),
                                Format::htmlchars($ticket->getOwner()->getEmail())),
                            'none'  =>  sprintf('&mdash; %s  &mdash;',
                                __('Do Not Email Reply'))
                            );

                    $replyTo = $_POST['reply-to'] ?: 'all';
                    $emailReply = ($replyTo != 'none');
                    ?>
                    <select id="reply-to" name="reply-to">
                        <?php
                        foreach ($replyTypes as $k => $v) {
                            echo sprintf('<option value="%s" %s>%s</option>',
                                    $k,
                                    ($k == $replyTo) ?
                                    'selected="selected"' : '',
                                    $v);
                        }
                        ?>
                    </select>
                    <i class="help-tip icon-question-sign" href="#reply_types"></i>
                </td>
             </tr>
            </tbody>
            <tbody id="resp_sec">
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Response');?>:</strong></label>
                </td>
                <td>
                <?php
                if ($errors['response'])
                    echo sprintf('<div class="error">%s</div>',
                            $errors['response']);

                if ($cfg->isCannedResponseEnabled()) { ?>
                  <div>
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected"><?php echo __('Select a canned response');?></option>
                        <option value='original'><?php echo __('Original Message'); ?></option>
                        <option value='lastmessage'><?php echo __('Last Message'); ?></option>
                        <?php
                        if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                            echo '<option value="0" disabled="disabled">
                                ------------- '.__('Premade Replies').' ------------- </option>';
                            foreach($cannedResponses as $id =>$title)
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    </div>
                <?php } # endif (canned-resonse-enabled)
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                  <div>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __(
                        'Start writing your response here. Use canned responses from the drop-down above'
                        ); 
                     ?>"
                        rows="9" wrap="soft"
                        style="width:75%; font-size:medium ;"
                       <?php # yaseen edit because lag issues if($cfg->isRichTextEnabled()) echo 'richtext';?>
                        class="<?php if ($cfg->isRichTextEnabled()) echo '';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.response', $ticket->getId(), $info['response']);
    echo $attrs; ?>><?php echo $_POST ? $info['response'] : $draft;
                    ?></textarea>
                </div>
                <div id="reply_form_attachments" class="attachments">
                <?php
                    print $response_form->getField('attachments')->render();
                ?>
                </div>
                </td>
            </tr>
            <tr>
                <td width="120">
                    <label for="signature" class="left"><?php echo __('Signature');?>:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        <?php echo sprintf(__('Department Signature (%s)'), Format::htmlchars($dept->getName())); ?></label>
                    <?php
                    } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Ticket Status');?>:</strong></label>
                </td>
                <td>
                    <?php
                    $outstanding = false;
                    $outstandingNEw = false;
                    $outstandingAsign = false;
                    $TeamID = 0;
                    $GetTeamID_Q = "SELECT `ost_help_topic`.`team_id` FROM `ost_help_topic` 
                                    INNER JOIN `ost_ticket` ON `ost_ticket`.`topic_id` = `ost_help_topic`.`topic_id`
                                    WHERE `ost_ticket`.`ticket_id` = " . $ticket->getId();
                                
                    if (($GetTeamID_Res = db_query($GetTeamID_Q)) && db_num_rows($GetTeamID_Res)) {
                        $TeamID = db_fetch_row($GetTeamID_Res)[0];
                    }
            
            
                 $GetTeamMembersQ = "SELECT `staff_id` FROM `ost_team_member` WHERE `team_id` = $TeamID";
                    
                    
                 if (($GetTeamMembers_Res = db_query($GetTeamMembersQ)) && db_num_rows($GetTeamMembers_Res)) {
                    while (list($StaffID) = db_fetch_row($GetTeamMembers_Res)) {
                        if ($thisstaff->getId()== $StaffID){
                            $outstanding=true;
                        break;
                        }
                      else{
                          $outstanding=false;
                      }
                    }
                }
                    
                $sql="SELECT IF( EXISTS(SELECT`ost_ticket`.`staff_id`  FROM `ost_ticket`  
                INNER JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id`
                WHERE  `ost_help_topic`.`staff_id`=".$thisstaff->getId()."  AND `ost_ticket`.`ticket_id`=".$ticket->getId()."), 1, 0)" ;
                // echo $sql;
                if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                    while (list($StaffID) = db_fetch_row($sql_Res)) {
                        if ($StaffID==1){
                            $outstandingNEw=true;
                        break;
                        }
                      else{
                          $outstandingNEw=false;
                      }
                    }
                }

                $sql="SELECT IF( EXISTS(SELECT `staff_id` FROM `ost_ticket` WHERE `ticket_id`=".$ticket->getId()."), 1, 0)" ;
                // echo $sql;
                if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                    while (list($StaffID) = db_fetch_row($sql_Res)) {
                        if ($StaffID==1){
                            $outstandingAsign=true;
                        break;
                        }
                      else{
                          $outstandingAsign=false;
                      }
                    }
                }
                    // if ($role->hasPerm(Ticket::PERM_CLOSE)
                    //         && is_string($warning=$ticket->isCloseable())) {
                    //     $outstanding =  true;
                        
                        
                    //     echo sprintf('<div class="warning-banner">%s</div>', $warning);
                        
                    // } 
                    $sqlstepstaff ="SELECT h.staff_id from ost_help_topic_flow h inner join ost_ticket t on t.topic_id=h.help_topic_id where step_number=current_step and t.ticket_id=".$ticket->getId();
                     //echo $sqlstepstaff;
                    if (($stepstaff_Res = db_query($sqlstepstaff)) && db_num_rows($stepstaff_Res)) {
                        $stepstaff= db_fetch_row($stepstaff_Res)[0];
                    }
?>
                    
                    <select name="reply_status_id">
                    <?php
                    $statusId = $info['reply_status_id'] ?: $ticket->getStatusId();
                    $states = array('open');
                    //yaseen
                    if ($stepstaff == $thisstaff->getId())
                    $states = array_merge($states, array('closed'));
                    //rahaf
                      if ( $outstanding)
                          $states = array_merge($states, array('closed'));
                    //rahaf
                    if ( $outstandingNEw)
                    $states = array_merge($states, array('closed'));
                    if ( $outstandingAsign)
                    $states = array_merge($states, array('closed'));
                    $states = array_merge($states, array('refuse'));
                    

                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()),
                                $selected
                                ? (' ('.__('current').')') : ''
                                );
                    }
                    ?>
                    </select>
                    <?php 
                        $StaffID = 0;
                        $TicketID = $ticket->getId();
                        $TicketCurrentStep = $ticket->getCurrentStep();
                        $CurrentStaffQ = "SELECT `ost_help_topic_flow`.`staff_id` FROM `ost_help_topic_flow` 
                        INNER JOIN `ost_ticket` ON `ost_ticket`.topic_id = `ost_help_topic_flow`.help_topic_id 
                        WHERE `ost_ticket`.ticket_id = $TicketID AND `ost_help_topic_flow`.`step_number` = ost_ticket.current_step;";

                        $CurrentStaffs = array();

                        if (($CurrentStaffRes = db_query($CurrentStaffQ)) && db_num_rows($CurrentStaffRes)) {
                            $Res = db_assoc_array($CurrentStaffRes);

                            if (isset($Res) && isset($Res[0]) && $Res[0] !== '') {
                                foreach ($Res as &$value) {
                                    array_push($CurrentStaffs, (int)$value['staff_id']);
                                }
                            }
                        }
                      //  echo $CurrentStaffs[0];
  //Yaseen
  $CurrentStep = $ticket->ht['current_step'];
  if ($CurrentStaffs && in_array($thisstaff->getId(), $CurrentStaffs)) { ?>
      <input id="is_done_checkbox"  style="vertical-align: middle;margin-left: 1em;" type="checkbox" name="is_done" value="Done" onclick="check();" />
      <label for="is_done_checkbox"><?php echo __('Done From Me');?></label>
      <?php if ( $CurrentStep >1) {?>
      <input id="privious_checkbox"  style="vertical-align: middle;margin-left: 1em;" type="checkbox" name="Privious_step" value="Privious"  onclick="check2();"/>
      <label for="privious_checkbox"><?php echo __('Privious Step');?></label>
  <?php }} ?>

</td>
</tr>
</tbody>
</table>
<!-- Yaseen -->
<script type="text/javascript">
function check() {
if($('#is_done_checkbox').is(':checked')){
$('#privious_checkbox').prop('checked', false);
}
}
function check2() {
if($('#privious_checkbox').is(':checked')){
$('#is_done_checkbox').prop('checked', false);
}
}
</script>

        <p  style="text-align:center;">
            <input class="save pending" type="submit" value="<?php echo __('Post Reply');?>" name="sub" id="sub">
            <input class="" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" class="hidden tab_content spellcheck exclusive save"
        data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
        data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
        action="tickets.php?id=<?php echo $ticket->getId(); ?>#note"
        name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime() * 60; ?>">
        <input type="hidden" name="a" value="postnote">
        <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong><?php echo __('Internal Note'); ?>:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em"><?php
                        echo __('Note title - summary of the note (optional)'); ?></div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <div class="error"><?php echo $errors['note']; ?></div>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="<?php echo __('Note details'); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.note', $ticket->getId(), $info['note']);
    echo $attrs; ?>><?php echo $_POST ? $info['note'] : $draft;
                        ?></textarea>
                <div class="attachments">
                <?php
                    print $note_form->getField('attachments')->render();
                ?>
                </div>
                </td>
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120">
                    <label><?php echo __('Ticket Status');?>:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="note_status_id">
                        <?php
                        $statusId = $info['note_status_id'] ?: $ticket->getStatusId();
                        $states = array('open');
                        
                        ///////rahaf
                        
                        // if ($ticket->isCloseable() === true
                        //         && $role->hasPerm(Ticket::PERM_CLOSE))
                        //    $states = array_merge($states, array('closed'));


                        foreach (TicketStatusList::getStatuses(
                                    array('states' => $states)) as $s) {
                            if (!$s->isEnabled()) continue;
                            $selected = $statusId == $s->getId();
                            echo sprintf('<option value="%d" %s>%s%s</option>',
                                    $s->getId(),
                                    $selected ? 'selected="selected"' : '',
                                    __($s->getName()),
                                    $selected ? (' ('.__('current').')') : ''
                                    );
                        }
                        ?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['note_status_id']; ?></span>
                </td>
            </tr>
        </table>
      
       <p style="text-align:center;">
           <input class="save pending" type="submit" value="<?php echo __('Post Note');?>">
           <input class="" type="reset" value="<?php echo __('Reset');?>">
       </p>
   </form>
 </div>
 <?php
 }?>
 </div>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3><?php echo __('Ticket Print Options');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>"
        method="post" id="print-form" name="print-form" target="_blank">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label class="fixed-size" for="notes"><?php echo __('Print Notes');?>:</label>
            <label class="inline checkbox">
            <input type="checkbox" id="notes" name="notes" value="1"> <?php echo __('Print <b>Internal</b> Notes/Comments');?>
            </label>
        </fieldset>
        <fieldset>
            <label class="fixed-size" for="psize"><?php echo __('Paper Size');?>:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; <?php echo __('Select Print Paper Size');?> &mdash;</option>
                <?php
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach(Export::$paper_sizes as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', __($v));
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Print');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>claim</b> (self assign) %s?'), __('this ticket'));?>
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>answered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <b>unanswered</b>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        <?php echo __('Are you sure you want to flag the ticket as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>ban</b> %s?'), $ticket->getEmail());?> <br><br>
        <?php echo __('New tickets from the email address will be automatically rejected.');?>
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>remove</b> %s from ban list?'), $ticket->getEmail()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        <?php echo sprintf(__('Are you sure you want to <b>unassign</b> ticket from <b>%s</b>?'), $ticket->getAssigned()); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <?php echo sprintf(Format::htmlchars(__('%s <%s> will longer have access to the ticket')),
            '<b>'.Format::htmlchars($ticket->getName()).'</b>', Format::htmlchars($ticket->getEmail())); ?>
        </span>
        <?php echo sprintf(__('Are you sure you want to <b>change</b> ticket owner to %s?'),
            '<b><span id="newuser">this guy</span></b>'); ?>
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong><?php echo sprintf(
            __('Are you sure you want to DELETE %s?'), __('this ticket'));?></strong></font>
        <br><br><?php echo __('Deleted data CANNOT be recovered, including any associated attachments.');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons pull-left">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('OK');?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });

    $(document).on('click', 'a.manage-collaborators', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, 201, function (xhr) {
           var resp = $.parseJSON(xhr.responseText);
           if (resp.user && !resp.users)
              resp.users.push(resp.user);
            // TODO: Process resp.users
           $('.tip_box').remove();
        }, {
            onshow: function() { $('#user-search').focus(); }
        });
        return false;
     });

    // Post Reply or Note action buttons.
    $('a.post-response').click(function (e) {
        var $r = $('ul.tabs > li > a'+$(this).attr('href')+'-tab');
        if ($r.length) {
            // Make sure ticket thread tab is visiable.
            var $t = $('ul#ticket_tabs > li > a#ticket-thread-tab');
            if ($t.length && !$t.hasClass('active'))
                $t.trigger('click');
            // Make the target response tab active.
            if (!$r.hasClass('active'))
                $r.trigger('click');

            // Scroll to the response section.
            var $stop = $(document).height();
            var $s = $('div#response_options');
            if ($s.length)
                $stop = $s.offset().top-125

            $('html, body').animate({scrollTop: $stop}, 'fast');
        }

        return false;
    });

  $('#show_ccs').click(function() {
    var show = $('#arrow-icon');
    var collabs = $('a#managecollabs');
    $('#ccs').slideToggle('fast', function(){
        if ($(this).is(":hidden")) {
            collabs.hide();
            show.removeClass('icon-caret-down').addClass('icon-caret-right');
        } else {
            collabs.show();
            show.removeClass('icon-caret-right').addClass('icon-caret-down');
        }
    });
    return false;
   });

  $('.collaborators.noclick').click(function() {
    $('#show_ccs').trigger('click');
   });

  $('#collabselection').select2({
    width: '350px',
    allowClear: true,
    sorter: function(data) {
        return data.filter(function (item) {
                return !item.selected;
                });
    },
    templateResult: function(e) {
        var $e = $(
        '<span><i class="icon-user"></i> ' + e.text + '</span>'
        );
        return $e;
    }
   }).on("select2:unselecting", function(e) {
        if (!confirm(__("Are you sure you want to DISABLE the collaborator?")))
            e.preventDefault();
   }).on("select2:selecting", function(e) {
        if (!confirm(__("Are you sure you want to ENABLE the collaborator?")))
             e.preventDefault();
   }).on('change', function(e) {
    var id = e.currentTarget.id;
    var count = $('li.select2-selection__choice').length;
    var total = $('#' + id +' option').length;
    $('.' + id + '__count').html(count);
    $('.' + id + '__total').html(total);
    $('.' + id + '__total').parent().toggle((total));
   }).on('select2:opening select2:closing', function(e) {
    $(this).parent().find('.select2-search__field').prop('disabled', true);
   });
});
</script>
<?php if(isset( $_SESSION["ticket_page_number"])){
?>
<button type="button"><a href="tickets.php?sort=&order=&p=<?php echo  $_SESSION["ticket_page_number"]; ?>">Back</a></button>
<?php
}
else{
  ?>
  <button type="button"><a href="tickets.php">Back</a></button>
  <?php
}
function CallAPI($phones,$msg)
{
    $phones = '963' . trim($phones);
    // $msg = urlencode($msg);
    $url = "https://services.mtnsyr.com:7443/General/MTNSERVICES/ConcatenatedSender.aspx?User=mab687&Pass=ocbam4141&From=MABCO&Lang=0&Msg=$msg&Gsm=$phones";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $ch = curl_exec($ch);
    return $ch;
}