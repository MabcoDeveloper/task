<?php
if (!defined('OSTSCPINC')
    || !$thisstaff || !$task
    || !($role = $thisstaff->getRole($task->getDept())))
    die('Invalid path');
    date_default_timezone_set('Asia/Damascus');

global $cfg;
$isprivate="SELECT `is_private` FROM `ost_task` WHERE `id`=".$task->getId();
    if (($isprivater_Res = db_query($isprivate)) && db_num_rows($isprivater_Res)) {
  
        $IsPrivate = db_fetch_row($isprivater_Res);
    }
$ISManeger="SELECT `manager_id` FROM `ost_department` WHERE `name`='".$task->getDept()."'";
if (($ISManeger_Res = db_query($ISManeger)) && db_num_rows($ISManeger_Res)) {
  
    $ISManegerT = db_fetch_row($ISManeger_Res);
}  
$ISMyManeger="SELECT `manager_id` FROM `ost_department` WHERE `id` =(SELECT `dept_id` FROM `ost_staff` WHERE `staff_id`=".$task->getAssignorId().")";
if (($ISMyManeger_Res = db_query($ISMyManeger)) && db_num_rows($ISMyManeger_Res)) {
  
    $ISMyManegerT = db_fetch_row($ISMyManeger_Res);
} 
$y=array();
$from=array();
$allow_staff=false;
$allow_staff_to=false;
$GetCCTasksQ = "SELECT `ost_staff_dept_access`.`dept_id`  FROM `ost_staff_dept_access` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_staff_dept_access`.`staff_id` WHERE `ost_staff_dept_access`.`dept_id`<> `ost_staff`.`dept_id` AND `ost_staff`.`staff_id`=" . $thisstaff->getId() ;
if (($GetCCTasks_Res = db_query($GetCCTasksQ)) && db_num_rows($GetCCTasks_Res)) {
    while (list($ID) = db_fetch_row($GetCCTasks_Res)) {
        
        
        array_push($y, $ID);
    }
}
$allow="SELECT `staff_id` FROM `ost_staff` WHERE `dept_id` IN ( ".implode(",", $y).")";
if (($allow_Res = db_query($allow)) && db_num_rows($allow_Res)) {
    while (list($ID) = db_fetch_row($allow_Res)) {
        
        
        array_push($from, $ID);
    }
}  
if($thisstaff->isManager() && in_array($task->getAssignorId(),$from)){
    $allow_staff=true;
}

if($thisstaff->isManager() && in_array($task->getStaffId(),$from)){
    $allow_staff_to=true;
}

if( $IsPrivate[0] == 1 && ($thisstaff->getId()!=$task->getAssignorId() && $thisstaff->getId()!=$task->getStaffId()  && $ISManegerT[0]!=$thisstaff->getId()  && $thisstaff->getId()!=$ISMyManegerT[0]) && $task->isClosed() && $task->isextended($thisstaff)==false && $task->getCollab1() != $thisstaff->getId() && $task->getCollab2() != $thisstaff->getId() && $task->getCollab3() != $thisstaff->getId() && !in_array($task->getTeamId(), $thisstaff->getTeams())  && $allow_staff==false  && $allow_staff_to==false  ){
    $errors['err'] = __('Access denied. Contact admin if you believe this is in error');
   ?>
         <div id="msg_error"><?php echo $errors['err']; ?></div><?php 
        die();
}


$id = $task->getId();
$dept = $task->getDept();
$thread= $task->getThread();
$TaskTeam = $task->getTeam();
$GetCC1 = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `ost_staff`.`staff_id`=" . $thisstaff->getId();
if (($GetCC1_Res = db_query($GetCC1)) && db_num_rows($GetCC1_Res)) {
    while (list($CC1_) = db_fetch_row($GetCC1_Res)) {
        $CC1= $CC1_;  
    }   
}


$timestamp = time();
$date_time = date("Y-m-d H:i:s", $timestamp);

$iscloseable = $task->isCloseable();

if ($TaskTeam == null) {
    $canClose = ($role->hasPerm(TaskModel::PERM_CLOSE) && $iscloseable === true);
} else {
    $canClose = (($role->hasPerm(TaskModel::PERM_CLOSE) || $TaskTeam->hasMember($thisstaff)) && $iscloseable === true);
}
If ($_POST["terms_of_services"]) { 
if($_POST["terms_of_services"] == "Y" && $task->isOpen()){
    $sqr_progress="UPDATE `ost_task` SET `status`=1 WHERE `id`=".$task->getId();
    db_query($sqr_progress);
    $sqr_progress_event= " INSERT into ost_thread_event (`thread_id`,`event_id`,`staff_id`,`team_id`,`dept_id`,`topic_id`,`data`,`username`,`uid`,`uid_type`,`annulled`,`timestamp`)
    values ( ".$task->getThreadId().",16,".$thisstaff->getId().",".$task->getTeamId().",".$dept->getId().",0,'','".$CC1."',".$thisstaff->getId().",'S',0, '$date_time') ";
    db_query($sqr_progress_event);
    echo "<script>

    if ( window.history.replaceState ) {
       window.history.replaceState( null, null, window.location.href );
       
     }
     window.location.reload();
        </script>";
}
elseif($_POST["terms_of_services"] == "N" && $task->isOpen())
{
    $sqr_progress="UPDATE `ost_task` SET `status`=0 WHERE `id`=".$task->getId();
    db_query($sqr_progress);
    echo "<script>

    if ( window.history.replaceState ) {
       window.history.replaceState( null, null, window.location.href );
       
     }
     window.location.reload();
        </script>";
        echo "yas";
}
}
$actions = array();

if ($task->isOpen() && $role->hasPerm(Task::PERM_ASSIGN)) {

    if ($task->getStaffId() != $thisstaff->getId()
            && (!$dept->assignMembersOnly()
                || $dept->isMember($thisstaff))) {
        $actions += array(
                'claim' => array(
                    'href' => sprintf('#tasks/%d/claim', $task->getId()),
                    'icon' => 'icon-user',
                    'label' => __('Claim'),
                    'redirect' => 'tasks.php'
                ));
    }

    $actions += array(
            'assign/agents' => array(
                'href' => sprintf('#tasks/%d/assign/agents', $task->getId()),
                'icon' => 'icon-user',
                'label' => __('Assign to Agent'),
                'redirect' => 'tasks.php'
            ));

    $actions += array(
            'assign/teams' => array(
                'href' => sprintf('#tasks/%d/assign/teams', $task->getId()),
                'icon' => 'icon-user',
                'label' => __('Assign to Team'),
                'redirect' => 'tasks.php'
            ));
}

if ($role->hasPerm(Task::PERM_TRANSFER)) {
    $actions += array(
            'transfer' => array(
                'href' => sprintf('#tasks/%d/transfer', $task->getId()),
                'icon' => 'icon-share',
                'label' => __('Transfer'),
                'redirect' => 'tasks.php'
            ));
}

$actions += array(
        'print' => array(
            'href' => sprintf('tasks.php?id=%d&a=print', $task->getId()),
            'class' => 'no-pjax',
            'icon' => 'icon-print',
            'label' => __('Print')
        ));

if ($role->hasPerm(Task::PERM_EDIT)) {
    $actions += array(
            'edit' => array(
                'href' => sprintf('#tasks/%d/edit', $task->getId()),
                'icon' => 'icon-edit',
                'dialog' => '{"size":"large"}',
                'label' => __('Edit')
            ));
}

if ($role->hasPerm(Task::PERM_DELETE)) {
    $actions += array(
            'delete' => array(
                'href' => sprintf('#tasks/%d/delete', $task->getId()),
                'icon' => 'icon-trash',
                'class' => (strpos($_SERVER['REQUEST_URI'], 'tickets.php') !== false) ? 'danger' : 'red button',
                'label' => __('Delete'),
                'redirect' => 'tasks.php'
            ));
}

$info=($_POST && $errors)?Format::input($_POST):array();

if ($task->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">'.__('Marked overdue!').'</span>';

?>
<div>
    <div class="sticky bar">
       <div class="content">
        <div class="pull-left flush-left">
            <?php
            if ($ticket) { ?>
                <strong>
                <a id="all-ticket-tasks" href="#">
                <?php
                    echo sprintf(__('All Tasks (%s)'),
                            $ticket->getNumTasks());
                 ?></a>
                &nbsp;/&nbsp;
                <a id="reload-task" class="preview"
                    <?php
                    echo ' class="preview" ';
                    echo sprintf('data-preview="#tasks/%d/preview" ', $task->getId());
                    echo sprintf('href="#tickets/%s/tasks/%d/view" ',
                            $ticket->getId(), $task->getId()
                            );
                    ?>><?php echo sprintf(__('Task #%s'), $task->getNumber()); ?></a>
                </strong>
            <?php
            } else { ?>
               <h2>
                <a  id="reload-task"
                    href="tasks.php?id=<?php echo $task->getId(); ?>"><i
                    class="icon-refresh"></i>&nbsp;<?php
                    echo sprintf(__('Task #%s'), $task->getNumber()); ?></a>
                </h2>
            <?php
            } ?>
        </div>
        <div class="flush-right">
            <?php
            if(isset($_POST["str100"])){
                // $report = new OverviewReport($_POST['start_date']);
                // echo  $_POST['ddate']." ".$_POST['time'].":00";
                $sql1="UPDATE `ost_task` SET `duedate`= '".$_POST['ddate']." ".$_POST['time'].":00"."' WHERE `id`=".$task->getId(); 
                if(db_query($sql1)){
                    echo "<script>
        if ( window.history.replaceState ) {
          window.history.replaceState( null, null, window.location.href );
          
        }
        window.location.reload();
        </script>";
                }
                echo $sql1;
            }
            if ($ticket) { ?>
            <a  id="task-view"
                target="_blank"
                class="action-button"
                href="tasks.php?id=<?php
                 echo $task->getId(); ?>"><i class="icon-share"></i> <?php
                            echo __('View Task'); ?></a>
            <span
                class="action-button"
                data-dropdown="#action-dropdown-task-options">
                <i class="icon-caret-down pull-right"></i>
                <a class="task-action"
                    href="#task-options"><i
                    class="icon-reorder"></i> <?php
                    echo __('Actions'); ?></a>
            </span>
            <div id="action-dropdown-task-options"
                class="action-dropdown anchor-right">
                <ul>

                    <?php
                    if (!$task->isOpen()) { ?>
                    <li>
                        <a class="no-pjax task-action"
                            href="#tasks/<?php echo $task->getId(); ?>/reopen"><i
                            class="icon-fixed-width icon-undo"></i> <?php
                            echo __('Reopen');?> </a>
                    </li>
                    <?php
                    } elseif ($canClose) {
                    ?>
                    <li>
                        <a class="no-pjax task-action"
                            href="#tasks/<?php echo $task->getId(); ?>/close"><i
                            class="icon-fixed-width icon-ok-circle"></i> <?php
                            echo __('Close');?> </a>
                    </li>
                    <?php
                    } ?>
                    <?php
                    foreach ($actions as $a => $action) { ?>
                    <li <?php if ($action['class']) echo sprintf("class='%s'", $action['class']); ?> >
                        <a class="no-pjax task-action" <?php
                            if ($action['dialog'])
                                echo sprintf("data-dialog-config='%s'", $action['dialog']);
                            if ($action['redirect'])
                                echo sprintf("data-redirect='%s'", $action['redirect']);
                            ?>
                            href="<?php echo $action['href']; ?>"
                            <?php
                            if (isset($action['href']) &&
                                    $action['href'][0] != '#') {
                                echo 'target="blank"';
                            } ?>
                            ><i class="<?php
                            echo $action['icon'] ?: 'icon-tag'; ?>"></i> <?php
                            echo  $action['label']; ?></a>
                    </li>
                <?php
                } ?>
                </ul>
            </div>
            <?php
           } else {
             ?>
               
    
    <?php
        $title = TaskForm::getInstance()->getField('title');
        if($task->getIsPrivate()){
        echo " <span style='font-size: 20px; font-weight:bold'>".$title->display($task->getTitle())."</span>"." <span style='color:green;'>(Private)</span>";
        }
        else{
            echo " <span style='font-size: 20px; font-weight:bold'>".$title->display($task->getTitle())."</span>"." <span style='color:red;'>(Not Private)</span>";
        }
    ?>
    



<?php
               if( $thisstaff == $task->getAssigned() || in_array($task->getTeamId(), $thisstaff->getTeams()) ){ 
                $_SESSION['Child_task'] = $id ;
                   
                   ?>
             <span
                    class="action-button">
                    <a 
                        href="#" class="link"
                        title="<?php echo __('Open New Sub Task'); ?>">
                        <i class="icon-comment"></i></a>

                </span>
               <?php }
               
               $sql="SELECT `id` FROM `ost_bookmark` WHERE `staff_id` = ".$thisstaff->getId()." AND `task_id` = ".$task->getId();
            //    echo $sql;
            if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
    
                $ID = db_fetch_row($sql_Res);
            }
                
            $sqlIsParent="SELECT `parent_task_id` FROM `ost_task` WHERE `id` = ".$task->getId();
            //    echo $sqlIsParent;
            if (($sqlIsParent_Res = db_query($sqlIsParent)) && db_num_rows($sqlIsParent_Res)) {
    
                $IDIsParent = db_fetch_row($sqlIsParent_Res);
            }

            if($IDIsParent[0] == null){
                if( $thisstaff == $task->getAssigned() || in_array($task->getTeamId(), $thisstaff->getTeams()) ){
                ?>
                <!-- Mark task as Important -->
                <span 
                class="action-button">
                <a 
                    href=<?php echo sprintf('tasks.php?SubTask=%d', $task->getId()) ?> 
                    title="<?php echo __('Set as SubTask'); ?>">
                    <i class="icon-retweet"></i></a>
            
            </span><?php
                }
            }
if($ID[0] == null){
    ?>
    <!-- Mark task as Important -->
    <span 
    class="action-button">
    <a 
        href=<?php echo sprintf('tasks.php?Mark=%d', $task->getId()) ?> 
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
        href=<?php echo sprintf('tasks.php?UnMark=%d', $ID[0]) ?> 
        title="<?php echo __('Mark as important'); ?>">
        <i class="icon-bookmark" style="background-color: red;" ></i></a>

</span><?php
}
             
                             ?>                
              
                <span
                    class="action-button"
                    data-dropdown="#action-dropdown-tasks-status">
                    <i class="icon-caret-down pull-right"></i>
                    <a class="tasks-status-action"
                        href="#statuses"
                        data-placement="bottom"
                        data-toggle="tooltip"
                        title="<?php echo __('Change Status'); ?>"><i
                        class="icon-flag"></i></a>
                </span>
                <div id="action-dropdown-tasks-status"
                    class="action-dropdown anchor-right">
                    <ul>
                        <?php
                        if ($task->isClosed()) { ?>
                        <li>
                            <a class="no-pjax task-action"
                                href="#tasks/<?php echo $task->getId(); ?>/reopen"><i
                                class="icon-fixed-width icon-undo"></i> <?php
                                echo __('Reopen');?> </a>
                        </li>
                        <?php
                        } elseif ($canClose) {
                        ?>
                        <li>
                            <a class="no-pjax task-action"
                                href="#tasks/<?php echo $task->getId(); ?>/close"><i
                                class="icon-fixed-width icon-ok-circle"></i> <?php
                                echo __('Close');?> </a>
                        </li>
                        <?php
                        } ?>
                    </ul>
                </div>
                <?php
                // Assign
                unset($actions['claim'], $actions['assign/agents'], $actions['assign/teams']);
                if (($task->isOpen() && $role->hasPerm(Task::PERM_ASSIGN)) || ($task->isOpen() && $task->getAssignorId()==$thisstaff->getId() ) ) {?>
                <span class="action-button"
                    data-dropdown="#action-dropdown-assign"
                    data-placement="bottom"
                    data-toggle="tooltip"
                    title=" <?php echo $task->isAssigned() ? __('Reassign') : __('Assign'); ?>"
                    >
                    <i class="icon-caret-down pull-right"></i>
                    <a class="task-action" id="task-assign"
                        data-redirect="tasks.php"
                        href="#tasks/<?php echo $task->getId(); ?>/assign"><i class="icon-user"></i></a>
                </span>
                <div id="action-dropdown-assign" class="action-dropdown anchor-right">
                  <ul>
                    <?php
                    // Agent can claim team assigned ticket
                    if ($task->getStaffId() != $thisstaff->getId()
                            && (!$dept->assignMembersOnly()
                                || $dept->isMember($thisstaff))
                            ) { ?>
                     <li><a class="no-pjax task-action"
                        data-redirect="tasks.php"
                        href="#tasks/<?php echo $task->getId(); ?>/claim"><i
                        class="icon-chevron-sign-down"></i> <?php echo __('Claim'); ?></a>
                    <?php
                    } ?>
                     <li><a class="no-pjax task-action"
                        data-redirect="tasks.php"
                        href="#tasks/<?php echo $task->getId(); ?>/assign/agents"><i
                        class="icon-user"></i> <?php echo __('Agent'); ?></a>
                     <li><a class="no-pjax task-action"
                        data-redirect="tasks.php"
                        href="#tasks/<?php echo $task->getId(); ?>/assign/teams"><i
                        class="icon-group"></i> <?php echo __('Team'); ?></a>
                  </ul>
                </div>
                <?php
                } ?>
                <?php
                foreach ($actions as $action) {?>
                <span class="action-button <?php echo $action['class'] ?: ''; ?>">
                    <a class="<?php echo ($action['class'] == 'no-pjax') ? '' : 'task-action'; ?>"
                        <?php
                        if ($action['dialog'])
                            echo sprintf("data-dialog-config='%s'", $action['dialog']);
                        if ($action['redirect'])
                            echo sprintf("data-redirect='%s'", $action['redirect']);
                        ?>
                        href="<?php echo $action['href']; ?>"
                        data-placement="bottom"
                        data-toggle="tooltip"
                        title="<?php echo $action['label']; ?>">
                        <i class="<?php
                        echo $action['icon'] ?: 'icon-tag'; ?>"></i>
                    </a>
                </span>
           <?php
                }
           } ?>
        </div>
    </div>
   </div>
</div>

<!-- <div class="clear tixTitle has_bottom_border">
    <h3>
    <?php
        // $title = TaskForm::getInstance()->getField('title');
        // if($task->getIsPrivate()){
        // echo $title->display($task->getTitle())." <span style='color:green;'>(Private)</span>";
        // }
        // else{
        //     echo $title->display($task->getTitle())." <span style='color:red;'>(Not Private)</span>";
        // }
    ?>
    </h3>
</div> -->
<?php
if (!$ticket) { ?>
    <table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">
        <tr>
            <td width="50%">
                <table border="0" cellspacing="" cellpadding="4" width="100%">
                    <tr>
                        <th width="100"><?php echo __('Status');?>:</th>
                        <td><?php echo $task->getStatus(); ?></td>
                    </tr>

                    <tr>
                        <th><?php echo __('Created');?>:</th>
                        <td><?php echo Format::datetime($new_date = date("Y-m-d H:i:s", strtotime('+3 hours', strtotime( $task->getCreateDate())))); ?></td>
                    </tr>
                    <?php
                    if($task->isOpen()){ ?>
                    <tr>
                        <th><?php echo __('Due Date');?>:</th>
                        <td><?php echo $task->duedate ?
                        Format::datetime(date("Y-m-d H:i:s", strtotime('+3 hours', strtotime($task->duedate)))) : '<span
                        class="faded">&mdash; '.__('None').' &mdash;</span>'; ?></td>
                    </tr>
                    <?php
                    }else { ?>
                    <tr>
                        <th><?php echo __('Completed');?>:</th>
                        <td><?php echo Format::datetime( date("Y-m-d H:i:s", strtotime('+3 hours', strtotime( $task->getCloseDate())))); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
            </td>
            <td width="50%" style="vertical-align:top">
                <table cellspacing="0" cellpadding="4" width="100%" border="0">

                    <tr>
                        <th><?php echo __('Department');?>:</th>
                        <td><?php echo Format::htmlchars($task->dept->getName()); ?></td>
                    </tr>
                    <?php
                    if ($task->isOpen()) { ?>
                    <tr>
                        <th width="100"><?php echo __('Assigned To');?>:</th>
                        <td>
                            <?php
                            if($task->getStaffId() != 0 ){
                            if ($assigned=$task->getAssigned())
                                echo Format::htmlchars($assigned);
                            else
                                echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                            }
                            else{
                                $_SESSION['Showteam']=$task->getTeamId();
                                if ($assigned=$task->getAssigned())
                                    echo sprintf('<span><a class="preview"
                                    href="#" data-preview="#tasks/%s/preview"><span>%s</span></a></span>',
                                    $task->getId(),
                                    Format::htmlchars($assigned));
                                else
                                    echo '<span class="faded">&mdash; '.__('Unassigned').' &mdash;</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    } else { ?>
                    <tr>
                        <th width="100"><?php echo __('Closed By');?>:</th>
                        <td>
                            <?php
                            if (($staff = $task->getStaff()))
                                echo Format::htmlchars($staff->getName());
                            else
                                echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
                            ?>
                        </td>
                    </tr>
                    <?php
                    } ?>
                    <tr>
                        <th><?php echo __('Collaborators');?>:</th>
                        <td>
                            <?php
                            $collaborators = __('Collaborators');
                            if ($task->getThread()->getNumCollaborators())
                                $collaborators = sprintf(__('Collaborators (%d)'),
                                        $task->getThread()->getNumCollaborators());

                            echo sprintf('<span><a class="collaborators preview"
                                    href="#thread/%d/collaborators"><span
                                    id="t%d-collaborators">%s</span></a></span>',
                                    $task->getThreadId(),
                                    $task->getThreadId(),
                                    $collaborators);
                           ?>
                        </td>
                    </tr>
                    <?php if ($task->getCollab1() != null ){ ?>
                    <tr>
                        <th><?php echo __('CC1');?>:</th>
                        <td>
                            <?php
                            $cc = __('CC1');
                            
                                $GetCC1 = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `ost_staff`.`staff_id`=" . $task->getCollab1();
                                if (($GetCC1_Res = db_query($GetCC1)) && db_num_rows($GetCC1_Res)) {
                                    while (list($CC1_) = db_fetch_row($GetCC1_Res)) {
                                        $CC1= $CC1_;  
                                    }   
                                }
                                $cc = sprintf(__('CC1 (%d)'),$CC1);
                                echo sprintf($CC1);
                            
                                
                           ?>
                        </td>
                    </tr>
                <?php } ?>


                <?php if ($task->getCollab2() != null ){ ?>
                    <tr>
                        <th><?php echo __('CC2');?>:</th>
                        <td>
                            <?php
                            $cc = __('CC2');
                            
                                $GetCC1 = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `ost_staff`.`staff_id`=" . $task->getCollab2();
                                if (($GetCC1_Res = db_query($GetCC1)) && db_num_rows($GetCC1_Res)) {
                                    while (list($CC1_) = db_fetch_row($GetCC1_Res)) {
                                        $CC1= $CC1_;  
                                    }   
                                }
                                $cc = sprintf(__('CC2 (%d)'),$CC1);
                                echo sprintf($CC1);
                            
                                
                           ?>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($task->getCollab3() != null ){ ?>
                    <tr>
                        <th><?php echo __('CC3');?>:</th>
                        <td>
                            <?php
                            $cc = __('CC3');
                            
                                $GetCC1 = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `ost_staff`.`staff_id`=" . $task->getCollab3();
                                if (($GetCC1_Res = db_query($GetCC1)) && db_num_rows($GetCC1_Res)) {
                                    while (list($CC1_) = db_fetch_row($GetCC1_Res)) {
                                        $CC1= $CC1_;  
                                    }   
                                }
                                $cc = sprintf(__('CC3 (%d)'),$CC1);
                                echo sprintf($CC1);
                            
                                
                           ?>
                        </td>
                    </tr>
                <?php } 
                $teamID=array();
                $IfteamCC="SELECT `collab_team` FROM `ost_task` WHERE `id`=".$task->getId();
                if (($IfteamCC_Res = db_query($IfteamCC)) && db_num_rows($IfteamCC_Res)) {
                    while (list($teamID_) = db_fetch_row($IfteamCC_Res)) {
                        array_push($teamID, $teamID_);
                    }
                }
                
?>
                <?php if ($teamID[0] != 0 ){ ?>
                    <tr>
                        <th><?php echo __('CC Team');?>:</th>
                        <td>
                            <?php
                            $cc = __('CC Team');
                            
                                $GetCC1 = "SELECT `name` FROM `ost_team` WHERE `team_id` = " . $teamID[0];
                                if (($GetCC1_Res = db_query($GetCC1)) && db_num_rows($GetCC1_Res)) {
                                    while (list($CC1_) = db_fetch_row($GetCC1_Res)) {
                                        $CC1= $CC1_;  
                                    }   
                                }
                                $cc = sprintf(__('CC Team (%d)'),$CC1);
                                echo sprintf($CC1);
                            
                                
                           ?>
                        </td>
                    </tr>
                <?php } 
                if($task->getCollab3() == null || $task->getCollab2() == null || $task->getCollab1() == null){
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
                ?>
                <tr>
                    <th>
                <button id="showit">+Add CC</button>
                <div id="myform" style="display:none;">
                <form method="post" class="org" action="" id="formID"  enctype="multipart/form-data">
                <?php csrf_token(); ?>
 <!-- content Staff-->
                            <select class="modal-body" id="ddlViewst" name="ddlViewst">
                            <option disabled selected value> -- select an agent -- </option>
                            <?php foreach ($staffname as $index => $item) {


                            ?>
                                <option value="<?php echo $staffid[$index]; ?>"><?php echo $item; ?></option>
                            <?php } ?>
                        </select>
                        <span class="buttons pull-right">
                            <input type="submit" value="<?php echo __('submit'); ?>" name="str1" id='submitbtn' onclick="this.style.visibility = 'hidden'">
                        </span>
                    
            </form>

                            </div>
                            </th>
                </tr>
               

                            <script>
        $(document).ready(function(){
        $("#showit").click(function(){
       $("#myform").css("display","block");
   });
});
                            </script>
                    <?php 
                }
                if (isset($_POST["str1"])) {
                        if (isset($_POST['ddlViewst'])) {
                            $staffID = $_POST['ddlViewst'];
                            // echo "staffID: " . $staffID . "<br>";
                            if($task->getCollab1() == null){
                                $sql1="UPDATE `ost_task` SET `collab_1`= ".$_POST['ddlViewst']." WHERE `id`=".$task->getId(); 
                                if (db_query($sql1)){ 
                                         echo "<script>
                                         $(document).ready(function(){
                                         $('#myform').css('display','none');
                                     });
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
                                }
                            }
                           
                            elseif($task->getCollab2() == null){
                                $sql2="UPDATE `ost_task` SET `collab_2`= ".$_POST['ddlViewst']." WHERE `id`=".$task->getId(); 
                                if (db_query($sql2)){ 
                                         echo "<script>
                                         $(document).ready(function(){
                                         $('#myform').css('display','none');
                                     });
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
                                }
                            }
                            elseif($task->getCollab3() == null){
                                $sql3="UPDATE `ost_task` SET `collab_3`= ".$_POST['ddlViewst']." WHERE `id`=".$task->getId(); 
                                if (db_query($sql3)){ 
                                         echo "<script>
                                         $(document).ready(function(){
                                         $('#myform').css('display','none');
                                     });
                                     if ( window.history.replaceState ) {
                                        window.history.replaceState( null, null, window.location.href );
                                        
                                      }
                                      window.location.reload();
                                         </script>";
                                }
                             }
    } else {
        $staffID = "Null";
    }

                }
              $GetParentTasksQ = "SELECT `ost_task`.`id`,`ost_task`.`number` FROM `ost_task`  WHERE `ost_task`.`id` =(SELECT  `parent_task_id` FROM `ost_task`  WHERE  `ost_task`.`id`=" . $task->getId().")";
              $ID_TEST=NULL;
              $Number_TEST=NULL;
              if (($GetParentTasksQ_Res = db_query($GetParentTasksQ)) && db_num_rows($GetParentTasksQ_Res)) {
                  while (list($ID, $Number) = db_fetch_row($GetParentTasksQ_Res)) {
                      $ID_TEST= $ID;
                      $Number_TEST=$Number;
                  }
                  
              }
              
                  if(!is_null($ID_TEST)){
                      echo "
                      <tr>
                      <th> Parent Task : </th>";
                      
                      if($task->getAssignorId()== $thisstaff->getid()){?>
                        <td style='border-right: 1px solid #eeeeef;'  style='cursor:pointer;'> <a class="preview" href="tasks.php?id=<?php echo $ID_TEST; ?>" data-preview="#tasks/<?php echo $ID_TEST; ?>/preview"><?php echo '#'.$Number_TEST; ?></a></td>
                      <?php }
                      else {?>

                        <td style='border-right: 1px solid #eeeeef;'  style='cursor:pointer;'>  <?php echo '#'.$Number_TEST; ?></td>

                      <?php }?>

                      
                     
                  </tr>
                      
                <?php  }  
                
                    ?>
                    <tr>
                    <th>
                    <?php
                    $team_lead=array();
                        $sql_getTeam_Lead="SELECT `lead_id` FROM `ost_team` WHERE `team_id` =".$task->getTeamId();
                        if (($getTeam_Lead_Res = db_query($sql_getTeam_Lead)) && db_num_rows($getTeam_Lead_Res )) {
                            while (list($ID) = db_fetch_row($getTeam_Lead_Res)) {
                                array_push($team_lead, $ID);
                            }
                            
                        }
                        // echo $team_lead[0];
                        // $sql_getTeam_Lead="SELECT `lead_id` FROM `ost_team` WHERE `team_id` IN (" . implode(",", $thisstaff->getTeams()).")";
                        // echo $sql_getTeam_Lead;
                    
                    if($task->isOpen() && ($task->getAssignorId()== $thisstaff->getid() || $task->getStaffId()== $thisstaff->getid() || ($team_lead[0]==$thisstaff->getId()))){ ?> 
        <button id='datetimepicker1'>Edit Dudate</button>
<div id="myform100" style="display:none;">
<form method="post" class="org" action="" id="formID"  enctype="multipart/form-data">
                <?php csrf_token(); ?>
 <!-- content Staff-->
 <div class='input-group date' id='datetimepicker1'>
                     <input type="date" name="ddate">
                     <input type="time" name="time">
                  
                     <span class="buttons ">
                            <input type="submit" value="<?php echo __('submit'); ?>" name="str100" id='submitbtn' onclick="this.style.visibility = 'hidden'">
                        </span>
                  </div>
                 
                    
            </form>
</div>
          
                      <?php } ?>  
                    
                    </th>
                    <script>
        $(document).ready(function(){
        $("#datetimepicker1").click(function(){
       $("#myform100").css("display","block");
   });
});
                            </script>
                             <?php
     ?>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <style>


td {  }


</style> 
    <?php 
       $z = array();
       $GetChildTasksQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`updated`,`ost_task__cdata`.`title`, CONCAT(too.`firstname`, ' ', too.`lastname`),
       CONCAT(ffrom.`firstname`, ' ', ffrom.`lastname`)
       FROM `ost_task` 
       INNER JOIN `ost_task__cdata` ON `ost_task`.`id`=`ost_task__cdata`.`task_id`
       INNER JOIN `ost_staff` As too ON too.`staff_id`=`ost_task`.`staff_id` 
       INNER JOIN `ost_staff` As ffrom ON ffrom.`staff_id`=`ost_task`.`assignor_id`
       
       WHERE `parent_task_id`=" . $task->getId();

if (($GetChildTasksQ_Res = db_query($GetChildTasksQ)) && db_num_rows($GetChildTasksQ_Res)) {
    while (list($ID, $Number,$Update, $Subject, $TooStaff, $FromStaff) = db_fetch_row($GetChildTasksQ_Res)) {
        array_push($z, $ID);
    }
    
}


    
    
    ?>
    <table style="margin-top:2em" class="list" border="0" cellspacing="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <?php if (count($z) > 0) {
                                echo "<th width='3%'>number</th>";
                                echo "<th width='5%'>Last Update</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='20%'>From</th>";
                                echo "<th width='10%'>To</th>";
                                
                            }?>
                        </tr>
                   
                    </thead>
                    <tbody>  
                <?php

if (($GetChildTasksQ_Res = db_query($GetChildTasksQ)) && db_num_rows($GetChildTasksQ_Res)) {
    while (list($ID, $Number,$Update, $Subject, $TooStaff, $FromStaff) = db_fetch_row($GetChildTasksQ_Res)) {
      
    echo "<tr  style='border: solid border-width: 5px #000000'>";
    ?>

        <td style='border-right: 1px solid #eeeeef;' width='3%' align='center' style='cursor:pointer;'> <a class="preview" href="tasks.php?id=<?php echo $ID; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
       <?php
       echo"
        <td style='border-right: 1px solid #eeeeef;' width='5%'>$Update</td>
        <td style='border-right: 1px solid #eeeeef;'  width='20%'>$Subject</td>
        <td style='border-right: 1px solid #eeeeef;'  width='20%'>$FromStaff</td>
        <td style='border-right: 1px solid #eeeeef;'  width='10%'> $TooStaff</td>
        
    </tr>
";
}}
                
                
                ?>
                 </tbody>
                </table>
    <br>
    <table class="ticket_info" cellspacing="0" cellpadding="0" width="100%" border="0">
    <?php
    $idx = 0;
    foreach (DynamicFormEntry::forObject($task->getId(),
                ObjectModel::OBJECT_TYPE_TASK) as $form) {
        $answers = $form->getAnswers()->exclude(Q::any(array(
            'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
            'field__name__in' => array('title')
        )));
        if (!$answers || count($answers) == 0)
            continue;

        ?>
            <tr>
            <td colspan="2">
                <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php foreach($answers as $a) {
                    if (!($v = $a->display())) continue; ?>
                    <tr>
                        <th width="100"><?php
                            echo $a->getField()->get('label');
                        ?>:</th>
                        <td><?php
                            echo $v;
                        ?></td>
                    </tr>
                    <?php
                } ?>
                </table>
            </td>
            </tr>
        <?php
        $idx++;
    } ?>
    </table>
<?php
} ?>
<div class="clear"></div>
<div id="task_thread_container">
    <div id="task_thread_content" class="tab_content">
     <?php
     $task->getThread()->render(array('M', 'R', 'N'),
             array(
                 'mode' => Thread::MODE_STAFF,
                 'container' => 'taskThread',
                 'sort' => $thisstaff->thread_view_order
                 )
             );
     ?>
   </div>
</div>
<div class="clear"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php }

if ($ticket)
    $action = sprintf('#tickets/%d/tasks/%d',
            $ticket->getId(), $task->getId());
else
    $action = 'tasks.php?id='.$task->getId();
?>
<div id="task_response_options" class="<?php echo $ticket ? 'ticket_task_actions' : ''; ?> sticky bar stop actions">
    <ul class="tabs">
        <?php
           $DueDate = new DateTime("Asia/Damascus");
   //$DueDate->add(new DateInterval('PT' . $HoursToAdd . 'H'));
   $DueDate = $DueDate->format('Y-m-d H:i:s T');
   //echo $DueDate;
        // if ($role->hasPerm(TaskModel::PERM_REPLY)) {
             ?>
        <li class="active"><a href="#task_reply"><?php echo __('Post Update');?></a></li>
        <!--<li><a href="#task_note">--><?php //echo __('Post Internal Note');?><!--</a></li>-->
        <?php
       // }
        ?>
    </ul>
    <?php
    //if ($role->hasPerm(TaskModel::PERM_REPLY)) { ?>
    <form id="task_reply" class="tab_content spellcheck save"
        action="<?php echo $action; ?>"
        name="task_reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $task->getId(); ?>">
        <input type="hidden" name="a" value="postreply">
        <input type="hidden" name="lockCode" value="<?php echo ($mylock) ? $mylock->getCode() : ''; ?>">
        <span class="error"></span>
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
            <tbody id="collab_sec" style="display:table-row-group">
             <tr>
                <td>
                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $thread->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    if ($thread->getNumCollaborators())
                        $recipients = sprintf(__('(%d of %d)'),
                        $task->getThread()->getNumCollaborators(),
                        $task->getThread()->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#thread/%d/collaborators"> %s &nbsp;<span id="t%d-recipients">%s</span></a></span>',
                            $thread->getId(),
                            __('Collaborators'),
                            $thread->getId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <tbody id="update_sec">
            <tr>
                <td>
                    <div class="error"><?php echo $errors['response']; ?></div>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="task-response" cols="50"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __( 'Start writing your update here.'); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?> draft draft-delete" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('task.response', $task->getId(), $info['task.response']);
    echo $attrs; ?>><?php echo $draft ?: $info['task.response'];
                    ?></textarea>
                <div id="task_response_form_attachments" class="attachments">
                <?php
                    if ($reply_attachments_form)
                        print $reply_attachments_form->getField('attachments')->render();
                ?>
                </div>
               </td>
            </tr>
            <tr>
                <td>
                <?php $sql_p="SELECT `CBC` FROM `ost_task` WHERE `id`=".$task->getId();
                if (($P_Res = db_query($sql_p)) && db_num_rows($P_Res)) {
                    $CBC = db_fetch_row($P_Res);
                }
                ?>
                    <div><?php echo __('Status');?>
                        <span class="faded"> - </span>
                        <select  name="task:status" <?php
                        if ($CBC[0]==1 && $thisstaff->getId()!=$task->getAssignorId())
                        {
                            echo "disabled";
                        }
                        ?> >
                            <option value="open" <?php
                                echo $task->isOpen() ?
                                'selected="selected"': ''; ?>> <?php
                                echo __('Open'); ?></option>
                            <?php
                            $canclosesql="SELECT `Flag_can_close` FROM `ost_staff` WHERE `staff_id`=".$thisstaff->getId();
                            if (($canclosesql_Res = db_query($canclosesql)) && db_num_rows($canclosesql_Res)) {
  
                                $CanClose = db_fetch_row($canclosesql_Res);
                            }
                            // echo $canclose;
                            
                            if ($task->isClosed() || $canClose || ($CanClose[0]==1 && $thisstaff->getId()==$task->getAssignorId())  || ($CanClose[0]==1 && $thisstaff->getId()==$task->getStaffId()) || ($CanClose[0]==1 && in_array($task->getTeamId(), $thisstaff->getTeams())) ) {
                                ?>
                            <option value="closed" <?php
                                echo $task->isClosed() ?
                                'selected="selected"': ''; ?>> <?php
                                echo __('Closed'); ?></option>
                            <?php
                            } ?>
                        </select>
                        &nbsp;<span class='error'><?php echo
                        $errors['task:status']; ?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <?php
                $sql_p="SELECT `status` FROM `ost_task` WHERE `id`=".$task->getId();
                if (($P_Res = db_query($sql_p)) && db_num_rows($P_Res)) {
                    $Progress = db_fetch_row($P_Res);
                }
                ?>
                <td>
                    <!--yaseen edit this-->
                    <?php if($task->isOpen()){ ?>
                <form action="post">
                    <lable>
                    In Progress
                    <?php 
                 if ($Progress[0]==1){
                    ?>
                    
                    <select name="terms_of_services" id="terms_of_services">
                        <option value="Y">yes</option>
                    <option value="N">No</option>
                    </select>
                    <?php 
                    }?>
                        <?php 
                  if  ($Progress[0]==0){
                    ?>
                    
                    <select name="terms_of_services" id="terms_of_services">
                        <option value="N">No</option>
                    <option value="Y">yes</option>
                    
                    </select>
                    <?php 
                    }?>
                    <lable>
                    <input type="submit" value="Go">
                    </form>
                    <?php } ?>
                </td>
            </tr>
        </table>
       <p  style="text-align:center;">
           <input class="save pending" type="submit" value="<?php echo __('Post Update');?>">
           <input type="reset" value="<?php echo __('Reset');?>">
       </p>
       <?php if($task->isOpen()){ ?>
                   
                        <p><?php echo __('Due Date');?>:</p>
                        <p><?php echo Format::datetime($new_date = date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($task->duedate))))  ?
                        Format::datetime($new_date = date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($task->duedate)))) : '<span
                        class="faded">&mdash; '.__('None').' &mdash;</span>'; ?></p>
                    
                    <?php
                    }?>


           
    </form> 
    
    
 </div>
<?php
echo $reply_attachments_form->getMedia();
?>
<script type="text/javascript">
$(function() {
    $(document).off('.tasks-content');
    $(document).on('click.tasks-content', '#all-ticket-tasks', function(e) {
        e.preventDefault();
        $('div#task_content').hide().empty();
        $('div#tasks_content').show();
        return false;
     });

    $(document).off('.task-action');
    $(document).on('click.task-action', 'a.task-action', function(e) {
        e.preventDefault();
        var url = 'ajax.php/'
        +$(this).attr('href').substr(1)
        +'?_uid='+new Date().getTime();
        var $options = $(this).data('dialogConfig');
        var $redirect = $(this).data('redirect');
        $.dialog(url, [201], function (xhr) {
            if (!!$redirect)
                window.location.href = $redirect;
            else
                $.pjax.reload('#pjax-container');
        }, $options);

        return false;
    });

    $(document).off('.tf');
    $(document).on('submit.tf', '.ticket_task_actions form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $container = $('div#task_content');
        $.ajax({
            type:  $form.attr('method'),
            url: 'ajax.php/'+$form.attr('action').substr(1),
            data: $form.serialize(),
            cache: false,
            success: function(resp, status, xhr) {
                $container.html(resp);
                $('#msg_notice, #msg_error',$container)
                .delay(5000)
                .slideUp();
            }
        })
        .done(function() {
            $('#loading').hide();
            $.toggleOverlay(false);
        })
        .fail(function() { });
     });
    <?php
    if ($ticket) { ?>
    $('#ticket-tasks-count').html(<?php echo $ticket->getNumTasks(); ?>);
   <?php
    } ?>
});
</script>
<script type="text/javascript">
        $(function(){
            $(document).off('.link');
                $(document).on('click.link', 'a.link', function(e) {
                    e.preventDefault();
                    var url = 'ajax.php/tasks/add' +
                        '?_uid=' + new Date().getTime();
                        console.log(url);
                    var $options = $(this).data('dialogConfig');
                    $.dialog(url, [201], function(xhr) {
                        var tid = parseInt(xhr.responseText);
                        if (tid) {
                            window.location.href = 'tasks.php?id=' + tid;
                        } else {
                            $.pjax.reload('#pjax-container');
                        }
                    }, $options);
                    return false;
                });
                $('[data-toggle=tooltip]').tooltip();
            });
        </script>