<?php

/*************************************************************************
    tasks.php

    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

require('staff.inc.php');

session_start();

$page = '';

//Navigation
$nav->setTabActive('masseges');
$open_name = _P(
    'queue-name',
    /* This is the name of the open tasks queue */
    'Masseges'
);
// echo $queue_name;

$nav->addSubMenu(array(
    'desc' => $open_name,
    'title' => __('UnRead Masseges'),
    'href' => '?masseges',
    'iconclass' => 'Ticket'
));

$nav->addSubMenu(array(
    'desc' => 'Readed Masseges',
    'title' => __('Readed Masseges'),
    'href' => '?readedmasseges',
    'iconclass' => 'answeredTickets'
));

$nav->addSubMenu(array(
    'desc' => "New Message",
    'title' => __('New Message'),
    'href' => '?cm=cm',
    'iconclass' => 'newTicket'
));

if($thisstaff->isManager()){
    $nav->addSubMenu(array(
        'desc' => "My Staff Messages",
        'title' => __('My Staff Messages'),
        'href' => '?maneger=maneger',
        'iconclass' => 'departments'
    ));
}
$result = array();
            $sql="SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=".$thisstaff->getId();
           
            if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
                    array_push($result, $RecurringTaskID);
                   
                }
            }
            if(count($result)>0){
                $nav->addSubMenu(array(
                    'desc' => "My Showrooms Messages",
                    'title' => __('My Showrooms Messages'),
                    'href' => '?Showrooms=Showrooms',
                    'iconclass' => 'helpTopics'
                ));
            }


//create new message 
// if (isset($_POST["str1"])) {
//     unset($_GET["cm"]);
// }
//     $staffID = null;
//     $teamId = null;
//     $cc1 = null;
//     $cc2 = null;
//     $cc3 = null;
//     $departmentid = null;
//     $userId = null;
//     $name = null;
//     $gender = 1;
    
//     if (isset($_POST['ddlViewst'])) {
//         $staffID = $_POST['ddlViewst'];
//         // echo "staffID: " . $staffID . "<br>";
//     } else {
//         $staffID = "Null";
//     }
//     if (isset($_POST['ddlViewteam'])) {
//         $teamId = $_POST['ddlViewteam'];
//         // echo "teamId: " . $teamId . "<br>";
//     } else {
//         $teamId = "Null";
//     }
//     if (isset($_POST['ddlViewcc1'])) {
//         $cc1 = $_POST['ddlViewcc1'];
//         // echo "cc1: " . $cc1 . "<br>";
//     } else {
//         $cc1 = "Null";
//     }
//     if (isset($_POST['ddlViewcc2'])) {
//         $cc2 = $_POST['ddlViewcc2'];
//         // echo "cc2: " . $cc2 . "<br>";
//     } else {
//         $cc2 = "Null";
//     }
//     if (isset($_POST['ddlViewcc3'])) {
//         $cc3 = $_POST['ddlViewcc3'];
//         // echo "cc3: " . $cc3 . "<br>";
//     } else {
//         $cc3 = "Null";
//     }
//     if (isset($_POST['ddlViewuser'])) {
//         $user = $_POST['ddlViewuser'];
//         // echo "user: " . $user . "<br>";
//     } else {
//         $user = "Null";
//     }
//     if (isset($_POST['ddlViewDep'])) {
//         $departmentid = $_POST['ddlViewDep'];
//         // echo "departmentid: " . $departmentid . "<br>";
//     } else {
//         $departmentid = "Null";
//     }
//     if (empty($_POST["name"])) {
//         $nameErr = "Name is required";
//         $name = "Null";
//     } else {
  
//         $name = $_POST["name"];
//         if(preg_match('/\s/', substr($name, 0, 50))) {
//             // has whitespace
//             $Newname="No Subject".$name;
//             $name=$Newname;
//         }
//         // echo "name: " . $name . "<br>";
//     }
//     if (empty($_POST["no"])) {
//         $nameErr = "Name is required";
//         $gender = 1;
//     } else {
  
//         $gender = 0;
//         // echo "name: " . $name . "<br>";
//     }
//     $sqlgetmaxnumber = "SELECT MAX(CAST(`number` AS INT)) FROM `ost_massage`";
//     if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
  
//         $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
//     }
//   if(($staffID != "Null" ||   $teamId!="Null" || $user!= "Null" ) && $name!="Null"){
  
//     $sql = 'INSERT INTO `ost_massage` ( `number`, `dept_id`, `staff_id`, `assignor_id`, `team_id`,`collab_1`,`collab_2`,`collab_3`,`created`,`user_id`,`title`,`is_private`) VALUES 
//                                     ("' . ((int)$newnumber[0] + 1) . '",' . $departmentid . ',' . $staffID . ',' . $thisstaff->getId() . ',' . $teamId . ',' . $cc1 . ',' . $cc2 . ',' . $cc3 . ',Now(),' . $user . ',"' . str_replace('"', '', $name) . '",' . $gender . ')';
//     // echo $sql;
  
//     $GetStaffNameQ = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `staff_id` = ".$thisstaff->getId();
  
//     if (($GetStaffName_Res = db_query($GetStaffNameQ)) && db_num_rows($GetStaffName_Res)) {
//         $Title= db_fetch_row($GetStaffName_Res)[0];
//     }
//     if (db_query($sql)) {
//         unset($_GET["cm"]);
//     }
//     if($staffID != null){
//         $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
//         if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
    
//             $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
//         }
       
//         Ticket::SendPushNotificationMsg($name,0,$staffID,$newnumber[0] ,$Title);
//     }
//     if($user != null){
//         $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
//         if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
    
//             $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
//         }
//         Ticket::SendPushNotificationMsg($name,$user,0,$newnumber[0] ,$Title);
//     }
    
//         $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
//         if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
    
//             $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
//         }
//      $sql = 'INSERT INTO `ost_file_messages` (`id`, `msg_id`, `file_id`) 
//      VALUES (NULL, ' .$newnumber[0] . ','  . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ')';
//       if (db_query($sql)) {
//           $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
//           db_query($sqll);
  
//        } 
//     GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);
  
    
    
//   }else{
    
//     $errors['err'] = __('something went wrong plese make sure you enter the message body and assignor');
//   ?>
        <!-- <div id="msg_error"><?php// echo $errors['err']; ?></div>--><?php 
       
//   }   
  
// }

//forward message
if (isset($_POST["str2"])) {
    $staffID = null;
    $teamId = null;
    $cc1 = null;
    $cc2 = null;
    $cc3 = null;
    $departmentid = null;
    $userId = null;
    $name = null;
    $gender = 1;
    if (isset($_POST['ddlViewst'])) {
        $staffID = $_POST['ddlViewst'];
        // echo "staffID: " . $staffID . "<br>";
    } else {
        $staffID = "Null";
    }
    if (isset($_POST['ddlViewteam'])) {
        $teamId = $_POST['ddlViewteam'];
        // echo "teamId: " . $teamId . "<br>";
    } else {
        $teamId = "Null";
    }
    if (isset($_POST['ddlViewcc1'])) {
        $cc1 = $_POST['ddlViewcc1'];
        // echo "cc1: " . $cc1 . "<br>";
    } else {
        $cc1 = "Null";
    }
    if (isset($_POST['ddlViewcc2'])) {
        $cc2 = $_POST['ddlViewcc2'];
        // echo "cc2: " . $cc2 . "<br>";
    } else {
        $cc2 = "Null";
    }
    if (isset($_POST['ddlViewcc3'])) {
        $cc3 = $_POST['ddlViewcc3'];
        // echo "cc3: " . $cc3 . "<br>";
    } else {
        $cc3 = "Null";
    }
    if (isset($_POST['ddlViewuser'])) {
        $user = $_POST['ddlViewuser'];
        // echo "user: " . $user . "<br>";
    } else {
        $user = "Null";
    }
    if (isset($_POST['ddlViewDep'])) {
        $departmentid = $_POST['ddlViewDep'];
        // echo "departmentid: " . $departmentid . "<br>";
    } else {
        $departmentid = "Null";
    }
    // if (empty($_POST["name"])) {
    //     $nameErr = "Name is required";
    //     $name = "Null";
    // } else {

    //     $name = $_POST["name"];
    //     // echo "name: " . $name . "<br>";
    // }
    if (empty($_POST["no"])) {
        $nameErr = "Name is required";
        $gender = 1;
    } else {

        $gender = 0;
        // echo "name: " . $name . "<br>";
    }
    $sqlgetmaxnumber = "SELECT MAX(CAST(`number` AS INT)) FROM `ost_massage`";
    if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {

        $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
    }
    if((($staffID != "Null" && $$departmentid!= "Null")  ||   ($teamId!="Null"&& $$departmentid!= "Null") || $user!= "Null" )){
    $sql = 'INSERT INTO `ost_massage` ( `number`, `dept_id`, `staff_id`, `assignor_id`, `team_id`,`collab_1`,`collab_2`,`collab_3`,`created`,`user_id`,`title`,`is_private`,`TheTitle`) VALUES 
                                    ("' . ((int)$newnumber[0] + 1) . '",' . $departmentid . ',' . $staffID . ',' . $thisstaff->getId() . ',' . $teamId . ',' . $cc1 . ',' . $cc2 . ',' . $cc3 . ',Now(),' . $user . ',"' . $_SESSION["Subject"]. '",' . $gender . ',"' . $_SESSION["TT"]. '")';
    echo $sql;

    if (db_query($sql)) {
        if($_SESSION["FileID"] != "NULL"){
        $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
        if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
    
            $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
        }
        $sql = 'INSERT INTO `ost_file_messages` (`id`, `msg_id`, `file_id`) 
        VALUES (NULL, ' .$newnumber[0] . ','  . $_SESSION["FileID"]   . ')';
         if (db_query($sql)) {
             $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".$_SESSION["FileID"].", NULL, '0', NULL)";
             db_query($sqll);
     
          } 
        }

        unset($_GET["forword"]);
    }


}else{
    $errors['err'] = __('something went wrong plese make sure you enter  assignor');
    ?>
          <div id="msg_error"><?php echo $errors['err']; ?></div><?php
         
}
}
//reply to message
if (isset($_POST["Submit1"])) {
   
     
$sqlgetmaxnumber = "SELECT MAX(CAST(`number` AS INT)) FROM `ost_massage`";
if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {

    $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
}

if(!empty($_POST["replytext"])){
    
$sql = 'INSERT INTO `ost_massage` ( `number`, `dept_id`, `staff_id`, `assignor_id`, `team_id`,`collab_1`,`collab_2`,`collab_3`,`created`,`title`,`reply`,`user_id`,`TheTitle`) VALUES 
("' . ((int)$newnumber[0] + 1) . '",' . $_SESSION["Dep"] . ',' .  $_SESSION["FromStaffId"] . ',' . $thisstaff->getId() . ',' . $_SESSION["Toteam"] . ',' . $_SESSION["cc1"] . ',' . $_SESSION["cc2"] . ',' . $_SESSION["cc3"] . ',Now()' .  ',"' . str_replace('"', '', $_POST["replytext"]) . '","' . $_SESSION["Number"] . '",'. $_SESSION["userid"] .  ',"' . str_replace('"', '', $_POST["title"]) . '")';
echo $sql;
// db_query($sql);
if (db_query($sql)) {
    unset($_GET["reply"]);
}

$sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
  if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {

      $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
  }
if( array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0] != null){
$sql = 'INSERT INTO `ost_file_messages` (`id`, `msg_id`, `file_id`) 
VALUES (NULL, ' .$newnumber[0] . ','  . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ')';
if (db_query($sql)) {
    $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
    db_query($sqll);

 } 
GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);

}
// unset($_GET["reply"]);
}
else{
unset($_GET["reply"]);
$errors['err'] = __(' Plese make sure you enter Reply title and message');
?>
      <div id="msg_error"><?php echo $errors['err']; ?></div><?php
     
} 
}
require_once(STAFFINC_DIR . 'header.inc.php');
//view all messages 
$orderWays = array('DESC' => '-', 'ASC' => '');
if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $negorder = $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $negorder = $order = 'ASC';
}
if (!isset($_GET["cm"]) && !isset($_GET["id"]) && !isset($_GET["forword"]) && !isset($_GET["download"]) && !isset($_GET["readedmasseges"]) && !isset($_GET["maneger"])  && !isset($_GET["Showrooms"])) {
    // $results = ucfirst($status) . ' ' . __('Masseges');
    
    $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
    if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
        $order = $orderWays[strtoupper($_REQUEST['order'])];
    } else {
        $order = 'ASC';
    }
    
?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Masseges'); ?>
        </a>
    </h1>
    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
        <thead>
            <tr>
                <th width="20">
                <a href="masseges.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('#'); ?>&nbsp;</a>
                </th>
                <th width="100">
                <a href="masseges.php?sort=from_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'From Agent'"> <?php echo __('From'); ?>&nbsp;</a>
                </th>
                <th width="120">
                <a href="masseges.php?sort=subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
                </th>
                <th width="120">
                <a href="masseges.php?sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
                </th>
                <th width="120">
                <a href="masseges.php?sort=to_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To Agent'"><?php echo __('To'); ?>&nbsp;</a>
                </th>
                <th width="110">
                <a href="masseges.php?sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
                </th>
                
               

                <th width="75">
                <a href="masseges.php?sort=team&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Team"><?php echo __('Team'); ?>&nbsp;</a>
                </th>
                <th width="75">
                   <?php echo __('CC'); ?>
                </th>
                <th width="75">
                <a href="masseges.php?sort=to_user&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To User'"><?php echo __('To User'); ?>&nbsp;</a>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ((sizeof($thisstaff->getTeams())) != 0 ){
                $teams = $thisstaff->getTeams();
                $where[] = ' OR (`ost_massage`.`team_id` IN(' . implode(',', db_input(array_filter($teams)))
                . ') AND `ost_massage`.`is_private` = 0 ) ';
                // print_r($where);
                // echo $where[0];
            }
            $z = array();
            $sql="SELECT `msg_id` FROM `ost_read_messages` WHERE `staff_id`=".$thisstaff->getId();
            if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
                    array_push($z, $RecurringTaskID);
                   
                }
            }
            if(count($z)>0){
                $where1[] = ' AND  `ost_massage`.`id` NOT IN (SELECT `msg_id` FROM `ost_read_messages` WHERE `staff_id`='.$thisstaff->getId().') '; 
            }
if (isset($_GET["sort"])){
//Sort By Number    
if($_GET["sort"]=="ID"){
    if($_GET["order"]=="DESC"){
        $OrderBy = " `ost_massage`.`number` DESC";
    }
elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`number` ASC";
}
}

//Sort By subject

if($_GET["sort"]=="subject"){
    if($_GET["order"]=="DESC"){
        $OrderBy = "  `ost_massage`.`TheTitle`  DESC";
    }
    elseif ($_GET["order"]=="ASC") {
        $OrderBy= "  `ost_massage`.`TheTitle`  ASC";
    }
}

//Sort By To Agent   
if($_GET["sort"]=="to_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  t.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  t.`firstname`  ASC";
   }
   }

   //Sort By from Agent   
if($_GET["sort"]=="from_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  f.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  f.`firstname`  ASC";
   }
   }

//Sort By team   
if($_GET["sort"]=="team"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  `ost_team`.`name`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  `ost_team`.`name`  ASC";
   }
   }



if($_GET["sort"]=="date"){
    if($_GET["order"]=="DESC"){
        $OrderBy = "  `ost_massage`.`created`  DESC";
    }
    elseif ($_GET["order"]=="ASC") {
        $OrderBy= "  `ost_massage`.`created`  ASC";
    }
  }
    if($_GET["sort"]=="dept"){
        if($_GET["order"]=="DESC"){
            $OrderBy = "  `ost_department`.`name`  DESC";
        }
        elseif ($_GET["order"]=="ASC") {
            $OrderBy = "  `ost_department`.`name`  ASC";
        }
    }
    if($_GET["sort"]=="to_user"){
        if($_GET["order"]=="DESC"){
            $OrderBy = "  touser.`name`  DESC";
        }
        elseif ($_GET["order"]=="ASC") {
            $OrderBy = "  touser.`name`  ASC";
        }
    }
//end sort     
}
else{
  $OrderBy="  `ost_massage`.`created` DESC";
}

$GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`TheTitle`,`ost_department`.`name`
 ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`)
 ,`ost_team`.`name`,
 CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`),
 CONCAT(c3.`firstname`, ' ', c3.`lastname`),touser.`name`,fromuser.`name`,`ost_file_messages`.`file_id`
 FROM `ost_massage` 
 LEFT JOIN `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id`
 LEFT JOIN `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id`
 LEFT JOIN  `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1`
 LEFT JOIN  `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2`
 LEFT JOIN  `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3`
 LEFT JOIN  `ost_team`  ON `ost_team`.`team_id`=`ost_massage`.`team_id`
 LEFT JOIN `ost_user` as touser ON touser.`id`=`ost_massage`.`user_id`
 LEFT JOIN `ost_department` ON  `ost_department`.`id`=`ost_massage`.`dept_id`
 LEFT JOIN `ost_user` as fromuser ON fromuser.`id`=`ost_massage`.`From_user`
 LEFT JOIN  `ost_file_messages` ON `ost_file_messages`.`msg_id`= `ost_massage`.`id`
 LEFT JOIN `ost_file` ON `ost_file`.`id`=`ost_file_messages`.`file_id`
 WHERE  (`ost_massage`.`assignor_id`=". $thisstaff->getId() ." AND `ost_massage`.`id` IN (SELECT `msg_id` FROM `ost_read_messages` WHERE `staff_id`=". $thisstaff->getId() ." )) OR
 
 (`ost_massage`.`staff_id`=" . $thisstaff->getId() . " OR  `ost_massage`.`assignor_id`=" . $thisstaff->getId() .$where[0] . " OR 
 `ost_massage`.`collab_1`=" . $thisstaff->getId() . " OR `ost_massage`.`collab_2`=" . $thisstaff->getId() . " OR `ost_massage`.`collab_3`=" . $thisstaff->getId().
 ") ". $where1[0]."  ORDER BY ". $OrderBy;
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Number, $Created, $Subject, $Dep, $FromStaff, $ToStaff, $team, $cc1, $cc2, $cc3,$touser,$fronuser,$fileID) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    if ($team == null) {
                        $teamID = "No Team";
                    } else {
                        $teamID = $team;
                    }
                    if ($Dep == null) {
                        $DepID = "No Department";
                    } else {
                        $DepID = $Dep;
                    }
                    if ($touser == null) {
                        $ToUser = "No User";
                    } else {
                        $ToUser = $touser;
                    }

                    if ($ToStaff == null) {
                        $ToStaffName = "No Agent";
                    } else {
                        $ToStaffName  = $ToStaff;
                    }
                    if ($FromStaff == null) {
                        $FromStaffName = $fronuser;
                    } else {
                        $FromStaffName  = $FromStaff;
                    }

            ?>
                    <tr id="<?php echo $Number; ?>">
                        <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo $Number; ?></a></td>
                        <td style="font-weight:bold;"><span><?php echo $FromStaffName; ?></span></td>
                        <?Php if($fileID != null ) {?>
                             <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a><?php echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';?></td>
                        <?php } else {?>
                            <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a></td>
                        <?Php }?>
                        <td style='font-weight:bold'><?php echo $Created; ?></td>
                        <td style='font-weight:bold'><span class="truncate"><?php echo $ToStaffName; ?></span></td>
                        <td style='font-weight:bold'><span><?php echo  $DepID; ?></span></td>           
                        <td style='font-weight:bold'><span class="truncate"><?php echo $teamID; ?></span></td>
                        <?php if($cc1 != null ) {?>
                        <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                        <?php }
                        elseif($cc2 != null ){?>
                            <td style='font-weight:bold'><span class="truncate"><?php echo $cc2  ?></span></td>
                            <?php }
                            elseif($cc3 != null ){?>
                                <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                                <?php }
                                else{?>
                                  <td style='font-weight:bold'><span class="truncate"><?php echo "No CC"  ?></span></td>
                                <?php }?>
                                <td style='font-weight:bold'><span class="truncate"><?php echo $ToUser; ?></span></td>

                    </tr>
            <?php
                }
            }

            ?>
        </tbody>
    </table>
<?php  }
//open create new Message
if (isset($_GET["cm"])) {

?>
    <style>
        .splitscreen {
            display: flex;
        }

        .splitscreen .left {
            flex: 1;
        }

        .splitscreen .right {
            flex: 1;
        }
        /* #cc2{
            display:none;
        }
        #cc3{
            display:none;
        } */
    </style>
    <div id="task-form">



        <?php
        if ($info['error']) {
            echo sprintf('<p id="msg_error">%s</p>', $info['error']);
        } elseif ($info['warning']) {
            echo sprintf('<p id="msg_warning">%s</p>', $info['warning']);
        } elseif ($info['msg']) {
            echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
        }
        //get All department
        $departmentname = array();
        $departmentid = array();
        $types = array();
        $GetAllStaff = "SELECT `id`,`name` FROM `ost_department`";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($departmentname, $RecurringTaskTitle);
                array_push($departmentid, $RecurringTaskID);
            }
        }

        //get All staff 

        $staffname = array();
        $staffid = array();

        $GetAllStaff = "SELECT `staff_id`,CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($staffname, $RecurringTaskTitle);
                array_push($staffid, $RecurringTaskID);
            }
        }

        //get All teams   
        $teamname = array();
        $teamid = array();

        $GetAllStaff = "SELECT `team_id`,`name` FROM `ost_team` ";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($teamname, $RecurringTaskTitle);
                array_push($teamid, $RecurringTaskID);
            }
        }



        //get All User   
        $username = array();
        $userid = array();

        $GetAllStaff = "SELECT `id`,`name` FROM `ost_user`  ";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($username, $RecurringTaskTitle);
                array_push($userid, $RecurringTaskID);
            }
        }
        ?>
        <div id="new-task-form" style="display:block;">
            <form method="post" class="org" action="" id="formID"  enctype="multipart/form-data">
                <?php csrf_token(); ?>





                <!-- <h3 class="drag-handle"><?php echo "Choose department"; ?></h3> -->
                
                


                <!-- <hr> -->
                <h3 class="drag-handle"><?php echo "Choose department And Choose (Agents OR Team)"; ?></h3>
                

                <div class="splitscreen">
                <div class="buttons pull-left">
                        <!-- content department-->
                        <select class="modal-body" id="ddlViewDep" name="ddlViewDep">
                    <option disabled selected value> -- select a department -- </option>
                    <?php foreach ($departmentname as $index => $item) {


                    ?>
                        <option value="<?php echo $departmentid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                    </div>


                    <div class="buttons pull-right" style="margin-left: 50px;">
                        <!-- content Staff-->
                        <select class="modal-body" id="ddlViewst" name="ddlViewst">
                            <option disabled selected value> -- select an agent -- </option>
                            <?php foreach ($staffname as $index => $item) {


                            ?>
                                <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="buttons pull-right" style="margin-left: 50px;">
                        <!-- content Team -->
                        <select class="modal-body" id="ddlViewteam" name="ddlViewteam">
                            <option disabled selected value> -- select a team -- </option>
                            <?php foreach ($teamname as $index => $item) {


                            ?>
                                <option value="<?php echo $teamid[$index]; ?>"><?php echo $item; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <hr>
                <div class="splitscreen">
                <div class="buttons pull-left">
                <h3 class="drag-handle"><?php echo "CC #1:"; ?></h3>
                <select class="modal-body" id="ddlViewcc1" name="ddlViewcc1">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                </div>
                
                <div class="buttons pull-right" style="margin-left: 50px;">
                <h3 class="drag-handle"><?php echo "CC #2:"; ?></h3>
                
                <select class="modal-body" id="ddlViewcc2" name="ddlViewcc2">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                </div>



                
                
                <div class="buttons pull-right" style="margin-left: 100px;">
                <h3 class="drag-handle"><?php echo "CC #3:"; ?></h3>
                
                <select class="modal-body" id="ddlViewcc3" name="ddlViewcc3">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>


                </div>


                
                
                </div>
                <hr>
                <h3 class="drag-handle"><?php echo "Choose User if you Want to Message User "; ?></h3>
                <select class="modal-body" id="ddlViewuser" name="ddlViewuser">
                    <option disabled selected value> -- select an user -- </option>
                    <?php foreach ($username as $index => $item) {


                    ?>
                        <option value="<?php echo $userid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                <hr>
                <h3><?php echo "Title"; ?></h3>
                <input style="width: 90%"   type="text" placeholder="Enter Messages Title" name="title" required>
                <hr>
                <h3><?php echo "Start New Message"; ?></h3>
                <textarea  name="name" id="task-response" cols="50" data-signature-field="signature" data-signature="<?php
                                                                                                                    echo Format::htmlchars(Format::viewableImages($signature)); ?>" placeholder="<?php echo __('Start writing your Message.'); ?>" rows="9" wrap="soft" class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                                                                                                                                                                                                                                                                ?> draft draft-delete" <?php

                                                                                                                                                                                                                                                                                                        ?> required></textarea>

                
                Is Private(Default Is Private):
                <!-- <input type="checkbox" name="yes" <?php //if (isset($gender) && $gender == "yes") echo "checked"; ?> value="yes">yes -->
                <input type="checkbox" name="no" <?php if (isset($gender) && $gender == "no") echo "checked"; ?> value="no">no

                <hr>
                <label for="file">Choose File:</label>
                <div style="width: auto;height: 50px; ">
         <?php
    $reply_attachments_form = new SimpleForm(array(
        'attachments' => new FileUploadField(array('id'=>'attach',
            'name'=>'attach:reply',
            'configuration' => array('extensions'=>'')))
    ));
    
    print $reply_attachments_form->getField('attachments')->render();
    echo $reply_attachments_form->getMedia();
    
    // print_r(array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]);
  
    
?>
</div>

<!-- <input type="submit" name="submit" value="Submit" /> -->



                

<br>
                    <p class="full-width" style="margin-bottom: 4em;">
                        <span class="buttons pull-left">
                            <button type="button" name="reset" class="close"><a href="masseges.php?cm=cm"><?php echo __('Reset'); ?></a></button>
                            <!-- <input type="reset" value="<?php //echo __('Reset'); ?>"> -->
                            <!-- <input type="button" name="cancel" class="close" value="<?php //echo __('Cancel'); ?>"><a href="masseges.php"></a></input> -->
                            <button type="button" name="cancel" class="close"><a href="masseges.php"><?php echo __('Cancel'); ?></a></button>
                        </span>
                        <span class="buttons pull-right">
                            <input type="submit" value="<?php echo __('Create Message'); ?>" name="str1" id='submitbtn' onclick="this.style.visibility = 'hidden'">
                        </span>
                    </p>
            </form>


        </div>
        <div class="clear"></div>

    </div>

<?php
  $staffID = null;
  $teamId = null;
  $cc1 = null;
  $cc2 = null;
  $cc3 = null;
  $departmentid = null;
  $userId = null;
  $name = null;
  $gender = 1;
  $title=null;
  
  if (isset($_POST['title'])) {
    $title = $_POST['title'];
    // echo "staffID: " . $staffID . "<br>";
    } else {
    $title = "Null";
    }

  if (isset($_POST['ddlViewst'])) {
      $staffID = $_POST['ddlViewst'];
      // echo "staffID: " . $staffID . "<br>";
  } else {
      $staffID = "Null";
  }
  if (isset($_POST['ddlViewteam'])) {
      $teamId = $_POST['ddlViewteam'];
      // echo "teamId: " . $teamId . "<br>";
  } else {
      $teamId = "Null";
  }
  if (isset($_POST['ddlViewcc1'])) {
      $cc1 = $_POST['ddlViewcc1'];
      // echo "cc1: " . $cc1 . "<br>";
  } else {
      $cc1 = "Null";
  }
  if (isset($_POST['ddlViewcc2'])) {
      $cc2 = $_POST['ddlViewcc2'];
      // echo "cc2: " . $cc2 . "<br>";
  } else {
      $cc2 = "Null";
  }
  if (isset($_POST['ddlViewcc3'])) {
      $cc3 = $_POST['ddlViewcc3'];
      // echo "cc3: " . $cc3 . "<br>";
  } else {
      $cc3 = "Null";
  }
  if (isset($_POST['ddlViewuser'])) {
      $user = $_POST['ddlViewuser'];
      // echo "user: " . $user . "<br>";
  } else {
      $user = "Null";
  }
  if (isset($_POST['ddlViewDep'])) {
      $departmentid = $_POST['ddlViewDep'];
      // echo "departmentid: " . $departmentid . "<br>";
  } else {
      $departmentid = "Null";
  }
  if (empty($_POST["name"])) {
      $nameErr = "Name is required";
      $name = "Null";
  } else {

      $name = $_POST["name"];
      // echo "name: " . $name . "<br>";
  }
  if (empty($_POST["no"])) {
      $nameErr = "IsPrivate is required";
      $gender = 1;
  } else {

      $gender = 0;
      // echo "name: " . $name . "<br>";
  }
  $sqlgetmaxnumber = "SELECT MAX(CAST(`number` AS INT)) FROM `ost_massage`";
  if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {

      $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
  }
if(($staffID != "Null" ||   $teamId!="Null" || $user!= "Null" ) && $name!="Null" && $title!="Null"){

  $sql = 'INSERT INTO `ost_massage` ( `number`, `dept_id`, `staff_id`, `assignor_id`, `team_id`,`collab_1`,`collab_2`,`collab_3`,`created`,`user_id`,`title`,`is_private` , `TheTitle`) VALUES 
                                  ("' . ((int)$newnumber[0] + 1) . '",' . $departmentid . ',' . $staffID . ',' . $thisstaff->getId() . ',' . $teamId . ',' . $cc1 . ',' . $cc2 . ',' . $cc3 . ',Now(),' . $user . ',"' . str_replace('"', '', $name) . '",' . $gender . ',"' . str_replace('"', '', $title) . '")';
  // echo $sql;

  $GetStaffNameQ = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `staff_id` = ".$thisstaff->getId();

  if (($GetStaffName_Res = db_query($GetStaffNameQ)) && db_num_rows($GetStaffName_Res)) {
      $Title= db_fetch_row($GetStaffName_Res)[0];
  }
  db_query($sql);
//   if (db_query($sql)) {
//       unset($_GET["cm"]);
//   }
  if($staffID != null){
      $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
      if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
  
          $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
      }
     
      Ticket::SendPushNotificationMsg($name,0,$staffID,$newnumber[0] ,$Title);
  }
  if($user != null){
      $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
      if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
  
          $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
      }
      Ticket::SendPushNotificationMsg($name,$user,0,$newnumber[0] ,$Title);
  }
  
      $sqlgetmaxnumber = "SELECT `id` FROM `ost_massage` ORDER BY `id` DESC LIMIT 1";
      if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {
  
          $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
      }
 if( array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0] != null){
   $sql = 'INSERT INTO `ost_file_messages` (`id`, `msg_id`, `file_id`) 
   VALUES (NULL, ' .$newnumber[0] . ','  . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ')';
    if (db_query($sql)) {
        $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
        db_query($sqll);

     } 
  GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);
  
    }
    
        ?><script>window.location.href = "masseges.php?masseges";</script><?php
       
    
    
}else{
  
  $errors['err'] = __('Plese make sure you enter the message title and body and assignor');
?>
      <div id="msg_error"><?php echo $errors['err']; ?></div><?php
     
}

}


//open message
if (isset($_GET["id"])) {
    // echo "hellllllo";
    $sqlisread="INSERT INTO `ost_read_messages` (`id`, `msg_id`, `staff_id`, `user_id`) VALUES (NULL, ".$_GET["id"].", ".$thisstaff->getId().", NULL)";

    db_query($sqlisread);

?>
    <strong>


        <a style="font-size: 20px;"   id="reload-task" class="preview" <?php
                                            echo ' class="preview" ';
                                            ?>><i style="padding-right: 5px;" class="icon-refresh"></i><?php echo sprintf(__('Messages #%s'), $_GET["id"]); ?></a>
    </strong>
    <div class="buttons pull-right">

        <!-- <span class="action-button">
            <a href="?reply=reply" class="link" title="<?php echo __('Reply'); ?>">
                <i class="icon-mail-reply"></i></a>

        </span> -->

        <span class="action-button">
            <a href="?forword=forword" class="link" title="<?php echo __('Forward'); ?>">
                <i class="icon-share"></i></a>

        </span>


    </div>
    <br>
    <br>
    <table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr>

            <?php
            $GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`title`,`ost_department`.`name`,`ost_department`.`id`
          ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`)
          ,`ost_team`.`name`,`ost_team`.`team_id`,
          CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`),
          CONCAT(c3.`firstname`, ' ', c3.`lastname`),f.`staff_id`,t.`staff_id`,`ost_user`.`name`, `ost_user`.`id`,fromuser.`name`,fromuser.`id`,`ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id`,`ost_massage`.`TheTitle`
          ,c1.`staff_id`,c2.`staff_id`,c3.`staff_id`
          FROM `ost_massage` 
          LEFT JOIN  `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id`
          LEFT JOIN  `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id`
          LEFT JOIN  `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1`
          LEFT JOIN  `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2`
          LEFT JOIN  `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3`
          LEFT JOIN  `ost_team`  ON `ost_team`.`team_id`=`ost_massage`.`team_id`
          
          LEFT JOIN  `ost_department` ON  `ost_department`.`id`=`ost_massage`.`dept_id`
          LEFT JOIN   `ost_user`  ON  `ost_user`.`id`=`ost_massage`.`user_id`
          LEFT JOIN  `ost_user` as fromuser ON fromuser.`id`=`ost_massage`.`From_user`
          LEFT JOIN  `ost_file_messages` ON `ost_file_messages`.`msg_id`= `ost_massage`.`id`
          LEFT JOIN `ost_file` ON `ost_file`.`id`=`ost_file_messages`.`file_id`
          LEFT JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
          WHERE   `ost_massage`.`id`=" . $_GET["id"];
            //  echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Number, $Created, $Subject, $Dep, $DepId, $FromStaff, $ToStaff, $team, $teamid, $cc1, $cc2, $cc3, $FromStaffId,$ToStaffId,$username,$userid,$fronusername,$fromuserid,$FileID,$FileName,$FilePath,$TT,$CC1Id,$CC2Id,$CC3Id) = db_fetch_row($GetUsersStaffTickets_Res)) {

                    $_SESSION["Number"] = $ID;
                    $_SESSION["Subject"] = $Subject;
                    if ($FromStaff != null) {
                        $_SESSION["FromStaff"] = $FromStaff;
                        $Fromm=$FromStaff;
                    } 
                    if ($fronusername != null) {
                        $_SESSION["FromStaff"] = $fronusername;
                        $Fromm=$fronusername;
                    }
                    if ($FromStaffId != null) {
                        $_SESSION["FromStaffId"] = $FromStaffId;
                    }
                    else {
                        $_SESSION["FromStaffId"] = "Null";
                    }
                    if ($fromuserid != null) {
                        $_SESSION["fromuserid"] = $fromuserid;
                    }
                    else {
                        $_SESSION["fromuserid"] = "Null";
                    }
                    
                    if ($teamid != null) {
                        $_SESSION["Toteam "] = $teamid;
                    } else {
                        $_SESSION["Toteam"] = "Null";
                    }
                    if ($DepId != null) {
                        $_SESSION["Dep"] = $DepId;
                    } else {
                        $_SESSION["Dep"] = "Null";
                    }
                    if ($userid != null) {
                        $_SESSION["userid"] = $userid;
                    } else {
                        $_SESSION["userid"] = "Null";
                    }
                    if ($ToStaffId != null) {
                        $_SESSION["ToStaffId"] = $ToStaffId;
                    } else {
                        $_SESSION["ToStaffId"] = "Null";
                    }


                    if ($CC1Id != null) {
                        $_SESSION["cc1"] = $CC1Id;
                    } else {
                        $_SESSION["cc1"] = "Null";
                    }

                    if ($CC2Id != null) {
                        $_SESSION["cc2"] = $CC2Id;
                    } else {
                        $_SESSION["cc2"] = "Null";
                    }

                    if ($CC3Id != null) {
                        $_SESSION["cc3"] = $CC3Id;
                    } else {
                        $_SESSION["cc3"] = "Null";
                    }


                    if ($TT != null) {
                        $_SESSION["TT"] = $TT;
                    } else {
                        $_SESSION["TT"] = "Null";
                    }
                    if ($FileID != null) {
                        $_SESSION["FileID"] = $FileID;
                    } else {
                        $_SESSION["FileID"] = "Null";
                    }
                    
            ?>
            <strong>


<a style="font-size: 25px; color:#444444; font-weight:normal;"  id="reload-task" class="preview" <?php
                                    echo ' class="preview" ';
                                    ?>><?php echo sprintf(__('%s'), $TT); ?></a>
</strong>
<hr>
                    <td width="50%">
                        <table border="0" cellspacing="" cellpadding="4" width="100%">
                            <tr>
                                <th width="100"><?php echo __('Number '); ?>:</th>
                                <td><?php echo Format::htmlchars($Number); ?></td>
                            </tr>

                            <tr>
                                <th><?php echo __('Created'); ?>:</th>
                                <td><?php echo Format::htmlchars($Created); ?></td>
                            </tr>


                            <tr>
                                <th><?php echo __('From '); ?>:</th>
                                <td><?php echo Format::htmlchars($Fromm); ?></td>
                            </tr>

                        </table>
                    </td>
                    <td width="50%" style="vertical-align:top">
                        <table cellspacing="0" cellpadding="4" width="100%" border="0">

                            <tr>
                                <th><?php echo __('Department'); ?>:</th>
                                <td><?php echo Format::htmlchars($Dep); ?></td>
                            </tr>

                            <tr>
                                <th width="100"><?php echo __('Assigned To'); ?>:</th>
                                <td>
                                    <?php
                                    if ($ToStaff != null)
                                        echo Format::htmlchars($ToStaff);
                                    else
                                        echo Format::htmlchars($team);;
                                    ?>
                                </td>
                            </tr>


                            <?php if ($cc1 != null) { ?>
                                <tr>
                                    <th><?php echo __('CC1'); ?>:</th>
                                    <td>
                                        <?php
                                        $cc = __('CC1');
                                        $cc = sprintf(__('CC1 (%d)'), $cc1);
                                        echo sprintf($cc1);


                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>


                            <?php if ($cc2 != null) { ?>
                                <tr>
                                    <th><?php echo __('CC2'); ?>:</th>
                                    <td>
                                        <?php
                                        $cc = __('CC2');


                                        $cc = sprintf(__('CC2 (%d)'), $cc2);
                                        echo sprintf($cc2);


                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if ($cc3 != null) { ?>
                                <tr>
                                    <th><?php echo __('CC3'); ?>:</th>
                                    <td>
                                        <?php
                                        $cc = __('CC3');


                                        $cc = sprintf(__('CC3 (%d)'), $cc3);
                                        echo sprintf($cc3);


                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>


                            <?php if ($username != null) { ?>
                                <tr>
                                    <th><?php echo __('User'); ?>:</th>
                                    <td>
                                        <?php
                                        $cc = __('User');


                                        $cc = sprintf(__('User (%d)'), $username);
                                        echo sprintf($username);


                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>


                        </table>
                    </td>
        </tr>
    </table>
    <br>
    <hr>
    <div class="clear"></div>
    <div id="task_thread_container">
        <div id="task_thread_content" class="tab_content">
            <table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">

                <tr>
                    <td colspan="2">
                        <table cellspacing="0" cellpadding="4" width="100%" border="0">
                            <tr>
                                <th width="100">
                                    <?php
                                    echo "Message Content:\n";
                                    ?>
                                <td><?php
                                    echo $Subject;
                                    ?></td>
                                </th>

                            </tr>

                            <?php if ($FileName != null) {
                                $f = AttachmentFile::lookup((int) $FileID); ?>
                                <tr>
                                    <th style="color:blue;"><?php echo __('Attachment'); ?>:</th>
                                    <td><a class="no-pjax truncate filename" href="<?php echo $f->getDownloadUrl(['id' => $FilePath]); ?>" download="<?php echo $FileName; ?>" target="_blank">
                                        <?php
                                        


                                        // $cc = sprintf(__('Attachment (%d)'), $FileName);
                                        echo sprintf($FileName);


                                        ?></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php


                    $getreply_id = "SELECT `reply` FROM `ost_massage` WHERE `id`=" . $ID;
                    if (($getreply_id_Res = db_query($getreply_id)) && db_num_rows($getreply_id_Res)) {

                        $reply_id = db_fetch_row($getreply_id_Res);
                    }
// echo $reply_id[0];
                    //show reply here
                    if ($reply_id[0] != null) {
                        $GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`title`,`ost_department`.`name`,`ost_department`.`id`
          ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`)
          ,`ost_team`.`name`,`ost_team`.`team_id`,
          CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`),
          CONCAT(c3.`firstname`, ' ', c3.`lastname`),f.`staff_id`
          FROM `ost_massage` 
          LEFT JOIN `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id`
          LEFT JOIN `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id`
          LEFT JOIN  `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1`
          LEFT JOIN  `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2`
          LEFT JOIN  `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3`
          LEFT JOIN  `ost_team`  ON `ost_team`.`team_id`=`ost_massage`.`team_id`
          
          LEFT JOIN `ost_department` ON  `ost_department`.`id`=`ost_massage`.`dept_id`
          WHERE   `ost_massage`.`id`=" . $reply_id[0];
                        //  echo $GetUsersStaffTicketsQ;
                        if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                            while (list($ID_, $Number_, $Created, $Subject, $Dep, $DepId, $FromStaff, $ToStaff, $team, $teamid, $cc1, $cc2, $cc3, $FromStaffId) = db_fetch_row($GetUsersStaffTickets_Res)) {
    ?>
                <br>
                <hr>
                <div class="clear"></div>
                <div id="task_thread_container">
                    <div id="task_thread_content" class="tab_content">
                        <table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">

                            <tr>
                                <td colspan="2">
                                    <table cellspacing="0" cellpadding="4" width="100%" border="0">
                                        <tr>
                                            <th width="100"><a href="?id=<?php echo $ID_; ?>">
                                                    <?php
                                                    echo "Message Replyed:#" . $Number_;
                                                    ?></a>
                                            <td><?php
                                                echo $Subject;
                                                ?></td>

                                            </th>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
<?php
                            }
                        }
                    }
                }
            }
        }
//open reply dailoge
if (isset($_GET["reply"])) {
?>
<style>
    html {
        overflow: scroll;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
    }

    /* The Modal (background) */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */

        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 200%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
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
        animation-duration: 0.4s;
        overflow-y: auto;
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
        width: 600px;
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

    .error {
        color: #FF0000;
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

<body scroll="yes">
    <!-- The Modal -->
    <div id="myModal" class="modal">

        <!-- Modal content -->
        <div class="modal-content">

            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="h2class">Reply To : <?php echo $_SESSION["FromStaff"]; ?></h2>

            </div>
            <div class="modal-body">


                <form method="post" action="masseges.php" id="myForm">
                    <?php csrf_token(); ?>

                    <h3><?php echo "Title"; ?></h3>
                <input style="width: 90%"   type="text" placeholder="Enter Messages Title" name="title" required>
                <br><br>
                    <textarea name="replytext" id="task-response" cols="50" data-signature-field="signature" data-signature="<?php
                                                                                                                                echo Format::htmlchars(Format::viewableImages($signature)); ?>" placeholder="<?php echo __('Start writing your Reply.'); ?>" rows="9" wrap="soft" class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                                                                                                                                                                    ?> draft draft-delete" <?php

                                                ?> required></textarea>
                    <br>
                    <!-- <label for="file">Choose File:</label>
                <div style="width: auto;height: 50px; "> -->
         <?php
    // $reply_attachments_form = new SimpleForm(array(
    //     'attachments' => new FileUploadField(array('id'=>'attach',
    //         'name'=>'attach:reply',
    //         'configuration' => array('extensions'=>'')))
    // ));
    
    // print $reply_attachments_form->getField('attachments')->render();
    // echo $reply_attachments_form->getMedia();
    ?>
    <br>
                    <table cellspacing="0" cellpadding="4" width="100%" border="0" style="background-color: #bbb;">
                        <tr>
                            <th width="100">
                                <?php
                                echo "Message Content:\n";
                                ?>
                            <td><?php
                                echo $_SESSION["Subject"];
                                ?></td>
                            </th>

                        </tr>
                    </table>

                    <hr>
                    <input type="submit" name="Submit1" value="Submit" id="btn-submit" onclick="this.style.visibility = 'hidden'">
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


}
 

//open forwad page
if (isset($_GET["forword"])) {


?>
    <style>
        .splitscreen {
            display: flex;
        }

        .splitscreen .left {
            flex: 1;
        }

        .splitscreen .right {
            flex: 1;
        }
    </style>
    <div id="task-form">



        <?php
            if ($info['error']) {
                echo sprintf('<p id="msg_error">%s</p>', $info['error']);
            } elseif ($info['warning']) {
                echo sprintf('<p id="msg_warning">%s</p>', $info['warning']);
            } elseif ($info['msg']) {
                echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
            }
            //get All department
            $departmentname = array();
            $departmentid = array();
            $types = array();
            $GetAllStaff = "SELECT `id`,`name` FROM `ost_department`";
            if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                    array_push($departmentname, $RecurringTaskTitle);
                    array_push($departmentid, $RecurringTaskID);
                }
            }

            //get All staff 

            $staffname = array();
            $staffid = array();

            $GetAllStaff = "SELECT `staff_id`,CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`";
            if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                    array_push($staffname, $RecurringTaskTitle);
                    array_push($staffid, $RecurringTaskID);
                }
            }

            //get All teams   
            $teamname = array();
            $teamid = array();

            $GetAllStaff = "SELECT `team_id`,`name` FROM `ost_team` ";
            if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                    array_push($teamname, $RecurringTaskTitle);
                    array_push($teamid, $RecurringTaskID);
                }
            }



            //get All User   
            $username = array();
            $userid = array();

            $GetAllStaff = "SELECT `id`,`name` FROM `ost_user` WHERE `fcm_token` IS NOT NULL";
            if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                    array_push($username, $RecurringTaskTitle);
                    array_push($userid, $RecurringTaskID);
                }
            }
        ?>
        <div id="new-task-form" style="display:block;">
            <form method="post" class="org" action="" id="formID">
                <?php csrf_token(); ?>
               



                <h3 class="drag-handle"><?php echo "Choose department AND Choose (Agents OR Team)"; ?></h3>
                <div class="splitscreen">
                <div class="buttons pull-left">
                        <!-- content department-->
                        <select class="modal-body" id="ddlViewDep" name="ddlViewDep">
                    <option disabled selected value> -- select a department -- </option>
                    <?php foreach ($departmentname as $index => $item) {


                    ?>
                        <option value="<?php echo $departmentid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                    </div>


                    <div class="buttons pull-right" style="margin-left: 50px;">
                        <!-- content Staff-->
                        <select class="modal-body" id="ddlViewst" name="ddlViewst">
                            <option disabled selected value> -- select an agent -- </option>
                            <?php foreach ($staffname as $index => $item) {


                            ?>
                                <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="buttons pull-right" style="margin-left: 50px;">
                        <!-- content Team -->
                        <select class="modal-body" id="ddlViewteam" name="ddlViewteam">
                            <option disabled selected value> -- select a team -- </option>
                            <?php foreach ($teamname as $index => $item) {


                            ?>
                                <option value="<?php echo $teamid[$index]; ?>"><?php echo $item; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <hr>
                <div class="splitscreen">
                <div class="buttons pull-left">
                <h3 class="drag-handle"><?php echo "CC #1:"; ?></h3>
                <select class="modal-body" id="ddlViewcc1" name="ddlViewcc1">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                </div>
                
                <div class="buttons pull-right" style="margin-left: 50px;">
                <h3 class="drag-handle"><?php echo "CC #2:"; ?></h3>
                
                <select class="modal-body" id="ddlViewcc2" name="ddlViewcc2">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                </div>



                
                
                <div class="buttons pull-right" style="margin-left: 100px;">
                <h3 class="drag-handle"><?php echo "CC #3:"; ?></h3>
                
                <select class="modal-body" id="ddlViewcc3" name="ddlViewcc3">
                    <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($staffname as $index => $item) {


                    ?>
                        <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                </div>
                </div>
                <hr>
                <h3 class="drag-handle"><?php echo "Choose User if you Whant to Message User "; ?></h3>
                <select class="modal-body" id="ddlViewuser" name="ddlViewuser">
                    <option disabled selected value> -- select an user -- </option>
                    <?php foreach ($username as $index => $item) {


                    ?>
                        <option value="<?php echo $userid[$index]; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
                <h3><?php echo "Title"; ?></h3>
                <input disabled  style="width: 90%"   type="text" placeholder="<?php echo $_SESSION["TT"];?>" name="title" >
                <hr>
                <h3><?php echo "Forward Message Content"; ?></h3>
                

                <textarea disabled name="name" id="task-response" cols="50" data-signature-field="signature" data-signature="<?php
                                                                                                                    echo Format::htmlchars(Format::viewableImages($signature)); ?>" placeholder="<?php echo $_SESSION["Subject"]; ?>" rows="9" wrap="soft"><?php echo $_SESSION["Subject"]; ?></textarea>
                <br>
                <hr>
                Is Private(Default Is Private):
                <!-- <input type="checkbox" name="yes" <?php //if (isset($gender) && $gender == "yes") echo "checked"; ?> value="yes">yes -->
                <input type="radio" name="no" <?php if (isset($gender) && $gender == "no") echo "checked"; ?> value="no">no

                <br>
                    <p class="full-width" style="margin-bottom: 4em;">
                        <span class="buttons pull-left">
                            <button type="button" name="reset" class="close"><a href="masseges.php?forword=forword"><?php echo __('Reset'); ?></a></button>
                            <!-- <input type="reset" value="<?php echo __('Reset'); ?>"> -->
                            <!-- <input type="button" name="cancel" class="close" value="<?php echo __('Cancel'); ?>"><a href="masseges.php"></a></input> -->
                            <button type="button" name="cancel" class="close"><a href="masseges.php"><?php echo __('Cancel'); ?></a></button>
                        </span>
                        <span class="buttons pull-right">
                            <input type="submit" value="<?php echo __('Forward Message'); ?>" name="str2" id='submitbtn' onclick="this.style.visibility = 'hidden'">
                        </span>
                    </p>
            </form>


        </div>
        <div class="clear"></div>
    </div>

<?php

        }



        //download file                  
if(isset($_GET["download"])){
    // $file = basename($_GET['file']);
    $file = $_GET["download"];
    // echo $_GET["download"];
    if(!file_exists($file)){ // file does not exist
        die('file not found');
    } else {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        // header("Content-Disposition: attachment; filename=$file");
        // header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        // header('Content-Disposition: attachment; filename=' . $file); 

        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush(); // Flush system output buffer
    
        // read the file from disk
        readfile($file);
    }
   
}



//Read Masseges


if(isset($_GET["readedmasseges"])){
 // $results = ucfirst($status) . ' ' . __('Masseges');
    
 $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
 if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
     $order = $orderWays[strtoupper($_REQUEST['order'])];
 } else {
     $order = 'ASC';
 }
 
?>
 <h1 style="margin:10px 0">
     <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
         <?php echo __('Masseges'); ?>
     </a>
 </h1>
 <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
     <caption><?php echo  $results; ?></caption>
     <thead>
         <tr>
             <th width="20">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('#'); ?>&nbsp;</a>
             </th>
             <th width="100">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=from_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'From Agent"><?php echo __('From'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=to_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To Agent"><?php echo __('To'); ?>&nbsp;</a>
             </th>
             <th width="110">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
             </th>
             
            

             <th width="75">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=team&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Team"> <?php echo __('Team'); ?>&nbsp;</a>
             </th>
             <th width="75">
                <?php echo __('CC'); ?>
             </th>
             <th width="75">
             <a href="masseges.php?readedmasseges=readedmasseges&sort=to_user&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To User'"><?php echo __('To User'); ?>&nbsp;</a>
             </th>
         </tr>
     </thead>
     <tbody>
         <?php
            if ((sizeof($thisstaff->getTeams())) != 0 ){
                $teams = $thisstaff->getTeams();
                $where[] = ' OR (`ost_massage`.`team_id` IN(' . implode(',', db_input(array_filter($teams)))
                . ') AND `ost_massage`.`is_private` = 0 ) ';
                // print_r($where);
                // echo $where[0];
            }

if (isset($_GET["sort"])){
//Sort By Number    
if($_GET["sort"]=="ID"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`number` DESC";
 }
elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`number` ASC";
}
}



//Sort By To Agent   
if($_GET["sort"]=="to_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  t.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  t.`firstname`  ASC";
   }
   }



   //Sort By from Agent   
if($_GET["sort"]=="from_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  f.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  f.`firstname`  ASC";
   }
   }


  
  
   //Sort By team   
if($_GET["sort"]=="team"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  `ost_team`.`name`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  `ost_team`.`name`  ASC";
   }
   }


//Sort By 

if($_GET["sort"]=="subject"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`TheTitle`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`TheTitle`  ASC";
 }
}
if($_GET["sort"]=="date"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`created`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`created`  ASC";
 }
}

 if($_GET["sort"]=="dept"){
     if($_GET["order"]=="DESC"){
        $OrderBy = "  `ost_department`.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
         $OrderBy = "  `ost_department`.`name`  ASC";
     }
 }


 if($_GET["sort"]=="to_user"){
     if($_GET["order"]=="DESC"){
        $OrderBy = "  touser.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
        $OrderBy = "  touser.`name`  ASC";
     }
 }
//end sort     
}
else{

$OrderBy="`ost_massage`.`created` DESC";
         
}

$GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`TheTitle`,`ost_department`.`name` ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`) ,`ost_team`.`name`, CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`), CONCAT(c3.`firstname`, ' ', c3.`lastname`),touser.`name`,fromuser.`name`,`ost_file_messages`.`id` 
         FROM `ost_massage` 
         LEFT JOIN `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id` 
         LEFT JOIN `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id` 
         LEFT JOIN `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1` 
         LEFT JOIN `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2` 
         LEFT JOIN `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3` 
         LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_massage`.`team_id` 
         LEFT JOIN `ost_user` as touser ON touser.`id`=`ost_massage`.`user_id` 
         LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_massage`.`dept_id` 
         LEFT JOIN `ost_user` as fromuser ON fromuser.`id`=`ost_massage`.`From_user` 
         LEFT JOIN `ost_file_messages` ON `ost_file_messages`.`msg_id`=`ost_massage`.`id`
         LEFT JOIN `ost_read_messages` ON `ost_read_messages`.`msg_id`=`ost_massage`.`id` 
         WHERE `ost_read_messages`.`staff_id`= ".$thisstaff->getId()."
         ORDER BY ".$OrderBy;
        //  echo $GetUsersStaffTicketsQ;
         if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
             while (list($ID, $Number, $Created, $Subject, $Dep, $FromStaff, $ToStaff, $team, $cc1, $cc2, $cc3,$touser,$fronuser,$fileID) = db_fetch_row($GetUsersStaffTickets_Res)) {
                 if ($team == null) {
                     $teamID = "No Team";
                 } else {
                     $teamID = $team;
                 }
                 if ($Dep == null) {
                     $DepID = "No Department";
                 } else {
                     $DepID = $Dep;
                 }
                 if ($touser == null) {
                     $ToUser = "No User";
                 } else {
                     $ToUser = $touser;
                 }

                 if ($ToStaff == null) {
                     $ToStaffName = "No Agent";
                 } else {
                     $ToStaffName  = $ToStaff;
                 }
                 if ($FromStaff == null) {
                     $FromStaffName = $fronuser;
                 } else {
                     $FromStaffName  = $FromStaff;
                 }

         ?>
                 <tr id="<?php echo $Number; ?>">
                     <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo $Number; ?></a></td>
                     <td style="font-weight:bold;"><span><?php echo $FromStaffName; ?></span></td>
                     <?Php if($fileID != null ) {?>
                          <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a><?php echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';?></td>
                     <?php } else {?>
                         <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a></td>
                     <?Php }?>
                     <td style='font-weight:bold'><?php echo $Created; ?></td>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $ToStaffName; ?></span></td>
                     <td style='font-weight:bold'><span><?php echo  $DepID; ?></span></td>           
                     <td style='font-weight:bold'><span class="truncate"><?php echo $teamID; ?></span></td>
                     <?php if($cc1 != null ) {?>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                     <?php }
                     elseif($cc2 != null ){?>
                         <td style='font-weight:bold'><span class="truncate"><?php echo $cc2  ?></span></td>
                         <?php }
                         elseif($cc3 != null ){?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                             <?php }
                             else{?>
                               <td style='font-weight:bold'><span class="truncate"><?php echo "No CC"  ?></span></td>
                             <?php }?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $ToUser; ?></span></td>

                 </tr>
         <?php
             }
         }

         ?>
     </tbody>
 </table>
<?php  }

if(isset($_GET["maneger"])){
 // $results = ucfirst($status) . ' ' . __('Masseges');
    
 $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
 if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
     $order = $orderWays[strtoupper($_REQUEST['order'])];
 } else {
     $order = 'ASC';
 }
 
?>
 <h1 style="margin:10px 0">
     <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
         <?php echo __('Masseges'); ?>
     </a>
 </h1>
 <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
     <caption><?php echo  $results; ?></caption>
     <thead>
         <tr>
             <th width="20">
             <a href="masseges.php?maneger=maneger&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('#'); ?>&nbsp;</a>
             </th>
             <th width="100">
             <a href="masseges.php?maneger=maneger&sort=from_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'From Agent'"> <?php echo __('From'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?maneger=maneger&sort=subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?maneger=maneger&sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?maneger=maneger&sort=to_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To Agent'"><?php echo __('To'); ?>&nbsp;</a>
             </th>
             <th width="110">
             <a href="masseges.php?maneger=maneger&sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
             </th>
             
            

             <th width="75">
             <a href="masseges.php?maneger=maneger&sort=team&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Team"><?php echo __('Team'); ?>&nbsp;</a>
             </th>
             <th width="75">
                <?php echo __('CC'); ?>
             </th>
             <th width="75">
             <a href="masseges.php?maneger=maneger&sort=to_user&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To User'"><?php echo __('To User'); ?>&nbsp;</a>
             </th>
         </tr>
     </thead>
     <tbody>
         <?php
         if (isset($_GET["sort"])){
//Sort By Number    
if($_GET["sort"]=="ID"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`number` DESC";
 }
elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`number` ASC";
}
}
//Sort By To Agent   
if($_GET["sort"]=="to_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  t.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  t.`firstname`  ASC";
   }
   }


   //Sort By from Agent   
if($_GET["sort"]=="from_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  f.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  f.`firstname`  ASC";
   }
   }

//Sort By team   
if($_GET["sort"]=="team"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  `ost_team`.`name`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  `ost_team`.`name`  ASC";
   }
   }





//Sort By 

if($_GET["sort"]=="subject"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`TheTitle`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`TheTitle`  ASC";
 }
}
if($_GET["sort"]=="date"){
 if($_GET["order"]=="DESC"){
    $OrderBy= "  `ost_massage`.`created`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`created`  ASC";
 }
}
 if($_GET["sort"]=="dept"){
     if($_GET["order"]=="DESC"){
        $OrderBy= "  `ost_department`.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
        $OrderBy = "  `ost_department`.`name`  ASC";
     }
 }
 if($_GET["sort"]=="to_user"){
     if($_GET["order"]=="DESC"){
        $OrderBy = "  touser.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
        $OrderBy = "  touser.`name`  ASC";
     }
 }
//end sort     
}
else{


         $OrderBy = "  `ost_massage`.`created` DESC";
}

$GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`TheTitle`,`ost_department`.`name` ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`) ,`ost_team`.`name`, CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`), CONCAT(c3.`firstname`, ' ', c3.`lastname`),touser.`name`,fromuser.`name`,`ost_file_messages`.`file_id` 
         FROM `ost_massage` 
         LEFT JOIN `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id`
          LEFT JOIN `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id` 
          LEFT JOIN `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1` 
          LEFT JOIN `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2` 
          LEFT JOIN `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3` 
          LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_massage`.`team_id` 
          LEFT JOIN `ost_user` as touser ON touser.`id`=`ost_massage`.`user_id` 
          LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_massage`.`dept_id` 
          LEFT JOIN `ost_user` as fromuser ON fromuser.`id`=`ost_massage`.`From_user` 
          LEFT JOIN  `ost_file_messages` ON `ost_file_messages`.`msg_id`= `ost_massage`.`id`
          LEFT JOIN `ost_file` ON `ost_file`.`id`=`ost_file_messages`.`file_id`

         WHERE `ost_massage`.`staff_id` IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId()."))
          OR `ost_massage`.`assignor_id`IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId().")) 
          OR `ost_massage`.`collab_1`IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId()."))
           OR `ost_massage`.`collab_2`IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId().")) 
           OR `ost_massage`.`collab_3`IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId()."))  
            ORDER BY ".$OrderBy;
        //  echo $GetUsersStaffTicketsQ;
         if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
             while (list($ID, $Number, $Created, $Subject, $Dep, $FromStaff, $ToStaff, $team, $cc1, $cc2, $cc3,$touser,$fronuser,$fileID) = db_fetch_row($GetUsersStaffTickets_Res)) {
                 if ($team == null) {
                     $teamID = "No Team";
                 } else {
                     $teamID = $team;
                 }
                 if ($Dep == null) {
                     $DepID = "No Department";
                 } else {
                     $DepID = $Dep;
                 }
                 if ($touser == null) {
                     $ToUser = "No User";
                 } else {
                     $ToUser = $touser;
                 }

                 if ($ToStaff == null) {
                     $ToStaffName = "No Agent";
                 } else {
                     $ToStaffName  = $ToStaff;
                 }
                 if ($FromStaff == null) {
                     $FromStaffName = $fronuser;
                 } else {
                     $FromStaffName  = $FromStaff;
                 }

         ?>
                 <tr id="<?php echo $Number; ?>">
                     <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo $Number; ?></a></td>
                     <td style="font-weight:bold;"><span><?php echo $FromStaffName; ?></span></td>
                     <?Php if($fileID != null ) {?>
                          <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a><?php echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';?></td>
                     <?php } else {?>
                         <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a></td>
                     <?Php }?>
                     <td style='font-weight:bold'><?php echo $Created; ?></td>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $ToStaffName; ?></span></td>
                     <td style='font-weight:bold'><span><?php echo  $DepID; ?></span></td>           
                     <td style='font-weight:bold'><span class="truncate"><?php echo $teamID; ?></span></td>
                     <?php if($cc1 != null ) {?>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                     <?php }
                     elseif($cc2 != null ){?>
                         <td style='font-weight:bold'><span class="truncate"><?php echo $cc2  ?></span></td>
                         <?php }
                         elseif($cc3 != null ){?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                             <?php }
                             else{?>
                               <td style='font-weight:bold'><span class="truncate"><?php echo "No CC"  ?></span></td>
                             <?php }?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $ToUser; ?></span></td>

                 </tr>
         <?php
             }
         }

         ?>
     </tbody>
 </table>
<?php  }

if(isset($_GET["Showrooms"])){
 // $results = ucfirst($status) . ' ' . __('Masseges');
    
 $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
 if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
     $order = $orderWays[strtoupper($_REQUEST['order'])];
 } else {
     $order = 'ASC';
 }
 
?>
 <h1 style="margin:10px 0">
     <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
         <?php echo __('Masseges'); ?>
     </a>
 </h1>
 <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
     <caption><?php echo  $results; ?></caption>
     <thead>
         <tr>
             <th width="20">
             <a href="masseges.php?Showrooms=Showrooms&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('#'); ?>&nbsp;</a>
             </th>
             <th width="100">
             <a href="masseges.php?Showrooms=Showrooms&sort=from_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'From Agent'"> <?php echo __('From'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?Showrooms=Showrooms&sort=subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?Showrooms=Showrooms&sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
             </th>
             <th width="120">
             <a href="masseges.php?Showrooms=Showrooms&sort=to_agent&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To Agent'"><?php echo __('To'); ?>&nbsp;</a>
             </th>
             <th width="110">
             <a href="masseges.php?Showrooms=Showrooms&sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
             </th>
             
            

             <th width="75">
             <a href="masseges.php?Showrooms=Showrooms&sort=team&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Team"><?php echo __('Team'); ?>&nbsp;</a>
             </th>
             <th width="75">
                <?php echo __('CC'); ?>
             </th>
             <th width="75">
             <a href="masseges.php?Showrooms=Showrooms&sort=to_user&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To User'"><?php echo __('To User'); ?>&nbsp;</a>
             </th>
         </tr>
     </thead>
     <tbody>
         <?php
         if (isset($_GET["sort"])){
//Sort By Number    
if($_GET["sort"]=="ID"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`number` DESC";
 }
elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`number` ASC";
}
}
//Sort By To Agent   
if($_GET["sort"]=="to_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  t.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  t.`firstname`  ASC";
   }
   }


   //Sort By from Agent   
if($_GET["sort"]=="from_agent"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  f.`firstname`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  f.`firstname`  ASC";
   }
   }

//Sort By team   
if($_GET["sort"]=="team"){
    if($_GET["order"]=="DESC"){
       $OrderBy = "  `ost_team`.`name`  DESC";
    }
   elseif ($_GET["order"]=="ASC") {
       $OrderBy = "  `ost_team`.`name`  ASC";
   }
   }





//Sort By 

if($_GET["sort"]=="subject"){
 if($_GET["order"]=="DESC"){
    $OrderBy = "  `ost_massage`.`TheTitle`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`TheTitle`  ASC";
 }
}
if($_GET["sort"]=="date"){
 if($_GET["order"]=="DESC"){
    $OrderBy= "  `ost_massage`.`created`  DESC";
 }
 elseif ($_GET["order"]=="ASC") {
    $OrderBy = "  `ost_massage`.`created`  ASC";
 }
}
 if($_GET["sort"]=="dept"){
     if($_GET["order"]=="DESC"){
        $OrderBy= "  `ost_department`.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
        $OrderBy = "  `ost_department`.`name`  ASC";
     }
 }
 if($_GET["sort"]=="to_user"){
     if($_GET["order"]=="DESC"){
        $OrderBy = "  touser.`name`  DESC";
     }
     elseif ($_GET["order"]=="ASC") {
        $OrderBy = "  touser.`name`  ASC";
     }
 }
//end sort     
}
else{


         $OrderBy = "  `ost_massage`.`created` DESC";
}

$GetUsersStaffTicketsQ = "SELECT `ost_massage`.`id` ,`ost_massage`.`number`, `ost_massage`.`created` ,`ost_massage`.`TheTitle`,`ost_department`.`name` ,CONCAT(f.`firstname`, ' ', f.`lastname`),CONCAT(t.`firstname`, ' ', t.`lastname`) ,`ost_team`.`name`, CONCAT(c1.`firstname`, ' ', c1.`lastname`),CONCAT(c2.`firstname`, ' ', c2.`lastname`), CONCAT(c3.`firstname`, ' ', c3.`lastname`),touser.`name`,fromuser.`name`,`ost_file_messages`.`file_id` 
         FROM `ost_massage` 
         LEFT JOIN `ost_staff` as f ON f.`staff_id`=`ost_massage`.`assignor_id`
          LEFT JOIN `ost_staff` as t ON t.`staff_id`=`ost_massage`.`staff_id` 
          LEFT JOIN `ost_staff` as c1 ON c1.`staff_id`=`ost_massage`.`collab_1` 
          LEFT JOIN `ost_staff` as c2 ON c2.`staff_id`=`ost_massage`.`collab_2` 
          LEFT JOIN `ost_staff` as c3 ON c3.`staff_id`=`ost_massage`.`collab_3` 
          LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_massage`.`team_id` 
          LEFT JOIN `ost_user` as touser ON touser.`id`=`ost_massage`.`user_id` 
          LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_massage`.`dept_id` 
          LEFT JOIN `ost_user` as fromuser ON fromuser.`id`=`ost_massage`.`From_user` 
          LEFT JOIN  `ost_file_messages` ON `ost_file_messages`.`msg_id`= `ost_massage`.`id`
          LEFT JOIN `ost_file` ON `ost_file`.`id`=`ost_file_messages`.`file_id`

         WHERE `ost_massage`.`user_id` IN (SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=".$thisstaff->getId().")
          OR `ost_massage`.`From_user` IN (SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=".$thisstaff->getId().")   
            ORDER BY ".$OrderBy;
        //  echo $GetUsersStaffTicketsQ;
         if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
             while (list($ID, $Number, $Created, $Subject, $Dep, $FromStaff, $ToStaff, $team, $cc1, $cc2, $cc3,$touser,$fronuser,$fileID) = db_fetch_row($GetUsersStaffTickets_Res)) {
                 if ($team == null) {
                     $teamID = "No Team";
                 } else {
                     $teamID = $team;
                 }
                 if ($Dep == null) {
                     $DepID = "No Department";
                 } else {
                     $DepID = $Dep;
                 }
                 if ($touser == null) {
                     $ToUser = "No User";
                 } else {
                     $ToUser = $touser;
                 }

                 if ($ToStaff == null) {
                     $ToStaffName = "No Agent";
                 } else {
                     $ToStaffName  = $ToStaff;
                 }
                 if ($FromStaff == null) {
                     $FromStaffName = $fronuser;
                 } else {
                     $FromStaffName  = $FromStaff;
                 }

         ?>
                 <tr id="<?php echo $Number; ?>">
                     <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo $Number; ?></a></td>
                     <td style="font-weight:bold;"><span><?php echo $FromStaffName; ?></span></td>
                     <?Php if($fileID != null ) {?>
                          <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a><?php echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';?></td>
                     <?php } else {?>
                         <td style='font-weight:bold'><a class="preview" href="?id=<?php echo $ID; ?>"><?php echo substr($Subject, 0, 50); ?></a></td>
                     <?Php }?>
                     <td style='font-weight:bold'><?php echo $Created; ?></td>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $ToStaffName; ?></span></td>
                     <td style='font-weight:bold'><span><?php echo  $DepID; ?></span></td>           
                     <td style='font-weight:bold'><span class="truncate"><?php echo $teamID; ?></span></td>
                     <?php if($cc1 != null ) {?>
                     <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                     <?php }
                     elseif($cc2 != null ){?>
                         <td style='font-weight:bold'><span class="truncate"><?php echo $cc2  ?></span></td>
                         <?php }
                         elseif($cc3 != null ){?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $cc1  ?></span></td>
                             <?php }
                             else{?>
                               <td style='font-weight:bold'><span class="truncate"><?php echo "No CC"  ?></span></td>
                             <?php }?>
                             <td style='font-weight:bold'><span class="truncate"><?php echo $ToUser; ?></span></td>

                 </tr>
         <?php
             }
         }

         ?>
     </tbody>
 </table>
<?php  }

        // require_once(STAFFINC_DIR.$inc);
        require_once(STAFFINC_DIR . 'footer.inc.php');
?>