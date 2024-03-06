<?php
/*********************************************************************
    open.php

    New tickets handle.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');

 date_default_timezone_set("Asia/Damascus");

define('SOURCE','Web'); //Ticket source.
$ticket = null;
$errors=array();
$ht_id=array();
if ($_POST) {
    
    $sql="SELECT `help_topic_id` FROM `ost_help_topic_external_approval` WHERE 1";
    if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
        while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
            array_push($ht_id, $RecurringTaskID);
           
        }
    }
    $vars = $_POST;

    $HelpTopicID = $vars['topicId'];
    $StaffID = 0;
    $ActiveSLA = 0;
if(in_array($HelpTopicID,$ht_id)){
    if(!isset($_POST['external_approval'])){
    $errors['err'] = __('This Help Topic Need external approval'); 
    } 
}
    $StaffID_Q = 'SELECT `staff_id`, `active_sla` FROM `ost_help_topic` WHERE topic_id = ' . $HelpTopicID . ' AND `staff_id` != 0';

    if(($StaffID_Res = db_query($StaffID_Q)) && db_num_rows($StaffID_Res)) {
        $Res = db_fetch_row($StaffID_Res);

        if ($Res[0] && $Res[0] > 0) {
            $StaffID = $Res[0];
        }
    }

    $TimeMapCount = 0;
    
    if ($StaffID > 0) {
        $ActiveSLA = $Res[1];

        $TimeMapCountQ = 'SELECT COUNT(`tm_id`) FROM `ost_time_map` WHERE `tm_staff_id` = ' . $StaffID;

        if(($TimeMapCountRes = db_query($TimeMapCountQ)) && db_num_rows($TimeMapCountRes)) {
            $Res = db_fetch_row($TimeMapCountRes)[0];

            if ($Res[0] && $Res[0] > 0) {
                $TimeMapCount = $Res[0];
            }
        }
    }

    $LastTicketEstDueTime = $TimeMapEndTime = "";
    
    if ($StaffID != 0 && $ActiveSLA != 0 && $TimeMapCount > 0) {
        $LastTicketDueTime_Q = 'SELECT `est_duedate` FROM `ost_ticket` GROUP BY `ticket_id` HAVING MAX(`ticket_id`) = (SELECT MAX(`ticket_id`) FROM `ost_ticket` WHERE `staff_id` = ' . $StaffID . ')';

        if(($LastTicketDueTime_Res = db_query($LastTicketDueTime_Q)) && db_num_rows($LastTicketDueTime_Res)) {
            $Res = db_fetch_row($LastTicketDueTime_Res);

            if ($Res[0] && $Res[0] != "") {
                $LastTicketEstDueTime = $Res[0];
            }

            $TimeMapEndTime_Q = 'SELECT `tm_end_time` FROM `ost_time_map` WHERE `tm_object_type` LIKE \'T\' AND DATE_FORMAT(\'' . $LastTicketEstDueTime . '\', "%k:%i:%S") BETWEEN `tm_start_time` AND `tm_end_time` ORDER BY `tm_id` LIMIT 1';
            
            if(($TimeMapEndTime_Res = db_query($TimeMapEndTime_Q)) && db_num_rows($TimeMapEndTime_Res)) {
                $Res = db_fetch_row($TimeMapEndTime_Res);

                if ($Res[0] && $Res[0] != "") {
                    $TimeMapEndTime = $Res[0];
                }
            }
        }
    }
    
    if ($LastTicketEstDueTime != "" && $TimeMapEndTime != "") {
        $StartTime = new DateTime($LastTicketEstDueTime);
        $EndTime = new DateTime($TimeMapEndTime);
        
        $StartTime = $StartTime->format('H:i:s');
        $StartTime = new DateTime($StartTime);

        $Difference = $StartTime->diff($EndTime);
        $MinutesDifference = $Difference->format('%I');

        $vars['scheduled'] = 0;
        $vars['new_est_duedate'] = null;

        if ($MinutesDifference > 5) {
            $dt = new DateTime($LastTicketEstDueTime);
            $vars['scheduled'] = 1;
            $vars['new_est_duedate'] = $dt->add(new DateInterval('PT' . $ActiveSLA . 'M'))->format('Y-m-d H:i:s');
        } else {
            $GetNextStartTimeMap_Q = 'SELECT `tm_start_time` FROM `ost_time_map` WHERE `tm_start_time` > "' . $TimeMapEndTime . '" AND `tm_object_type` LIKE \'T\' LIMIT 1';
            
            if(($GetNextStartTimeMap_Res = db_query($GetNextStartTimeMap_Q)) && db_num_rows($GetNextStartTimeMap_Res)) {
                $Res = db_fetch_row($GetNextStartTimeMap_Res);
                
                if ($Res[0] && $Res[0] != "") {
                    $dt = new DateTime($Res[0]);
                    $vars['scheduled'] = 1;
                    $vars['new_est_duedate'] = $dt->add(new DateInterval('PT' . $ActiveSLA . 'M'))->format('Y-m-d H:i:s');
                }
            } elseif (db_num_rows($GetNextStartTimeMap_Res) == 0) {
                $GetFirstStartTimeMap_Q = 'SELECT `tm_start_time` FROM `ost_time_map` WHERE `tm_staff_id` = ' . $StaffID . ' ORDER BY `tm_start_time` ASC;';

                if(($GetFirstStartTimeMap_Res = db_query($GetFirstStartTimeMap_Q)) && db_num_rows($GetFirstStartTimeMap_Res)) {
                    $Res = db_fetch_row($GetFirstStartTimeMap_Res);
                    
                    if ($Res[0] && $Res[0] != "") {
                        $FirstStartTime = $vars['new_est_duedate'];
                        
                        $GetNextStartTimeMap_Q = 'SELECT `tm_start_time` FROM `ost_time_map` WHERE `tm_start_time` > "' . $FirstStartTime . '" AND `tm_object_type` LIKE \'T\' LIMIT 1';
            
                        if(($GetNextStartTimeMap_Res = db_query($GetNextStartTimeMap_Q)) && db_num_rows($GetNextStartTimeMap_Res)) {
                            $Res = db_fetch_row($GetNextStartTimeMap_Res);
                            
                            if ($Res[0] && $Res[0] != "") {
                                $Res[0] = date('Y-m-d H:i:s', strtotime($Res[0] . ' +1 day'));
                                $dt = new DateTime($Res[0]);
                                $vars['scheduled'] = 1;
                                $vars['new_est_duedate'] = $dt->add(new DateInterval('PT' . $ActiveSLA . 'M'))->format('Y-m-d H:i:s');
                            }
                        }
                    }
                }
            }
        }
    }

    $vars['deptId'] = $vars['emailId'] = 0; // Just Making sure we don't accept crap...only topicId is expected.
    
    if ($thisclient) {
        $vars['uid'] = $thisclient->getId();
    } elseif($cfg->isCaptchaEnabled()) {
        if(!$_POST['captcha'])
            $errors['captcha'] = __('Enter text shown on the image');
        elseif(strcmp($_SESSION['captcha'], md5(strtoupper($_POST['captcha']))))
            $errors['captcha']=sprintf('%s - %s', __('Invalid'), __('Please try again!'));
    }

    $vars['uid'] = $thisclient->getId();

    $tform = TicketForm::objects()->one()->getForm($vars);
    $messageField = $tform->getField('message');
    $attachments = $messageField->getWidget()->getAttachments();

    if (!$errors && $messageField->isAttachmentsEnabled())
        $vars['files'] = $attachments->getFiles();

    // Drop the draft.. If there are validation errors, the content
    // submitted will be displayed back to the user
    Draft::deleteForNamespace('ticket.client.'.substr(session_id(), -12));
    //Ticket::create...checks for errors..

    if ($vars['to-user'] && count($vars['to-user'])) {
        $vars['ccs'] = $vars['to-user'];
        $vars['to_user_id'] = $vars['to-user'][0];
    }

    if (($ticket = Ticket::create($vars, $errors, SOURCE))) {
        if(isset($_POST['external_approval'])){
            // echo $_POST['external_approval'];
            $sql="UPDATE `ost_ticket` SET `external_approval`= 1 WHERE `ticket_id`=".$ticket->getId();
            db_query($sql);
        }
          //Yaseen
          $GetThreadID_Q = 'SELECT `id` FROM `ost_thread` WHERE `object_id` = ' . $ticket->getId() .' and object_type="T"';
            
          if(($GetThreadID_Res = db_query($GetThreadID_Q)) && db_num_rows($GetThreadID_Res)) {
              $Res = db_fetch_row($GetThreadID_Res);
  
              if ($Res[0] && $Res[0] != "") {
                  $ThreadID = $Res[0];
              }
          }
          $GetHelpTopic_Q = 'SELECT topic_id from ost_ticket where ticket_id = ' . $ticket->getId() .'';
            
          if(($GetHelpTopic_Res = db_query($GetHelpTopic_Q)) && db_num_rows($GetHelpTopic_Res)) {
              $Res = db_fetch_row($GetHelpTopic_Res);
  
              if ($Res[0] && $Res[0] != "") {
                  $HelpTopic = $Res[0];
              }
          }
         
          if(isset($_POST['add_user'])){
              if (isset($_POST['select_sec_user']))
              {
                   $sql02="INSERT INTO ost_help_topic_flow (`id`,`help_topic_id`,`step_number`,`staff_id`,`team_id`,`ticket_id`,`user_id`) SELECT max(id)+1,' . $HelpTopic . ',1,0,0,".$ticket->getId().",".$_POST['select_first_user']." from ost_help_topic_flow";
                  db_query($sql02); 
                  $sql03="INSERT INTO ost_help_topic_flow (`id`,`help_topic_id`,`step_number`,`staff_id`,`team_id`,`ticket_id`,`user_id`) SELECT max(id)+1,' . $HelpTopic . ',2,0,0,".$ticket->getId()." ,".$_POST['select_sec_user']." from ost_help_topic_flow";
                  db_query($sql03); 
                  $sql12="INSERT Into ost_thread_collaborator (`id`,`flags`,`thread_id`,`user_id`,`role`,`created`,`updated`,`team_id`) select max(id)+1, 3,". $ThreadID ." , " . $thisclient->getId() . ",'M',CURDATE(),CURDATE(),'' from ost_thread_collaborator ";
                  db_query($sql12); 
                //   $sql13="INSERT Into ost_thread_collaborator select max(id)+1, 3,". $ThreadID ." , " . $_POST['select_first_user']. ",'M',CURDATE(),CURDATE(),'' from ost_thread_collaborator ";
                //   db_query($sql13); 
                  $sql14="INSERT Into ost_thread_collaborator (`id`,`flags`,`thread_id`,`user_id`,`role`,`created`,`updated`,`team_id`) select max(id)+1, 3,". $ThreadID ." , " . $_POST['select_sec_user']. ",'M',CURDATE(),CURDATE(),'' from ost_thread_collaborator ";
                  db_query($sql14); 
                  if($HelpTopic == 127 ||$HelpTopic == 174 )
                  {
                      $sql15="INSERT Into ost_thread_collaborator (`id`,`flags`,`thread_id`,`user_id`,`role`,`created`,`updated`,`team_id`) select max(id)+1, 3,". $ThreadID ." , 50,'M',CURDATE(),CURDATE(),'' from ost_thread_collaborator ";
                      db_query($sql15); 
                      $sql16="INSERT Into ost_thread_collaborator (`id`,`flags`,`thread_id`,`user_id`,`role`,`created`,`updated`,`team_id`) select max(id)+1, 3,". $ThreadID ." , 138,'M',CURDATE(),CURDATE(),'' from ost_thread_collaborator ";
                      db_query($sql16); 
                  }
              }
          }
        $msg = __('Support ticket request created');
        // Drop session-backed form data
        unset($_SESSION[':form-data']);
        //Logged in...simply view the newly created ticket.
        if($thisclient && $thisclient->isValid()) {
            session_write_close();
            session_regenerate_id();

            $TeamID = $ticket->ht['team_id'];
            $Body = $ticket->last_message->ht['body'];

            $GetTeamMembersQ = "SELECT `staff_id` FROM `ost_team_member` WHERE `team_id` = $TeamID";
            
            if (($GetTeamMembers_Res = db_query($GetTeamMembersQ)) && db_num_rows($GetTeamMembers_Res)) {
                while (list($StaffID) = db_fetch_row($GetTeamMembers_Res)) {
                    Ticket::SendPushNotification($ticket->ht['ticket_id'], $Body, 0, $StaffID);
                }
            }

            if (array_key_exists("scheduled", $vars) && array_key_exists("new_est_duedate", $vars)) {
                if (($vars['scheduled'] == 1 && $vars['new_est_duedate'] != null) || $HelpTopicID == 64) {
                    $GetThreadID_Q = 'SELECT `id` FROM `ost_thread` WHERE `object_id` = ' . $ticket->getId();
            
                    if(($GetThreadID_Res = db_query($GetThreadID_Q)) && db_num_rows($GetThreadID_Res)) {
                        $Res = db_fetch_row($GetThreadID_Res);
        
                        if ($Res[0] && $Res[0] != "") {
                            $NewThreadID = $Res[0];
                            $GetThreadID_Q = "INSERT INTO `ost_thread_entry` (`pid`, `thread_id`, `staff_id`, `user_id`, `type`, `flags`, `poster`, `source`, `title`, `body`, `format`, `ip_address`, `recipients`, `created`, `updated`) VALUES ('0', '$NewThreadID', '0', '0', 'M', '65', 'SYSTEM', '', 'Important Notice', 'Ticket is auto-scheduled at " . $vars['new_est_duedate'] . "', 'html', '::1', NULL, NOW(), NOW());";
                            db_query($GetThreadID_Q);
                        }
                    }
                    
                    @header('Location: tickets.php?id=' . $ticket->getId());
                } else {
                    if (date('H') + 2 < 17 || $HelpTopicID == 64) {
                        @header('Location: tickets.php?id='.$ticket->getId());
                    }
                }
            } else {
                if (date('H') + 2 < 17 || $HelpTopicID == 64) {
                    @header('Location: tickets.php?id='.$ticket->getId());
                }
            }
        }
    } else {
        $errors['err'] = $errors['err'] ? : sprintf('%s %s', __('Unable to create a ticket.'), __('Correct any errors below and try again.'));
    }

    echo "<script>window.history.replaceState({}, document.title, window.location.toString());</script>";
}

//page
$nav->setActiveNav('new');
if ($cfg->isClientLoginRequired()) {
    if ($cfg->getClientRegistrationMode() == 'disabled') {
        Http::redirect('view.php');
    } elseif (!$thisclient) {
        require_once 'secure.inc.php';
    } elseif ($thisclient->isGuest()) {
        require_once 'login.php';
        exit();
    }
}

require(CLIENTINC_DIR.'header.inc.php');

if ($ticket && ((($topic = $ticket->getTopic()) && ($page = $topic->getPage()))|| ($page = $cfg->getThankYouPage()))) {
    // Thank the user and promise speedy resolution!
    echo Format::viewableImages(
        $ticket->replaceVars(
            $page->getLocalBody()
        ),
        ['type' => 'P']
    );
} else {
    require(CLIENTINC_DIR . 'open.inc.php');
}

require(CLIENTINC_DIR . 'footer.inc.php');
?>