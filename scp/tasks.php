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
require_once(INCLUDE_DIR . 'class.task.php');
require_once(INCLUDE_DIR . 'class.export.php');
session_start();
unset($_SESSION['Child_task']);
unset($_SESSION['Showteam']);
$page = '';
$task = null; //clean start.
if ($_REQUEST['id']) {
    if (!($task=Task::lookup($_REQUEST['id'])))
        $errors['err'] = sprintf(__('%s: Unknown or invalid ID.'), __('task'));
    elseif (!$task->checkStaffPerm($thisstaff)) {
        $errors['err'] = __('Access denied. Contact admin if you believe this is in error');
        $task = null;
    }
}

if($_REQUEST['Mark']){
    // echo $_REQUEST['Mark'];
    $query ="INSERT INTO `ost_bookmark` (`staff_id`, `task_id`) VALUES (".$thisstaff->getId().",".$_REQUEST['Mark'].")";
    db_query($query);
  
}

if($_REQUEST['UnMark']){
    // echo $_REQUEST['Mark'];
    $query ="DELETE FROM `ost_bookmark` WHERE `id`=".$_REQUEST['UnMark'];
    // echo $query;
    db_query($query);
  
}
// Configure form for file uploads
$note_attachments_form = new SimpleForm(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:note',
        'configuration' => array('extensions'=>'')))
));

$reply_attachments_form = new SimpleForm(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:reply',
        'configuration' => array('extensions'=>'')))
));

//At this stage we know the access status. we can process the post.
if($_POST && !$errors):
    if ($task) {
        //More coffee please.
        $errors=array();
        $role = $thisstaff->getRole($task->getDept());
        switch(strtolower($_POST['a'])):
        case 'postnote': /* Post Internal Note */
            $vars = $_POST;
            $vars['files'] = $note_attachments_form->getField('attachments')->getFiles();

            $wasOpen = ($task->isOpen());
            if(($note=$task->postNote($vars, $errors, $thisstaff))) {

                $msg=__('Internal note posted successfully');
                // Clear attachment list
                $note_attachments_form->setSource(array());
                $note_attachments_form->getField('attachments')->reset();

                if($wasOpen && $task->isClosed())
                    $task = null; //Going back to main listing.
                else
                    // Task is still open -- clear draft for the note
                    Draft::deleteForNamespace('task.note.'.$task->getId(),
                        $thisstaff->getId());

            } else {
                if(!$errors['err'])
                    $errors['err'] = __('Unable to post internal note - missing or invalid data.');

                $errors['postnote'] = sprintf('%s %s',
                    __('Unable to post the note.'),
                    __('Correct any errors below and try again.'));
            }
            
            echo "<script>window.history.replaceState({}, document.title, window.location.toString());</script>";
            break;
        case 'postreply': /* Post an update */
            $vars = $_POST;
            $vars['files'] = $reply_attachments_form->getField('attachments')->getFiles();
            $wasOpen = ($task->isOpen());
            
            if (($response = $task->postReply($vars, $errors))) {
                $msg = __('Reply posted successfully');
                // Clear attachment list
                $reply_attachments_form->setSource(array());
                $reply_attachments_form->getField('attachments')->reset();

                if ($wasOpen && $task->isClosed())
                    $task = null; //Going back to main listing.
                else
                    // Task is still open -- clear draft for the note
                    Draft::deleteForNamespace('task.reply.' . $task->getId(), $thisstaff->getId());
            } else {
                if (!$errors['err'])
                    $errors['err'] = __('Unable to post the reply - missing or invalid data.');

                $errors['postreply'] = sprintf('%s %s',
                    __('Unable to post the reply.'),
                    __('Correct any errors below and try again.'));
            }

            echo "<script>window.history.replaceState({}, document.title, window.location.toString());</script>";
            break;
        default:
            $errors['err']=__('Unknown action');
        endswitch;

        switch(strtolower($_POST['do'])):
          case 'addcc':
              $errors = array();
              if (!$role->hasPerm(Ticket::PERM_EDIT)) {
                  $errors['err']=__('Permission Denied. You are not allowed to add collaborators');
              } elseif (!$_POST['user_id'] || !($user=User::lookup($_POST['user_id']))) {
                  $errors['err'] = __('Unknown user selected');
            } elseif ($c2 = $task->addCollaborator($user, array(), $errors)) {
                  $c2->setFlag(Collaborator::FLAG_CC, true);
                  $c2->save();
                  $msg = sprintf(__('Collaborator %s added'),
                      Format::htmlchars($user->getName()));
              }
              else
                $errors['err'] = sprintf('%s %s', __('Unable to add collaborator.'), __('Please try again!'));
              break;
      endswitch;
    }
    if(!$errors)
        $thisstaff->resetStats(); //We'll need to reflect any changes just made!
endif;

if (isset($_GET['rt_delete']) && $_GET['rt_delete'] !== "") {
    if (strpos($_GET['rt_delete'], ',') !== false) {
        $RequiredData = explode(',', $_GET['rt_delete']);

        if (isset($RequiredData[0]) && isset($RequiredData[1])) {
            $DeleteRecurringTask_Q = "DELETE FROM `ost_recurring_tasks` WHERE `rt_id` LIKE '" . $RequiredData[0] . "';";
            
            if (($DeleteRecurringTask_Res = db_query($DeleteRecurringTask_Q)) && db_affected_rows($DeleteRecurringTask_Res)) {
                $DeleteAssociatedEvent_Q = "DROP EVENT IF EXISTS `$RequiredData[1]`;";
                
                if ($DeleteAssociatedEvent_Res = db_query($DeleteAssociatedEvent_Q)) {
                    $msg = "Recurring Task " . $RequiredData[0] . " successfully deleted!";
                } else {
                    $errors['err'] = __('Recurring Task wasn\'t successfully deleted!');
                }
            } else {
                $errors['err'] = __('Recurring Task wasn\'t successfully deleted!');
            }
        } else {
            $errors['err'] = __('Recurring Task wasn\'t successfully deleted!');
        }
    } else {
        $errors['err'] = __('Recurring Task wasn\'t successfully deleted!');
    }

    echo "<script>window.history.replaceState({}, document.title, window.location.toString().substring(0, window.location.toString().indexOf('?')));</script>";
} else if (isset($_GET['rt_toggle']) && $_GET['rt_toggle'] !== "") {
    if (strpos($_GET['rt_toggle'], ',') !== false) {
        $RequiredData = explode(',', $_GET['rt_toggle']);

        if (isset($RequiredData[0]) && isset($RequiredData[1])) {
            $UpdateRecurringTask_Q = "UPDATE `ost_recurring_tasks` SET `is_active` = CASE WHEN `is_active` = 1 THEN 0 ELSE 1 END WHERE `rt_id` LIKE '" . $RequiredData[0]. "';";
            
            if (($UpdateRecurringTask_Res = db_query($UpdateRecurringTask_Q)) && db_affected_rows($UpdateRecurringTask_Res)) {
                $GetRecurringTaskStatus_Q = "SELECT `is_active` FROM `ost_recurring_tasks` WHERE `rt_id` LIKE '" . $RequiredData[0]. "';";
            
                if (($GetRecurringTaskStatus_Res = db_query($GetRecurringTaskStatus_Q)) && db_num_rows($GetRecurringTaskStatus_Res)) {
                    $Res = db_fetch_row($GetRecurringTaskStatus_Res);
                    
                    if (isset($Res[0])) {
                        if ($Res[0] == 1) {
                            $UpdateAssociatedEvent_Q = "ALTER EVENT `$RequiredData[1]` ENABLE;";
                        } else {
                            $UpdateAssociatedEvent_Q = "ALTER EVENT `$RequiredData[1]` DISABLE;";
                        }
                        
                        if ($UpdateAssociatedEvent_Res = db_query($UpdateAssociatedEvent_Q)) {
                            $msg = "Recurring Task " . $RequiredData[0] . " successfully updated!";
                        } else {
                            $errors['err'] = __('Recurring Task wasn\'t successfully updated!');
                        }
                    }
                }
            } else {
                $errors['err'] = __('Recurring Task wasn\'t successfully updated!');
            }
        } else {
            $errors['err'] = __('Recurring Task wasn\'t successfully updated!');
        }
    } else {
        $errors['err'] = __('Recurring Task wasn\'t successfully updated!');
    }
    
    echo "<script>window.history.replaceState({}, document.title, window.location.toString().substring(0, window.location.toString().indexOf('?')));</script>";
}

/*... Quick stats ...*/
$stats= $thisstaff->getTasksStats();

// Clear advanced search upon request
if (isset($_GET['clear_filter']))
    unset($_SESSION['advsearch:tasks']);


if (!$task) {
    $queue_key = sprintf('::Q:%s', ObjectModel::OBJECT_TYPE_TASK);
    $queue_name = strtolower($_GET['status'] ?: $_GET['a']);

    if (!$queue_name && isset($_SESSION[$queue_key])) {
        $queue_name = $_SESSION[$queue_key];
    }

    // Stash current queue view
    $_SESSION[$queue_key] = $queue_name;
    // Set queue as status
    if (@!isset($_REQUEST['advanced'])
            && @$_REQUEST['a'] != 'search'
            && !isset($_GET['status'])
            && $queue_name)
        $_GET['status'] = $_REQUEST['status'] = $queue_name;
}

//Navigation
$nav->setTabActive('tasks');
$open_name = _P('queue-name',
    /* This is the name of the open tasks queue */
    'Open');
// echo $queue_name;

$nav->addSubMenu(array('desc'=>$open_name.'('.number_format($stats['open']).')',
    'title'=>__('Open Tasks'),
    'href'=>'tasks.php?status=open',
    'iconclass'=>'Ticket'),
    ((!$_REQUEST['status'] && !isset($_SESSION['advsearch:tasks'])) || $_REQUEST['status']=='open'));

$nav->addSubMenu(array('desc'=>__('My Tasks') .'('.number_format($stats['assigned']).')',
    'title'=>__('Tasks assigned directly to me'),
    'href'=>'tasks.php?status=assigned',
    'iconclass'=>'assignedTickets'),
    ($_REQUEST['status']=='assigned'));
//task i am cc in


$nav->addSubMenu(array('desc' => __('CC').'('.number_format($thisstaff->getNumCCTasks()).')',
'title'=>__('CC Tasks'),
'href'=>'tasks.php?status=cc',
'iconclass'=>'teams'),
($_REQUEST['status']=='cc'));

// $nav->addSubMenu(array('desc'=>__('Assigned To Teams'),
//     'title'=> __('Tasks assigned to teams i am in'),
//     'href'=>'tasks.php?status=assignedToTeams',
//     'iconclass'=>'teams',
//     ),
//     ($_REQUEST['status']=='assignedToTeams'));

//  $nav->addSubMenu(array('desc'=>__('Overdue').' ('.number_format($stats['overdue']).')',
//     'title'=>__('Stale Tasks'),
//     'href'=>'tasks.php?status=overdue',
//     'iconclass'=>'overdueTickets'),
//     ($_REQUEST['status']=='overdue'));

if(!$sysnotice && $stats['overdue'] > 10)
    $sysnotice=sprintf(__('%d overdue tasks!'), $stats['overdue']);
//recently closed
$nav->addSubMenu(array('desc' => __('Recently Completed').'('.number_format($thisstaff->getNumTodayClosed()).')',
    'title'=>__('Recently Completed Tasks'),
    'href'=>'tasks.php?status=recentlyclosed',
    'iconclass'=>'closedTickets'),
    ($_REQUEST['status']=='recentlyclosed'));

    if ($thisstaff->isManager()) {
//assign from my showroom
$nav->addSubMenu(array('desc' => __('Assign To My Showroom').'('.number_format($thisstaff->getNumAFMS()).')',
    'title'=>__('Assign To My Showroom'),
    'href'=>'tasks.php?status=atms',
    'iconclass'=>'teams'),
    ($_REQUEST['status']=='atms'));


    }




    









if ($thisstaff->hasPerm(TaskModel::PERM_CREATE, false)) {
    $nav->addSubMenu(array('desc'=>__('New Task'),
        'title'=> __('Open a New Task'),
        'href'=>'#tasks/addsub',
        'iconclass'=>'newTicket new-task',
        'id' => 'new-task',
        'attr' => array(
            'data-dialog-config' => '{"size":"large"}'
            )
        ),
    ($_REQUEST['a']=='open'));
}



$nav->addSubMenu(array('desc'=>__('BookMarks'),
'title'=> __('View BookMarks Tasks'),
'href'=>'tasks.php?status=BookMarks',
'iconclass'=>'Ticket',
), ($_REQUEST['status']=='BookMarks'));

if ($thisstaff->isManager()) {
    $nav->addSubMenu(array('desc'=>__('Recurring'),
    'title'=> __('View Recurring Tasks'),
    'href'=>'tasks.php?status=recurring',
    'iconclass'=>'Ticket',
    ), ($_REQUEST['status']=='recurring'));
}

//$nav->addSubMenu(array('desc' => __('Completed').'('.number_format($stats['closed']).')',
  //  'title'=>__('Completed Tasks'),
   // 'href'=>'tasks.php?status=closed',
    //'iconclass'=>'closedTickets'),
    //($_REQUEST['status']=='closed'));

$ost->addExtraHeader('<script type="text/javascript" src="js/ticket.js?a5d898b"></script>');
$ost->addExtraHeader('<script type="text/javascript" src="js/thread.js?a5d898b"></script>');
$ost->addExtraHeader('<meta name="tip-namespace" content="tasks.queue" />', "$('#content').data('tipNamespace', 'tasks.queue');");

if($task) {
    $ost->setPageTitle(sprintf(__('Task #%s'),$task->getNumber()));
    $nav->setActiveSubMenu(-1);
    $inc = 'task-view.inc.php';
    if ($_REQUEST['a']=='edit'
            && $task->checkStaffPerm($thisstaff, TaskModel::PERM_EDIT)) {
        $inc = 'task-edit.inc.php';
        if (!$forms) $forms=DynamicFormEntry::forObject($task->getId(), 'A');
        // Auto add new fields to the entries
        foreach ($forms as $f) $f->addMissingFields();
    } elseif($_REQUEST['a'] == 'print' && !$task->pdfExport($_REQUEST['psize']))
        $errors['err'] = __('Unable to print to PDF.')
            .' '.__('Internal error occurred');
} else {
    $inc = 'tasks.inc.php';
    
    if ($_REQUEST['a']=='open' && $thisstaff->hasPerm(Task::PERM_CREATE, false))
        $inc = 'task-open.inc.php';
    elseif ($_REQUEST['a'] == 'export') {
        $ts = strftime('%Y%m%d');
        if (!($query=$_SESSION[':Q:tasks']))
            $errors['err'] = __('Query token not found');
        elseif (!Export::saveTasks($query, "tasks-$ts.csv", 'csv'))
            $errors['err'] = __('Unable to dump query results.')
                .' '.__('Internal error occurred');
    }

    //Clear active submenu on search with no status
    if($_REQUEST['a']=='search' && !$_REQUEST['status'])
        $nav->setActiveSubMenu(-1);

    //set refresh rate if the user has it configured
    if(!$_POST && !$_REQUEST['a'] && ($min=$thisstaff->getRefreshRate())) {
        $js = "clearTimeout(window.task_refresh);
               window.task_refresh = setTimeout($.refreshTaskView,"
            .($min*60000).");";
        $ost->addExtraHeader('<script type="text/javascript">'.$js.'</script>', $js);
    }
}

if(isset($_GET["SubTask"])){
    ?>
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
  width: 100%;
  margin-bottom:10px;
  opacity: 0.8;
}

/* Add some hover effects to buttons */
.form-container .btn:hover, .open-button:hover {
  opacity: 1;
}
</style>

<!-- <button class="open-button" onclick="openForm()">Open Form</button> -->

<div class="form-popup" id="myForm" style="display :block ">
  <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
  <?php csrf_token(); ?>
    <h1>Create Folder</h1>

    <label ><b>Enter The Task Number To Be Parent</b></label>
    <input style="padding: 16px 20px;border: none;width: 85%;" type="text" placeholder="Enter Task Number" name="foldername" required>



    <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
    <input type="submit" class="btn" name="str1" value="Create" />
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
  window.location = "tasks.php";
}
</script>
    <?php
    if (isset($_POST["str1"])) {
        if (isset($_POST['foldername'])) {
            $sql_createnewfolder=' UPDATE `ost_task` SET `parent_task_id`= (SELECT `id`  FROM `ost_task` WHERE `number` = '.$_POST['foldername'].') ,`child_task_id`= '.$_GET["SubTask"].'  WHERE `id` = ' . $_GET["SubTask"];
            // echo $sql_createnewfolder; 
            if(db_query($sql_createnewfolder)){
                echo'
                <script>document.getElementById("myForm").style.display = "none";
                window.location = "tasks.php";</script>
                ';
            }}
        
}
}
require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');
