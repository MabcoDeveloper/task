<?php
$tasks = Task::objects();
$date_header = $date_col = false;

// Make sure the cdata materialized view is available
TaskForm::ensureDynamicDataView();

   
if(isset($_SESSION['showroomId'])){
    foreach($_SESSION['showroomId'] as $productId){

        $sqlCol="INSERT INTO `ost_thread_collaborator`( `flags`, `thread_id`, `user_id`, `role`, `created`) VALUES (3,".$_SESSION['thread_id'].",".$productId.",'M',Now())";
        // echo $sqlCol;
        db_query($sqlCol);
        unset($_SESSION['showroomId']);
        //notification 
        $sql="SELECT `ost_task__cdata`.`title` ,`ost_task`.`id`  FROM `ost_task__cdata` 
        INNER JOIN `ost_task` ON `ost_task`.`id`=`ost_task__cdata`.`task_id`
        INNER JOIN `ost_thread` ON `ost_task`.`id`=`ost_thread`.`object_id`
        WHERE `ost_thread`.`object_type`='A' AND `ost_thread`.`id`=".$_SESSION['thread_id'];
         if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
            while (list($Name,$ID) = db_fetch_row($sql_Res)) {
                Ticket::SendPushNotification($ID,$Name, $productId , 0);
            }
        }
        
    }
    
    }

    if(isset($_SESSION['showroomUserId'])){
        foreach($_SESSION['showroomUserId'] as $productId){
    
            $sqlCol="INSERT INTO `ost_thread_collaborator`( `flags`, `thread_id`, `team_id`, `role`, `created`) VALUES (3,".$_SESSION['thread_id'].",".$productId.",'M',Now())";
            // echo $sqlCol;
            db_query($sqlCol);
            unset($_SESSION['showroomUserId']);
        }
        
        }


        if(isset($_SESSION['teamcc'])){
            
                $sqlCol="UPDATE `ost_task` SET `collab_team`=".$_SESSION['teamcc']."  WHERE `id`= (SELECT `object_id` FROM `ost_thread` WHERE `id`=".$_SESSION['thread_id']." AND `object_type`='A')";
                // echo $sqlCol;
                db_query($sqlCol);
                unset($_SESSION['teamcc']);
            
            
            }
            if(isset($_SESSION['closebyme'])){
            
                $sqlCol="UPDATE `ost_task` SET `CBC`=1  WHERE `id`= (SELECT `object_id` FROM `ost_thread` WHERE `id`=".$_SESSION['thread_id']." AND `object_type`='A')";
                // echo $sqlCol;
                db_query($sqlCol);
                unset($_SESSION['closebyme']);
            
            
            }
// Figure out REFRESH url — which might not be accurate after posting a
// response
list($path,) = explode('?', $_SERVER['REQUEST_URI'], 2);
$args = array();
parse_str($_SERVER['QUERY_STRING'], $args);

// Remove commands from query
unset($args['id']);
unset($args['a']);

$refresh_url = $path . '?' . http_build_query($args);

$sort_options = array(
    'updated' =>            __('Most Recently Updated'),
    'created' =>            __('Most Recently Created'),
    'due' =>                __('Due Soon'),
    'number' =>             __('Task Number'),
    'closed' =>             __('Most Recently Closed'),
    'hot' =>                __('Longest Thread'),
    'relevance' =>          __('Relevance'),
    'From' =>               __('From Agent'),
    'To' =>                 __('To Agent'),

);
// Queues columns

$queue_columns = array(
    'number' => array(
        'width' => '8%',
        'heading' => __('Number'),
    ),
    'date' => array(
        'width' => '20%',
        'heading' => __('Last Updated'),
        'sort_col' => 'updated',
    ),
    'title' => array(
        'width' => '38%',
        'heading' => __('Title'),
        'sort_col' => 'cdata__title',
    ),
    'dept' => array(
        'width' => '16%',
        'heading' => __('Department'),
        'sort_col'  => 'dept__name',
    ),
    'assignor_id' => array(
        'width' => '16%',
        'heading' => __('From'),
        'sort_col'  => 'assignor_id',
    ),
    'assignee' => array(
        'width' => '16%',
        'heading' => __('To'),
        'sort_col'  => 'assignee',
    ),
    'Last Response' => array(
        'width' => '16%',
        'heading' => __('Last Response'),
        'sort_col'  => 'updated',
    ),
);

// Queue we're viewing
$queue_key = sprintf('::Q:%s', ObjectModel::OBJECT_TYPE_TASK);
$queue_name = $_SESSION[$queue_key] ?: 'assigned';

// if (($teams = $thisstaff->getTeams()) && count(array_filter($teams))) {
//     $teams = $thisstaff->getTeams();
//     $tasks->filter(array('team_id__in' => array_filter($teams), 'flags' => 1)); // Tasks assigned to teams and open
//     echo "<script>$('#subnav4').html('Assigned To Teams (" . count($tasks) . ")');</script>";
//     $tasks = Task::objects();
// } else {
//     echo "<script>$('#subnav4').html('Assigned To Teams (0)');</script>";
// }
// if (($teams = $thisstaff->getTeams()) ) {
//     $teams = $thisstaff->getTeams();
//     $tasks->filter(Q::any(array('staff_id' => $thisstaff->getId(),
//         'team_id__in' => array_filter($teams),
//         'assignor_id' => $thisstaff->getId()))); // Tasks assigned to teams and open
//     echo "<script>$('#subnav2').html('My Tasks (" . count($tasks). ")');</script>";
//     // $tasks = Task::objects();
//     // echo count($tasks);
// } 

switch ($queue_name) {
    case 'closed':
        $status = 'closed';
        $results_type = __('Completed Tasks');
        $showassigned = true; //closed by.
        // $tasks->filter(Q::any(array('closed' => "BETWEEN NOW() - INTERVAL 34 DAY AND NOW()")));
        
        $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot', 'From', 'To');
    break;
    case 'overdue':
        $status = 'open';
        $results_type = __('Overdue Tasks');
        $tasks->filter(array('flags' => 3));
        $queue_sort_options = array('updated', 'created', 'number', 'hot');
        break;
    case 'assigned':
        $status = 'open';
        $staffId = $thisstaff->getId();
        $results_type = __('My Tasks');
        $teams = $thisstaff->getTeams();
        $tasks->filter(Q::any(array('staff_id' => $thisstaff->getId(),
        'team_id__in' => array_filter($teams))));
        $queue_sort_options = array('updated', 'created', 'hot', 'number');
        $tasks->order_by('cdata__title', 'ASC');
        break;

    case 'afm':
        $status = 'open';
        $staffId = $thisstaff->getId();
        $results_type = __('Assigned From Me');
        $teams = $thisstaff->getTeams();
        $tasks->filter(Q::any(array('assignor_id' => $thisstaff->getId())));
        $queue_sort_options = array('updated', 'created', 'hot', 'number');
        $tasks->order_by('duedate', 'ASC');
        break;

        
    // case 'assignedtoteams':
    //     $status = 'open';
    //     $staffId = $thisstaff->getId();
    //     $results_type = __('Assigned To Teams');

    //     if (($teams = $thisstaff->getTeams()) && count(array_filter($teams))) {
    //         $tasks->filter(array('team_id__in' => array_filter($teams)));
    //     }

    //     $queue_sort_options = array('updated', 'created', 'hot', 'number');
    //     break;
    case 'recurring':
        $status = 'recurring';
        $staffId = $thisstaff->getId();
        $results_type = __('Recurring Tasks');
        $queue_sort_options = array('updated', 'created', 'hot', 'number');
        break;

    case 'cc':

        $status = 'open';
        $staffId = $thisstaff->getId();
        $results_type = __('CC Tasks');
        $tasks->filter(Q::any(array(
            'collab_1' => $staffId,
            'collab_2' => $staffId,
            'collab_3' => $staffId,
            'collab_team__in' => array_filter($thisstaff->getTeams())
        )));
        $queue_sort_options = array('updated', 'created', 'hot', 'number');
        break;
    case 'bookmarks':
        $status = 'bookmarks';
        $staffId = $thisstaff->getId();
        $results_type = __('BookMarks Tasks');
        $queue_sort_options = array('updated', 'created', 'hot', 'number');
        break;

    case 'atma':
            $status = 'atma';
            $staffId = $thisstaff->getId();
            $results_type = __('Assigned To My Extended');
            $queue_sort_options = array('updated', 'created', 'hot', 'number');
            break;
    case 'todo':
            $status = 'todo';
            $staffId = $thisstaff->getId();
            $results_type = __('To Do Tasks');
            $queue_sort_options = array('updated', 'created', 'hot', 'number');
            break;
    case 'recentlyclosed':
            $status = 'recentlyclosed';
            $staffId = $thisstaff->getId();
            $results_type = __('Today');
            $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot', 'From', 'To');
            break;
    case 'closedyesterday':
            $status = 'closedyesterday';
            $staffId = $thisstaff->getId();
            $results_type = __('Yesterday');
            $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot', 'From', 'To');
            break;
    case 'closedweek':
            $status = 'closedweek';
            $staffId = $thisstaff->getId();
            $results_type = __('This Week');
            $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot', 'From', 'To');
            break;
    case 'closedmonth':
            $status = 'closedmonth';
            $staffId = $thisstaff->getId();
            $results_type = __('This Month');
            $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot', 'From', 'To');
            break;
    case 'atms':
            $status = 'atms';
            $staffId = $thisstaff->getId();
            $results_type = __('Assign To My Showroom');
            $queue_sort_options = array('updated', 'created', 'hot', 'number');
            break;
    default:
    case 'search':
        $queue_sort_options = array('closed', 'updated', 'created', 'number', 'hot');
        // Consider basic search
        $count=0;
        if ($_REQUEST['query']) {
            $results_type = __('Search Results');
            $z = array();
            $Q=Q::any(array('number__startswith' => $_REQUEST['query'],
            'cdata__title__contains' => $_REQUEST['query'],
            'thread__entries__body__contains' => $_REQUEST['query']));
            //for searching
            $sqlgetAttachmentTasks = "select max(t.number) from ost_task t inner join ost_thread th on th.object_id=t.id inner join ost_thread_entry te on te.thread_id=th.id inner join ost_attachment a on a.object_id=te.id inner join ost_file f on f.id=a.file_id where f.name like '%" . $_REQUEST['query'] . "%' group by t.number order by t.updated desc";
            if (($sqlgetAttachmentTasks_Res = db_query($sqlgetAttachmentTasks)) && db_num_rows($sqlgetAttachmentTasks_Res)) {
                while (list($ID) = db_fetch_row($sqlgetAttachmentTasks_Res)) {
                    array_push($z, $ID);
                    $count++;
                }
            }
            if ($count >0)
            {
                $Q->add(array('number__in' => $z));
            }
            $tasks = $tasks->filter($Q);
            unset($_SESSION[$queue_key]);
            break;
        }
        // Fall-through and show open tickets
    case 'open':
        $status = 'open';
        $results_type = __('Open Tasks');
        // $tasks->filter(Q::any(array(
        //     'staff_id' => $tasks['staff_id']
            
        //                             )));
        if($thisstaff->isManager()){
            // $tasks->order_by('staff_id' , 'ASC' );
            $tasks->order_by('team__name', 'ASC');
            $tasks->order_by('staff__firstname', 'ASC');
        }
       
        $queue_sort_options = array('updated','created', 'due', 'number', 'hot','assignee');
        break;
    }

// Apply filters
$filters = array();
if ($status) {
    $SQ = new Q(array('flags__hasbit' => TaskModel::ISOPEN));
    if (!strcasecmp($status, 'closed'))
        $SQ->negate();

    $filters[] = $SQ;
}

if ($filters)
    $tasks->filter($filters);

// Impose visibility constraints
// ------------------------------------------------------------
// -- Open and assigned to me
$visibility = Q::any(
    new Q(array('flags__hasbit' => TaskModel::ISOPEN, 'staff_id' => $thisstaff->getId()))
);
// -- Task for tickets assigned to me
$visibility->add(new Q(array(
    'ticket__staff_id' => $thisstaff->getId(),
    'ticket__status__state' => 'open'
)));

$agents = Staff::objects()->select_related('dept');

$filters = array();

$filters += array('dept_id' => $thisstaff->getDeptId());

echo "<script>var DeptId = " . $thisstaff->getDeptId() . "</script>";

if ($filters)
    $agents->filter($filters);

$AllAgentsIds = array();
$AllTeamMember = array();
$depts = Dept::getDepartments();

foreach ($depts as $dkey => $value) {
    $AgentsInDept = Dept::getMembersByDeptId($dkey);

    foreach ($AgentsInDept as $key => $value) {
        if ($AllAgentsIds[$dkey] == "")
            $AllAgentsIds[$dkey] .= "s" . $value->getId();
        else
            $AllAgentsIds[$dkey] .= "_s" . $value->getId();
    }
}
foreach (Team::getActiveTeams() as $id => $name){
    $z= array();
    $sql="SELECT CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) FROM `ost_team` INNER JOIN `ost_team_member` ON `ost_team_member`.`team_id`=`ost_team`.`team_id` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_team_member`.`staff_id` WHERE `ost_team`.`team_id`=".$id;
    if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
        while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
            array_push($z, $RecurringTaskID);
            if ($AllTeamMember[$id] == "")
            $AllTeamMember[$id] .=" \n  ". $RecurringTaskID;
            else
            $AllTeamMember[$id] .=" \n  ". $RecurringTaskID;
            
        }
    }

}
$AgentsIds = array();

foreach ($agents as $agent) {
    array_push($AgentsIds, $agent->getId());
}

echo "<script>var AllAgentsIds = '" . json_encode($AllAgentsIds) . "'</script>";
echo "<script>var AllTeamMember = " . json_encode($AllTeamMember) . "</script>";
$visibility->add(new Q(array('assignor_id' => $thisstaff->getId())));
$visibility->add(new Q(array('assignor_id__in' => $AgentsIds)));
$visibility->add(new Q(array('collab_1' => $thisstaff->getId())));
$visibility->add(new Q(array('collab_2' => $thisstaff->getId())));
$visibility->add(new Q(array('collab_3' => $thisstaff->getId())));
if (($teams = $thisstaff->getTeams()) && count(array_filter($teams)))
    $visibility->add(new Q(array(
        'collab_team__in' => array_filter($teams),
        'flags__hasbit' => TaskModel::ISOPEN
    )));




// -- Routed to a department of mine
if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts()))
    $visibility->add(new Q(array('dept_id__in' => $depts)));
// -- Open and assigned to a team of mine
if (($teams = $thisstaff->getTeams()) && count(array_filter($teams)))
    $visibility->add(new Q(array(
        'team_id__in' => array_filter($teams),
        'flags__hasbit' => TaskModel::ISOPEN
    )));
$tasks->filter(new Q($visibility));

// Add in annotations
$tasks->annotate(array(
    'collab_count' => SqlAggregate::COUNT('thread__collaborators', true),
    'attachment_count' => SqlAggregate::COUNT(
        SqlCase::N()
            ->when(new SqlField('thread__entries__attachments__inline'), null)
            ->otherwise(new SqlField('thread__entries__attachments')),
        true
    ),
    'thread_count' => SqlAggregate::COUNT(
        SqlCase::N()
            ->when(
                new Q(array('thread__entries__flags__hasbit' => ThreadEntry::FLAG_HIDDEN)),
                null
            )
            ->otherwise(new SqlField('thread__entries__id')),
        true
    ),
));

$tasks->values(
    'id',
    'number',
    'created',
    'staff_id',
    'team_id',
    'staff__firstname',
    'staff__lastname',
    'team__name',
    'dept__name',
    'cdata__title',
    'flags',
    'assignor_id',
    'duedate'
);
// Apply requested quick filter

$queue_sort_key = sprintf(':Q%s:%s:sort', ObjectModel::OBJECT_TYPE_TASK, $queue_name);

if (isset($_GET['sort'])) {
    $_SESSION[$queue_sort_key] = array($_GET['sort'], $_GET['dir']);
}
elseif (!isset($_SESSION[$queue_sort_key])) {
    $_SESSION[$queue_sort_key] = array($queue_sort_options[0], 0);
}

list($sort_cols, $sort_dir) = $_SESSION[$queue_sort_key];
$orm_dir = $sort_dir ? QuerySet::ASC : QuerySet::DESC;
$orm_dir_r = $sort_dir ? QuerySet::DESC : QuerySet::ASC;

switch ($sort_cols) {
case 'number':
    $queue_columns['number']['sort_dir'] = $sort_dir;
    $tasks->extra(array(
            'order_by' => array(
                array(SqlExpression::times(new SqlField('number'), 1), $orm_dir)
            )
        ));
        break;
    case 'due':
        $queue_columns['date']['heading'] = __('Due Date');
        $queue_columns['date']['sort'] = 'due';
        $queue_columns['date']['sort_col'] = $date_col = 'duedate';
        $tasks->filter(array('flags' => 1));
        $tasks->values('duedate');
        $tasks->order_by(SqlFunction::COALESCE(new SqlField('duedate'), 'zzz'), $orm_dir_r);
        break;
    case 'closed':
        $queue_columns['date']['heading'] = __('Date Closed');
        $queue_columns['date']['sort'] = $sort_cols;
        $queue_columns['date']['sort_col'] = $date_col = 'closed';
        $queue_columns['date']['sort_dir'] = $sort_dir;
        $tasks->values('closed');
        $tasks->order_by($sort_dir ? 'closed' : '-closed');
        break;
    case 'updated':
        $queue_columns['date']['heading'] = __('Last Updated');
        $queue_columns['date']['sort'] = $sort_cols;
        $queue_columns['date']['sort_col'] = $date_col = 'updated';
        $tasks->values('updated');
        $tasks->order_by($sort_dir ? 'updated' : '-updated');
        break;
    case 'hot':
        $tasks->order_by('-thread_count');
        $tasks->annotate(array(
            'thread_count' => SqlAggregate::COUNT('thread__entries'),
        ));
        break;
    case 'From':
        $z = array();
        $x = array();
        $types = array();
        $GetAllStaff = "SELECT `staff_id`, CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
            }}
         ?>
            <style>
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
                    padding-top: 100px;
                    /* Location of the box */
                    left: 0;
                    top: 0;
                    width: 100%;
                    /* Full width */
                    height: 100%;
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
            </style>
            </head>

            <body>





                <!-- The Modal -->
                <div id="myModal" class="modal">

                    <!-- Modal content -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- <span class="close">&times;</span> -->
                            <h2 class="h2class">Filter Task From Specific Agent</h2>
                        </div>
                        <div class="modal-body">
                            <p><?php echo "Choose An Agent"; ?></p>
                            <form action="" method="post">
                            <?php csrf_token(); ?>
                            <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
                                <?php foreach ($z as $index => $item) {


                                ?>
                                    <option value="<?php echo $x[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                            </select>
                            <input type="submit">
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


                    // // When the user clicks on <span> (x), close the modal
                    // span.onclick = function() {
                    //     modal.style.display = "none";
                    // }
                    // // When the user clicks anywhere outside of the modal, close it
                    // window.onclick = function(event) {
                    //     if (event.target == modal) {
                    //         modal.style.display = "none";
                    //     }
                    // }
                </script>

                
        <?php
        if (!empty($_POST['ddlViewBy'])) {
            echo '<script>modal.style.display = "none";</script>';
        }
$variable=$_POST['ddlViewBy'];
// echo $variable;
$status = 'open';
$tasks->filter(Q::any(array(
'assignor_id' => $variable

                        )));
$tasks->values('updated');
$tasks->order_by($sort_dir ? 'updated' : '-updated');                        
                        $queue_sort_options = array('updated', 'created', 'hot', 'number', 'From', 'To');
                        
$_SESSION[$queue_sort_key] = array($queue_sort_options[0], 0);
        break;

    case 'To':
        $z = array();
        $x = array();
        $types = array();
        $GetAllStaff = "SELECT `staff_id`, CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
            }}
         ?>
            <style>
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
                    padding-top: 100px;
                    /* Location of the box */
                    left: 0;
                    top: 0;
                    width: 100%;
                    /* Full width */
                    height: 100%;
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
            </style>
            </head>

            <body>





                <!-- The Modal -->
                <div id="myModal" class="modal">

                    <!-- Modal content -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- <span class="close">&times;</span> -->
                            <h2 class="h2class">Filter Task From Specific Agent</h2>
                        </div>
                        <div class="modal-body">
                            <p><?php echo "Choose An Agent"; ?></p>
                            <form action="" method="post">
                            <?php csrf_token(); ?>
                            <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
                                <?php foreach ($z as $index => $item) {


                                ?>
                                    <option value="<?php echo $x[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                            </select>
                            <input type="submit">
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
                    // // When the user clicks anywhere outside of the modal, close it
                    // window.onclick = function(event) {
                    //     if (event.target == modal) {
                    //         modal.style.display = "none";
                    //     }
                    // }
                </script>

                
        <?php
        if (!empty($_POST['ddlViewBy'])) {
            echo '<script>modal.style.display = "none";</script>';
        }
$variable=$_POST['ddlViewBy'];
// echo $variable;
$status = 'open';
$tasks->filter(Q::any(array(
'staff_id' => $variable

                        )));
$tasks->values('updated');
$tasks->order_by($sort_dir ? 'updated' : '-updated');    
                        $queue_sort_options = array('updated', 'created', 'hot', 'number', 'From', 'To');
                        
$_SESSION[$queue_sort_key] = array($queue_sort_options[0], 0);
        break;
    case 'assignee':
        // $tasks->order_by('staff__lastname', $orm_dir);
        $tasks->order_by('team__name', $orm_dir);
        $tasks->order_by('staff__firstname', $orm_dir);
        $queue_columns['assignee']['sort_dir'] = $sort_dir;
        break;
    default:
        if ($sort_cols && isset($queue_columns[$sort_cols])) {
            $queue_columns[$sort_cols]['sort_dir'] = $sort_dir;
            if (isset($queue_columns[$sort_cols]['sort_col']))
                $sort_cols = $queue_columns[$sort_cols]['sort_col'];
            $tasks->order_by($sort_cols, $orm_dir);
            break;
        }
    case 'created':
        $queue_columns['date']['heading'] = __('Date Created');
        $queue_columns['date']['sort'] = 'created';
        $queue_columns['date']['sort_col'] = $date_col = 'created';
        $tasks->order_by($sort_dir ? 'created' : '-created');
        break;
}



if (in_array($sort_cols, array('created', 'due', 'updated')))
    $queue_columns['date']['sort_dir'] = $sort_dir;


// Apply requested pagination
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;

if ($queue_name == 'recurring') {
    $count = 0;
} else {
    $count = $tasks->count();
}
//yaseen
if ($queue_name == 'serach'||$queue_name == 'search') {
    $count = 500;$pageNav = new Pagenate($count, $page, 500);
} else {
    $count = $tasks->count();
    $pageNav = new Pagenate($count, $page, PAGE_LIMIT);
}
//
$pageNav->setURL('tasks.php', $args);
$tasks = $pageNav->paginate($tasks);

TaskForm::ensureDynamicDataView();

// Save the query to the session for exporting
$_SESSION[':Q:tasks'] = $tasks;

// Mass actions
$actions = array();

if ($thisstaff->hasPerm(Task::PERM_ASSIGN, false)) {
    $actions += array(
        'assign' => array(
            'icon' => 'icon-user',
            'action' => __('Assign Tasks')
        )
    );
}

if ($thisstaff->hasPerm(Task::PERM_TRANSFER, false)) {
    $actions += array(
        'transfer' => array(
            'icon' => 'icon-share',
            'action' => __('Transfer Tasks')
        )
    );
}

if ($thisstaff->hasPerm(Task::PERM_DELETE, false)) {
    $actions += array(
        'delete' => array(
            'icon' => 'icon-trash',
            'action' => __('Delete Tasks')
        )
    );
}

        ?>
        <!-- SEARCH FORM START -->
        <div id='basic_search'>
            <div class="pull-right" style="height:25px">
                <span class="valign-helper"></span>
                <?php
                require STAFFINC_DIR . 'templates/tasks-queue-sort.tmpl.php';
                ?>
            </div>
            <form action="tasks.php" method="get" onsubmit="javascript:
        $.pjax({
        url:$(this).attr('action') + '?' + $(this).serialize(),
        container:'#pjax-container',
        timeout: 2000
        });
        return false;">
                <input type="hidden" name="a" value="search">
                <input type="hidden" name="search-type" value="" />
                <div class="attached input">
                    <input type="text" class="basic-search" data-url="ajax.php/tasks/lookup" name="query" autofocus size="30" value="<?php echo Format::htmlchars($_REQUEST['query'], true); ?>" autocomplete="off" autocorrect="off" autocapitalize="off">
                    <button type="submit" class="attached button"><i class="icon-search"></i>
                    </button>
                </div>
            </form>

        </div>
        <!-- SEARCH FORM END -->
        <?php   $orderWays = array('DESC' => '-', 'ASC' => '');
if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $negorder = $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $negorder = $order = 'ASC';
}?>
        <div class="clear"></div>
        <div style="margin-bottom:20px; padding-top:5px;">
            <div class="sticky bar opaque">
                <div class="content">
                    <div style="display:flex; flex-direction: row; justify-content: center; align-items: center"  class="pull-left flush-left">
                        <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  $results_type . $showing; ?></a></h2>
                    
                <?php if($thisstaff->getIfextended() > 0  && $results_type . $showing == "Open Tasks"){
    
?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=atma" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Assigned To My Extended"; ?></a></h2>
<?php }?>
<?php if($thisstaff->isManager() && $results_type . $showing == "Open Tasks"){
    
    ?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=todo" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "To Do"; ?></a></h2>
    <?php }?>

    <?php if($thisstaff->isManager() && $results_type . $showing == "To Do Tasks"){
    
    ?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=open" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Open Tasks"; ?></a></h2>
    <?php }?>

    <?php if($thisstaff->isManager() && $results_type . $showing == "To Do Tasks"){
    
    ?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=atma" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Assigned To My Extended"; ?></a></h2>
    <?php }?>

    <?php if($thisstaff->isManager() && $results_type . $showing == "Assigned To My Extended"){
    
    ?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=open" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Open Tasks"; ?></a></h2>
    <?php }?>

    <?php if($thisstaff->isManager() && $results_type . $showing == "Assigned To My Extended"){
    
    ?>                        <h2 style="margin-left: 200px;"><a href="tasks.php?status=todo" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "To Do"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "Today"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedyesterday" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Yesterday"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "Today"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedweek" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Week"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "Today"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedmonth" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Month"; ?></a></h2>
    <?php }?>



    <!-- yesterday -->
    <?php if($results_type . $showing == "Yesterday"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=recentlyclosed" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Today"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "Yesterday"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedweek" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Week"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "Yesterday"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedmonth" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Month"; ?></a></h2>
    <?php }?>
    <!-- yesterday -->

      <!-- Week -->
      <?php if($results_type . $showing == "This Week"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=recentlyclosed" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Today"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "This Week"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedyesterday" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Yesterday"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "This Week"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedmonth" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Month"; ?></a></h2>
    <?php }?>
    <!-- Week -->

    <!-- Month -->
    <?php if($results_type . $showing == "This Month"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=recentlyclosed" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Today"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "This Month"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedyesterday" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "Yesterday"; ?></a></h2>
    <?php }?>
    <?php if($results_type . $showing == "This Month"){
    
    ?>                        <h2 style="margin-left: 100px;"><a href="tasks.php?status=closedweek" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo  "This Week"; ?></a></h2>
    <?php }?>
    <!-- Month -->

    <!-- From -->
    <!-- yaseen commented this  -->
    <?php //if ($results_type . $showing == "My Tasks") {

?> <!--<h2 style="margin-left: 200px;"><a href="tasks.php?status=afm" title="<?php //echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php //echo  "Assigned From Me"; ?></a></h2>
<?php //} ?>

<?php //if ($results_type . $showing == "Assigned From Me") {

?> <h2 style="margin-left: 200px;"><a href="tasks.php?status=assigned" title="<?php //echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php // echo  "My Tasks"; ?></a></h2>-->    
    <?php //}
    $y=array();
    $x=array();
    $sql="SELECT `team_id` FROM `ost_team_member` WHERE `staff_id`=".$thisstaff->getId().";";
    if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
        while (list($ID) = db_fetch_row($sql_Res)) {
            
            
            array_push($y, $ID);
        }
    }

    $sqlgettitle="SELECT `title` FROM `ost_task__cdata` WHERE `task_id` = 0";
    if (($sqlgettitle_Res = db_query($sqlgettitle)) && db_num_rows($sqlgettitle_Res)) {
        while (list($ID) = db_fetch_row($sqlgettitle_Res)) {
            
            
            array_push($x, $ID);
        }
    }
    // print_r($y);
    foreach ($tasks as $T) {
        if($results_type . $showing == "My Tasks" &&  strpos($T['cdata__title'], $x[0]) !== false && in_array($T['team_id'],$y)){
             ?>
             <audio  controls autoplay loop > 
             <source src='cheer.mp3' type='audio/mp3'>
             Your browser does not support the audio tag.
             </audio>
         <?php
         break;
         }}
    ?>
    <!-- from -->

    
                </div>
                    <div class="pull-right flush-right">
                        <?php
                        echo Task::getAgentActions($thisstaff, array('status' => $status));
                        ?>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <form action="tasks.php" method="POST" name='tasks' id="tasks">
                <?php csrf_token(); ?>
                <input type="hidden" name="a" value="mass_process">
                <input type="hidden" name="do" id="action" value="">
                <input type="hidden" name="status" value="<?php echo
                                                                Format::htmlchars($_REQUEST['status'], true); ?>">
                <table style="margin-top:2em" class="list" border="0" cellspacing="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <?php if ($queue_name == 'recurring') {
                                echo "<th width='3%'>Delete</th>";
                                echo "<th width='5%'>Number</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='20%'>Body</th>";
                                echo "<th width='10%'>Period Type</th>";
                                echo "<th width='15%'>Start / Recurring Date</th>";
                                echo "<th width='2%'>Duration</th>";
                                echo "<th width='20%'>Agent / Team</th>";
                                echo "<th width='5%'>Status</th>";
                            } else if ($queue_name == 'bookmarks') {
                                echo "<th width='5%'>Number</th>";
                                echo "<th width='20%'>Last Update</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='15%'>Department</th>";
                                echo "<th width='2%'>From</th>";
                                echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'atma') {
                                echo "<th width='5%'>Number</th>";
                                echo "<th width='20%'>Last Update</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='15%'>Department</th>";
                                echo "<th width='2%'>From</th>";
                                echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'todo') {
                                echo "<th width='5%'>Number</th>";
                                echo "<th width='20%'>Last Update</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='15%'>Department</th>";
                                echo "<th width='2%'>From</th>";
                                echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'recentlyclosed') {
                                $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
                                if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                                    $order = $orderWays[strtoupper($_REQUEST['order'])];
                                } else {
                                    $order = 'ASC';
                                }
                                // echo "<th width='5%'>Number</th>";
                                ?><th width="20">
                                <a href="tasks.php?status=recentlyclosed&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task ID"><?php echo __('Number'); ?>&nbsp;</a>
                                </th>
                                <th width='20%'>
                                <a href="tasks.php?status=recentlyclosed&sort=LastUpdate&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Last Update"><?php echo __('Last Update'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=recentlyclosed&sort=Title&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Title"><?php echo __('Title'); ?>&nbsp;</a>
                                </th>
                                <th width='15%'>
                                <a href="tasks.php?status=recentlyclosed&sort=Department&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Department"><?php echo __('Department'); ?>&nbsp;</a>
                                </th>

                                <th width='2%'>
                                <a href="tasks.php?status=recentlyclosed&sort=FromName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task From"><?php echo __('From'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=recentlyclosed&sort=ToName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task To"><?php echo __('To'); ?>&nbsp;</a>
                                </th>


                                <?php
                                // echo "<th width='20%'>Last Update</th>";
                                // echo "<th width='20%'>Title</th>";
                                // echo "<th width='15%'>Department</th>";
                                // echo "<th width='2%'>From</th>";
                                // echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'closedyesterday') {
                                $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
                                if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                                    $order = $orderWays[strtoupper($_REQUEST['order'])];
                                } else {
                                    $order = 'ASC';
                                }
                                // echo "<th width='5%'>Number</th>";
                                ?><th width="20">
                                <a href="tasks.php?status=closedyesterday&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task ID"><?php echo __('Number'); ?>&nbsp;</a>
                                </th>
                                <th width='20%'>
                                <a href="tasks.php?status=closedyesterday&sort=LastUpdate&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Last Update"><?php echo __('Last Update'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedyesterday&sort=Title&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Title"><?php echo __('Title'); ?>&nbsp;</a>
                                </th>
                                <th width='15%'>
                                <a href="tasks.php?status=closedyesterday&sort=Department&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Department"><?php echo __('Department'); ?>&nbsp;</a>
                                </th>

                                <th width='2%'>
                                <a href="tasks.php?status=closedyesterday&sort=FromName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task From"><?php echo __('From'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedyesterday&sort=ToName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task To"><?php echo __('To'); ?>&nbsp;</a>
                                </th>

                                
                                <?php
                                // echo "<th width='5%'>Number</th>";
                                // echo "<th width='20%'>Last Update</th>";
                                // echo "<th width='20%'>Title</th>";
                                // echo "<th width='15%'>Department</th>";
                                // echo "<th width='2%'>From</th>";
                                // echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'closedweek') {
                                $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
                                if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                                    $order = $orderWays[strtoupper($_REQUEST['order'])];
                                } else {
                                    $order = 'ASC';
                                }
                                // echo "<th width='5%'>Number</th>";
                                ?><th width="20">
                                <a href="tasks.php?status=closedweek&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task ID"><?php echo __('Number'); ?>&nbsp;</a>
                                </th>
                                <th width='20%'>
                                <a href="tasks.php?status=closedweek&sort=LastUpdate&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Last Update"><?php echo __('Last Update'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedweek&sort=Title&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Title"><?php echo __('Title'); ?>&nbsp;</a>
                                </th>
                                <th width='15%'>
                                <a href="tasks.php?status=closedweek&sort=Department&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Department"><?php echo __('Department'); ?>&nbsp;</a>
                                </th>

                                <th width='2%'>
                                <a href="tasks.php?status=closedweek&sort=FromName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task From"><?php echo __('From'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedweek&sort=ToName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task To"><?php echo __('To'); ?>&nbsp;</a>
                                </th>

                                
                                <?php
                                // echo "<th width='5%'>Number</th>";
                                // echo "<th width='20%'>Last Update</th>";
                                // echo "<th width='20%'>Title</th>";
                                // echo "<th width='15%'>Department</th>";
                                // echo "<th width='2%'>From</th>";
                                // echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'closedmonth') {
                                $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
                                if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                                    $order = $orderWays[strtoupper($_REQUEST['order'])];
                                } else {
                                    $order = 'ASC';
                                }
                                // echo "<th width='5%'>Number</th>";
                                ?><th width="20">
                                <a href="tasks.php?status=closedmonth&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task ID"><?php echo __('Number'); ?>&nbsp;</a>
                                </th>
                                <th width='20%'>
                                <a href="tasks.php?status=closedmonth&sort=LastUpdate&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Last Update"><?php echo __('Last Update'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedmonth&sort=Title&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Title"><?php echo __('Title'); ?>&nbsp;</a>
                                </th>
                                <th width='15%'>
                                <a href="tasks.php?status=closedmonth&sort=Department&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Department"><?php echo __('Department'); ?>&nbsp;</a>
                                </th>

                                <th width='2%'>
                                <a href="tasks.php?status=closedmonth&sort=FromName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task From"><?php echo __('From'); ?>&nbsp;</a>
                                </th>

                                <th width='20%'>
                                <a href="tasks.php?status=closedmonth&sort=ToName&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task To"><?php echo __('To'); ?>&nbsp;</a>
                                </th>

                                
                                <?php
                                // echo "<th width='5%'>Number</th>";
                                // echo "<th width='20%'>Last Update</th>";
                                // echo "<th width='20%'>Title</th>";
                                // echo "<th width='15%'>Department</th>";
                                // echo "<th width='2%'>From</th>";
                                // echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else if ($queue_name == 'atms') {
                                echo "<th width='5%'>Number</th>";
                                echo "<th width='20%'>Last Update</th>";
                                echo "<th width='20%'>Title</th>";
                                echo "<th width='15%'>Department</th>";
                                echo "<th width='2%'>From</th>";
                                echo "<th width='20%'>To</th>";
                                echo "<th width='20%'>Last Response</th>";
                            }
                            else { ?>
                                <?php if ($thisstaff->canManageTickets()) { ?>
                                    <th width="4%">&nbsp;</th>
                                <?php } ?>

                                <?php
                                // Query string
                                unset($args['sort'], $args['dir'], $args['_pjax']);
                                $qstr = Http::build_query($args);
                                // Show headers
                                foreach ($queue_columns as $k => $column) {
                                    echo sprintf(
                                        '<th width="%s"><a href="?sort=%s&dir=%s&%s" class="%s">%s</a></th>',
                                        $column['width'],
                                        $column['sort'] ?: $k,
                                        $column['sort_dir'] ? 0 : 1,
                                        $qstr,
                                        isset($column['sort_dir'])
                                            ? ($column['sort_dir'] ? 'asc' : 'desc') : '',
                                        $column['heading']
                                    );
                                }
                                ?>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Setup Subject field for display
                        $total = $RecurringTotal = 0;
                        $title_field = TaskForm::getInstance()->getField('title');
                        $ids = ($errors && $_POST['tids'] && is_array($_POST['tids'])) ? $_POST['tids'] : null;

                        $GetRecurringTasksQ = "SELECT `rt_id` FROM `ost_recurring_tasks` WHERE `rt_assignor_id` = " . $thisstaff->getId();

                        if ($GetRecurringTasks_Res = db_query($GetRecurringTasksQ)) {
                            $RecurringTasksCount = db_affected_rows($GetRecurringTasks_Res);
                            echo "<script>$('#subnav8').html('Recurring($RecurringTasksCount)');</script>";
                        }
                        if ($queue_name == 'recurring') {
                            $GetRecurringTasksQ = "SELECT `rt_id`, `rt_title`, `rt_body`, `rt_period`, `rt_staff_id`, `rt_team_id`, `is_active`, `duration`, `start_recurring_date`, `event_name` FROM `ost_recurring_tasks` WHERE `rt_assignor_id` = " . $thisstaff->getId();

                            if (($GetRecurringTasks_Res = db_query($GetRecurringTasksQ)) && db_num_rows($GetRecurringTasks_Res)) {
                                while (list($RecurringTaskID, $RecurringTaskTitle, $RecurringTaskBody, $RecurringTaskPeriod, $StaffID, $TeamID, $IsActive, $RecurringTaskDuration, $RecurringTaskStartTime, $EventName) = db_fetch_row($GetRecurringTasks_Res)) {
                                    $AgentTeamName = "Not found!";
                                    $GetStaffNameQ = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `staff_id` = " . $StaffID;

                                    if (($GetStaffName_Res = db_query($GetStaffNameQ)) && db_num_rows($GetStaffName_Res)) {
                                        $AgentTeamName = '<span class="Icon staffAssigned"></span>' . db_fetch_row($GetStaffName_Res)[0];
                                    } else {
                                        $GetTeamNameQ = "SELECT `name` FROM `ost_team` WHERE `team_id` = " . $TeamID;

                                        if (($GetTeamName_Res = db_query($GetTeamNameQ)) && db_num_rows($GetTeamName_Res)) {
                                            $AgentTeamName = '<span class="Icon teamAssigned"></span>' . db_fetch_row($GetTeamName_Res)[0];
                                        }
                                    }

                                    $ActivationIcon = 'ok.png';
                                    $ActivationIcon = $IsActive ? 'ok.png' : 'cancel.png';

                                    $RecurringTotal += 1;
                                    echo "
                        <tr>
                            <td align='center' style='cursor:pointer;'>
                                <a href='?rt_delete=$RecurringTaskID,$EventName' ><i class='fa icon-trash'></i></a>
                            </td>
                            <td>#$RecurringTaskID</td>
                            <td>$RecurringTaskTitle</td>
                            <td>$RecurringTaskBody</td>
                            <td>$RecurringTaskPeriod</td>
                            <td>" . Format::datetime(strtotime($RecurringTaskStartTime), false, false, 'UTC') . "</td>
                            <td>$RecurringTaskDuration Min</td>
                            <td>$AgentTeamName</td>
                            <td align='center' style='cursor:pointer;'>
                                <a href='?rt_toggle=$RecurringTaskID,$EventName' ><img src='images\icons\\$ActivationIcon' /></a>
                            </td>
                        </tr>
                    ";
                                }
                            }
                        }else if($queue_name == 'bookmarks'){
                            
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
                            
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'
                                    . ') ');
                            $where2 = '';
                           
                    
                            if (($teams = $thisstaff->getTeams()))
                                $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                    . ')  '
                                   
                                    . ')';
                    
                            if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                                $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';
                    
                            $where = implode(' OR ', $where);
                            if ($where) $where = ' ( ' . $where . ' ) ';
                    
                            $sql =  "SELECT task.`id`,task.`number`,task.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),task.`updated` ,`ost_team`.`name`  FROM `ost_task` as task
                            INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`
                            INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id`
                            INNER JOIN `ost_bookmark` ON `ost_bookmark`.`task_id`=task.`id`".
                            "LEFT JOIN `ost_team` ON`ost_team`.`team_id`=task.`team_id`"
                                . ' WHERE '
                                . "  `ost_bookmark`.`staff_id`=".$thisstaff->getId();
                        // echo $sql;
                                if (($GetUsersStaffTickets_Res = db_query($sql)) && db_num_rows($GetUsersStaffTickets_Res)) {
                             while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$Team) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                               if($ToStaff != null){
                                   $To=$ToStaff;
                               }
                               else{
                                $To=$Team;
                               }
                               ?>
                                <tr id="<?php echo $Number; ?>">
                                <td style="font-weight: bold;"  nowrap> <a class="preview" href="tasks.php?id=<?php echo $ID; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                <td align="center" nowrap><?php echo $Created; ?></td>
                                <td style="text-align: left;"><a    href="tasks.php?id=<?php echo $ID; ?>"><?php
                                                                           echo $Subject; ?></a></td></td>
                                
                                <td><span><?php echo  $Dep; ?></span></td>
                                <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                <td><span class="truncate"><?php echo $To; ?></span></td>
                                <td><span class="truncate"><?php echo $Update; ?></span></td>
                               
                            </tr>
                            <?php

                               }}  
                    // echo $sql;
                        }
                        else if($queue_name == 'atma'){
                            // $sql =  "SELECT task.`id`,task.`number`,task.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),task.`updated` 
                            // FROM `ost_task` as task 
                            // INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id` 
                            // INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` 
                            // LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` 
                            // INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id`  
                            // WHERE task.`dept_id` IN (SELECT `ost_staff_dept_access`.`dept_id` FROM `ost_staff_dept_access` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_staff_dept_access`.`staff_id`
                            // WHERE `ost_staff_dept_access`.`dept_id`<> `ost_staff`.`dept_id` AND `ost_staff`.`staff_id`=".$thisstaff->getId().")  AND task.`closed` IS Null ";
                            
                            $sql="SELECT task.`id`,task.`number`,task.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),task.`updated`,`ost_team`.`name` FROM
                            `ost_task` as task INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id` 
                            INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id`
                            LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` 
                            INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id`
                            LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id`
                            WHERE (task.`dept_id` IN (SELECT `ost_staff_dept_access`.`dept_id` FROM `ost_staff_dept_access` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_staff_dept_access`.`staff_id`
                            WHERE `ost_staff_dept_access`.`dept_id`<> `ost_staff`.`dept_id` AND `ost_staff`.`staff_id`=".$thisstaff->getId().") AND task.`closed` IS Null)
                            OR (task.`assignor_id` IN (SELECT `staff_id` FROM `ost_staff` WHERE `ost_staff`.`dept_id` IN (SELECT `ost_staff_dept_access`.`dept_id` FROM `ost_staff_dept_access` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_staff_dept_access`.`staff_id`
                            WHERE `ost_staff_dept_access`.`dept_id`<> `ost_staff`.`dept_id` AND `ost_staff`.`staff_id`=".$thisstaff->getId().")) AND task.`closed` IS Null)
                            OR (task.`staff_id` IN (SELECT `staff_id` FROM `ost_staff` WHERE `ost_staff`.`dept_id` IN (SELECT `ost_staff_dept_access`.`dept_id` FROM `ost_staff_dept_access` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_staff_dept_access`.`staff_id`
                            WHERE `ost_staff_dept_access`.`dept_id`<> `ost_staff`.`dept_id` AND `ost_staff`.`staff_id`=".$thisstaff->getId().")) AND task.`closed` IS Null)
                            
                            ORDER BY task.`created` DESC";
                        // echo $sql;
                                if (($GetUsersStaffTickets_Res = db_query($sql)) && db_num_rows($GetUsersStaffTickets_Res)) {
                             while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamId) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                               ?>
                                <tr id="<?php echo $Number; ?>">
                                <td  style="font-weight: bold;"  nowrap > <a class="preview" href="tasks.php?id=<?php echo $ID; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                <td align="center" nowrap ><?php echo $Created; ?></td>
                                <td style="text-align: left;"><a    href="tasks.php?id=<?php echo $ID; ?>"><?php
                                                                                                                                                                                            echo $Subject; ?></a></td>
                                
                                <td><span><?php echo  $Dep; ?></span></td>
                                <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                <?php if($ToStaff != null) {?>
                                <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                <?php } else if($TeamId != null) {?>
                                <td><span class="truncate"><?php echo $TeamId; ?></span></td>
                                <?php }?>
                                <td><span class="truncate"><?php echo $Update; ?></span></td>
                               
                            </tr>
                            <?php

                               }}  
                    // echo $sql;
                        }
                        // To Do
                        else if($queue_name == 'todo'){
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'. sprintf(' AND task.flags & %d != 0 ', TaskModel::ISOPEN)
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'. sprintf(' AND task.flags & %d != 0 ', TaskModel::ISOPEN)
                                    . ') ');
                            $where2 = '';

                            if (($teams = $thisstaff->getTeams()))
                            $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                . ') AND '
                                . sprintf('task.flags & %d != 0 ', TaskModel::ISOPEN)
                                . ')';

                        if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                            $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';

                        $where = implode(' OR ', $where);
                        if ($where) $where = 'AND ( ' . $where . ' ) ';

                        $ss="SELECT iid ,numii,cc,tit ,depp ,ff,To_name , dd FROM ( SELECT task.`id` As iid ,task.`number` AS numii,task.`created` AS cc,`ost_task__cdata`.`title`  AS tit,`ost_department`.`name` AS depp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) AS ff ,CONCAT(to_id.`firstname`, ' ', to_id.`lastname`) AS To_name ,task.`updated` As dd "
                        . " FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE task.flags & %d != 0 ", TaskModel::ISOPEN)
                        . $where . $where2 ." AND task.`closed` IS NULL
                        AND `ost_thread`.`object_type`='A'
                        GROUP BY task.id
                        HAVING COUNT(`ost_thread_entry`.`id`) = 1"
                        ." UNION SELECT task.`id` As iid,task.`number` AS numii ,task.`created` AS cc ,`ost_task__cdata`.`title` AS tit,`ost_department`.`name` AS depp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) AS ff  ,`ost_team`.`name` AS To_name ,task.`updated` As dd  "
                        . " FROM " . TASK_TABLE . " task "
                        ." INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ." INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ." LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ." INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ." INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                         INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE task.flags & %d != 0 ", TaskModel::ISOPEN)
                        . $where . $where2 ." AND task.`closed` IS NULL
                        AND `ost_thread`.`object_type`='A'
                        GROUP BY task.id
                        HAVING COUNT(`ost_thread_entry`.`id`) = 1"
                        ." ORDER By To_name , dd  DESC  ) as something WHERE To_name IS NOT NULL ORDER By To_name  , dd  DESC";

                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview" href="tasks.php?id=<?php echo $ID; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a    href="tasks.php?id=<?php echo $ID; ?>"><?php
                                                                                                                                                                                               echo $Subject; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        // Closed today
                        else if($queue_name == 'recentlyclosed'){
                            if (isset($_GET["sort"])){
                                //Sort By Number    
                                if($_GET["sort"]=="ID"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " NN  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  NN  ASC";
                                }
                                }
                                //Sort By LastUpdate    
                                if($_GET["sort"]=="LastUpdate"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Up  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Up  ASC";
                                }
                                }
                                //Sort By Title    
                                if($_GET["sort"]=="Title"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " TT  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  TT  ASC";
                                }
                                }
                                 //Sort By Department    
                                 if($_GET["sort"]=="Department"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Dp  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Dp  ASC";
                                }
                                }
                                 //Sort By From    
                                 if($_GET["sort"]=="FromName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Fm  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Fm  ASC";
                                }
                                }
                                 //Sort By To    
                                 if($_GET["sort"]=="ToName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " To_id  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  To_id  ASC";
                                }
                                }

                                   
                            }else{
                                $OrderBy =" CC  DESC";
                            }
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'
                                    . ') ');
                            $where2 = '';

                            if (($teams = $thisstaff->getTeams()))
                            $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                . ')  '
                                . ')';

                        if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                            $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';

                        $where = implode(' OR ', $where);
                        if ($where) $where = ' ( ' . $where . ' ) ';
                        $ss=  "SELECT  Wee,NN,CR,TT ,Dp ,Fm  , To_id ,Up,  CC from 

                        (SELECT task.`id` AS Wee,task.`number` as NN ,task.`created` as CR,`ost_task__cdata`.`title`  as TT ,`ost_department`.`name` as Dp  ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm ,CONCAT(to_id.`firstname`, ' ', to_id.`lastname`) as To_id ,task.`updated` as Up , task.`closed` AS CC  " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  SUBSTRING_INDEX(task.`closed`, ' ', 1) =SUBSTRING_INDEX(NOW(), ' ', 1)"
                        ."  UNION   SELECT task.`id` AS Wee,task.`number` as NN,task.`created` as CR,`ost_task__cdata`.`title`  as TT,`ost_department`.`name`  as Dp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm,`ost_team`.`name` as To_id ,task.`updated` as Up , task.`closed` AS CC " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  SUBSTRING_INDEX(task.`closed`, ' ', 1) =SUBSTRING_INDEX(NOW(), ' ', 1)" 
                        ." GROUP BY task.id
                         ORDER by " . $OrderBy.") as bigTable WHERE bigTable.To_id IS NOT NULL";

                        // echo $ss;
                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview"  onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"  data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a    onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"><?php
                                                                                                                                                                                               echo $Subject; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        // Closed yesterday
                        else if($queue_name == 'closedyesterday'){
                            if (isset($_GET["sort"])){
                                //Sort By Number    
                                if($_GET["sort"]=="ID"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " NN  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  NN  ASC";
                                }
                                }
                                //Sort By LastUpdate    
                                if($_GET["sort"]=="LastUpdate"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Up  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Up  ASC";
                                }
                                }
                                //Sort By Title    
                                if($_GET["sort"]=="Title"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " TT  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  TT  ASC";
                                }
                                }
                                 //Sort By Department    
                                 if($_GET["sort"]=="Department"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Dp  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Dp  ASC";
                                }
                                }
                                 //Sort By From    
                                 if($_GET["sort"]=="FromName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Fm  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Fm  ASC";
                                }
                                }
                                 //Sort By To    
                                 if($_GET["sort"]=="ToName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " To_id  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  To_id  ASC";
                                }
                                }

                                   
                            }else{
                                $OrderBy =" CC DESC";
                            }
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'
                                    . ') ');
                            $where2 = '';

                            if (($teams = $thisstaff->getTeams()))
                            $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                . ')  '
                                . ')';

                        if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                            $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';

                        $where = implode(' OR ', $where);
                        if ($where) $where = '  ( ' . $where . ' ) ';
                        $ss=  "SELECT  Wee,NN,CR,TT ,Dp ,Fm  , To_id ,Up,  CC from 

                        (SELECT task.`id` AS Wee,task.`number` as NN ,task.`created` as CR,`ost_task__cdata`.`title`  as TT ,`ost_department`.`name` as Dp  ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm ,CONCAT(to_id.`firstname`, ' ', to_id.`lastname`) as To_id ,task.`updated` as Up , task.`closed` AS CC  " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  SUBSTRING_INDEX(task.`closed`, ' ', 1) = subdate(current_date, 1) "
                        ."  UNION   SELECT task.`id` AS Wee,task.`number` as NN,task.`created` as CR,`ost_task__cdata`.`title`  as TT,`ost_department`.`name`  as Dp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm,`ost_team`.`name` as To_id ,task.`updated` as Up , task.`closed` AS CC " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  SUBSTRING_INDEX(task.`closed`, ' ', 1) = subdate(current_date, 1) " 
                        ." GROUP BY task.id
                         ORDER by " . $OrderBy.") as bigTable WHERE bigTable.To_id IS NOT NULL";

                        
                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamId) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a    onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"><?php
                                                                                                                                                                                               echo $Subject; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        // Closed week
                        else if($queue_name == 'closedweek'){
                            if (isset($_GET["sort"])){
                                //Sort By Number    
                                if($_GET["sort"]=="ID"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " NN  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  NN  ASC";
                                }
                                }
                                //Sort By LastUpdate    
                                if($_GET["sort"]=="LastUpdate"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Up  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Up  ASC";
                                }
                                }
                                //Sort By Title    
                                if($_GET["sort"]=="Title"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " TT  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  TT  ASC";
                                }
                                }
                                 //Sort By Department    
                                 if($_GET["sort"]=="Department"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Dp  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Dp  ASC";
                                }
                                }
                                 //Sort By From    
                                 if($_GET["sort"]=="FromName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Fm  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = " Fm  ASC";
                                }
                                }
                                 //Sort By To    
                                 if($_GET["sort"]=="ToName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " To_id  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  To_id  ASC";
                                }
                                }

                                   
                            }else{
                                $OrderBy =" CC DESC";
                            }
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'
                                    . ') ');
                            $where2 = '';

                            if (($teams = $thisstaff->getTeams()))
                            $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                . ')  '
                                . ')';

                        if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                            $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';

                        $where = implode(' OR ', $where);
                        if ($where) $where = '  ( ' . $where . ' ) ';
                        $ss=  "SELECT  Wee,NN,CR,TT ,Dp ,Fm  , To_id ,Up,  CC from 

                        (SELECT task.`id` AS Wee,task.`number` as NN ,task.`created` as CR,`ost_task__cdata`.`title`  as TT ,`ost_department`.`name` as Dp  ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm ,CONCAT(to_id.`firstname`, ' ', to_id.`lastname`) as To_id ,task.`updated` as Up , task.`closed` AS CC  " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  timestampdiff(DAY,task.`closed`,NOW()) <= 7 "
                         ."  UNION   SELECT task.`id` AS Wee,task.`number` as NN,task.`created` as CR,`ost_task__cdata`.`title`  as TT,`ost_department`.`name`  as Dp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm,`ost_team`.`name` as To_id ,task.`updated` as Up , task.`closed` AS CC " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  timestampdiff(DAY,task.`closed`,NOW()) <= 7 " 
                        ." GROUP BY task.id
                         ORDER by " . $OrderBy.") as bigTable WHERE bigTable.To_id IS NOT NULL";

                        
                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamId) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a    onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"><?php
                                                                                                                                                                                               echo $Subject; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   
                                   <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        // Closed month
                        else if($queue_name == 'closedmonth'){
                            if (isset($_GET["sort"])){
                                //Sort By Number    
                                if($_GET["sort"]=="ID"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " NN  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  NN  ASC";
                                }
                                }
                                //Sort By LastUpdate    
                                if($_GET["sort"]=="LastUpdate"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Up  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Up  ASC";
                                }
                                }
                                //Sort By Title    
                                if($_GET["sort"]=="Title"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " TT  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  TT  ASC";
                                }
                                }
                                 //Sort By Department    
                                 if($_GET["sort"]=="Department"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Dp  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Dp  ASC";
                                }
                                }
                                 //Sort By From    
                                 if($_GET["sort"]=="FromName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " Fm  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  Fm  ASC";
                                }
                                }
                                 //Sort By To    
                                 if($_GET["sort"]=="ToName"){
                                    if($_GET["order"]=="DESC"){
                                        $OrderBy = " To_id  DESC";
                                    }
                                elseif ($_GET["order"]=="ASC") {
                                    $OrderBy = "  To_id  ASC";
                                }
                                }

                                   
                            }else{
                                $OrderBy =" CC DESC";
                            }
                            $sql1='SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
                            $agents = array();
                            if (($res=db_query($sql1)) && db_num_rows($res)) {
                                while(list($id)=db_fetch_row($res))
                                    $agents[] = (int) $id;
                                   
                            }
                            $where = array('(task.staff_id IN(' . implode(',', db_input($agents))
                            . ')'
                                . ')  OR '.'(task.`assignor_id` IN(' . implode(',', db_input($agents))
                                . ')'
                                    . ') ');
                            $where2 = '';

                            if (($teams = $thisstaff->getTeams()))
                            $where[] = ' ( task.team_id IN(' . implode(',', db_input(array_filter($teams)))
                                . ') '
                                . ')';

                        if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                            $where[] = 'task.dept_id IN(' . implode(',', db_input($depts)) . ') ';

                        $where = implode(' OR ', $where);
                        if ($where) $where = '  ( ' . $where . ' ) ';
                        $ss=  "SELECT  Wee,NN,CR,TT ,Dp ,Fm  , To_id ,Up,  CC from 

                        (SELECT task.`id` AS Wee,task.`number` as NN ,task.`created` as CR,`ost_task__cdata`.`title`  as TT ,`ost_department`.`name` as Dp  ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm ,CONCAT(to_id.`firstname`, ' ', to_id.`lastname`) as To_id ,task.`updated` as Up , task.`closed` AS CC  " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  timestampdiff(DAY,task.`closed`,NOW()) <= 34"
                        ."  UNION   SELECT task.`id` AS Wee,task.`number` as NN,task.`created` as CR,`ost_task__cdata`.`title`  as TT,`ost_department`.`name`  as Dp ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`) as Fm,`ost_team`.`name` as To_id ,task.`updated` as Up , task.`closed` AS CC " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id` "
                        . sprintf(" WHERE ")
                        . $where
                        ." AND task.`closed` IS NOT NULL AND  timestampdiff(DAY,task.`closed`,NOW()) <= 34 " 
                        ." GROUP BY task.id
                         ORDER by " . $OrderBy.") as bigTable WHERE bigTable.To_id IS NOT NULL";

                     
                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamId) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"  data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a    onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"><?php
                                                                                                                                                                                               echo $Subject; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   
                                   <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        // Assign To My Showroom
                        else if($queue_name == 'atms'){
                        
                        $ss=  "SELECT task.`id`,task.`number`,task.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),task.`updated`,`ost_team`.`name`  " 
                        . "FROM " . TASK_TABLE . " task "
                        ."INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  "
                        ."INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` " 
                        ."LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` "
                        ."INNER JOIN `ost_department` ON `ost_department`.`id`=task.`dept_id` "
                        ." LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id` " 
                        ."INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
                        INNER JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id` 
                        INNER JOIN `ost_agent_users_tickets` ON `ost_agent_users_tickets`.`user_id`=`ost_thread_collaborator`.`user_id` "
                        ." WHERE `ost_agent_users_tickets`.`staff_id` =".$thisstaff->getId()." AND `ost_thread`.`object_type`='A' AND  task.`closed` IS NULL ";

                        
                            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamId) = db_fetch_row($GetUsersStaffTickets_Res)) {    
                                  ?>
                                   <tr id="<?php echo $Number; ?>">
                                   <td  style="font-weight: bold;"  nowrap > <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#"  data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                                   <td align="center" nowrap ><?php echo $Created; ?></td>
                                   <td style="text-align: left;"><a   onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false"  href="#" ><?php
                                                                                                                                                                                                                             echo $Subject; echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;'; ?></a></td>
                                   
                                   <td><span><?php echo  $Dep; ?></span></td>
                                   <td style="text-align: left;" nowrap><span><?php echo '<span class="Icon staffAssigned">'.$FromStaff; ?></span></td>
                                   <?php if($ToStaff != null) {?>
                                <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                                <?php } else if($TeamId != null) {?>
                                <td><span class="truncate"><?php echo $TeamId; ?></span></td>
                                <?php }?>
                                   <td><span class="truncate"><?php echo $Update; ?></span></td>
                                  
                               </tr>
                               <?php
   
                                  }}
                        // echo $ss;
                        }
                        else {
                            foreach ($tasks as $T) {

                                // foreach ($T as $tt) {
                                //     echo $tt."<br>";
                                //     echo "//";
                                // }
                                $lastUpdate="SELECT Max(`ost_thread_entry`.`created`) FROM `ost_task` INNER JOIN `ost_thread` ON  `ost_task`.`id`= `ost_thread`.`object_id` 
                                INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id`
                                WHERE 
                                `ost_thread`.`object_type`='A' AND `ost_task`.`id`=".$T['id'];
                                // echo $lastUpdate;
                                if(($lastUpdate_Res = db_query($lastUpdate)) && db_num_rows($lastUpdate_Res)) {
                                    $Res = db_fetch_row($lastUpdate_Res);
                                    $date_cl=$Res[0];
                                    $newLastUpdate= new DateTime($date_cl);
                                }
                                $T['isopen'] = ($T['flags'] & TaskModel::ISOPEN != 0); //XXX:
                                $total += 1;
                                $tag = $T['staff_id'] ? 'assigned' : 'openticket';
                                $flag = null;

                                if ($T['lock__staff_id'] && $T['lock__staff_id'] != $thisstaff->getId())
                                    $flag = 'locked';
                                elseif ($T['isoverdue'])
                                    $flag = 'overdue';

                                $DueSoonColorStyle = '';
                                $sql="SELECT `status` FROM `ost_task` WHERE `id`=".$T['id'];
                                if (($P_Res = db_query($sql)) && db_num_rows($P_Res)) {
                                    $Progress = db_fetch_row($P_Res);
                                } 

                                // INNER JOIN `ost_task__cdata` ON `ost_recurring_tasks`.`rt_title` LIKE `ost_task__cdata`.`title`
                                // WHERE  `ost_task__cdata`.`task_id`=".$T['id'];
                                // // echo  $sql;
                                // if(($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                                //     $Res_sql = db_fetch_row($sql_Res);
                                //     $IsRecurring=$Res_sql[0];
                                // }

                                
                                
                            //     if($IsRecurring != "" && ($IsRecurring == "MONTH" ||  $IsRecurring == "WEEK"  || $IsRecurring == "ONETIME" )){
                            //         // echo $IsRecurring;
                                    
                                    
                            //         $CreatedDateD = new DateTime($T['created']);
                            //         $NowDateD = new DateTime("Asia/Damascus");
                            //         $CreatedDate = strtotime($CreatedDateD->format('Y-m-d H:i:s'));
                            //         $NowDate = strtotime($NowDateD->format('Y-m-d H:i:s'));
                            //         // echo $CreatedDate;
                            //         // echo "<br>";
                            //         // echo $NowDate ;
                            //         $DateDiff = $NowDate - $CreatedDate;
                            //         $DiffInDays = round($DateDiff / (60 * 60 * 24));
                            //         // echo "<br>".$DiffInDays;
                            //         if ($DiffInDays > 7) {
                            //             $DueSoonColorStyle = 'style="background:#FFD2E1"';
                            //             $Task = Task::GetTaskByNumber($T['number']);
                            //             $Task->MarkAsOverdue();
                            //         }
                                
                            // } else{
                                if ($T['duedate']) {
                                    $CreatedDateD = new DateTime($T['created']);
                                    $DueDateD = new DateTime($T['duedate']);
                                    $UpDateD = new DateTime($T['updated']);
                                    $NowDateD = new DateTime("Asia/Damascus");
                                    
                                    $CreatedDate = strtotime($CreatedDateD->format('Y-m-d H:i:s'));
                                    $DueDate = strtotime($DueDateD->format('Y-m-d H:i:s'));
                                    $NowDate = strtotime($NowDateD->format('Y-m-d H:i:s'));
                                    $NowDateUp = strtotime($NowDateD->format('Y-m-d'));
                                    $UpDate = strtotime($UpDateD->format('Y-m-d H:i:s'));
                                    $DateDiff = $NowDate - $DueDate;
                                    $DateDiffUp =  $UpDate-$DueDate;
                                    
                                    if ($DateDiff > 0) {
                                        if ($queue_name == 'open' || $queue_name == 'assigned' || $queue_name == 'afm' || $queue_name == 'assignedToTeams') {
                                            $DueSoonColorStyle = 'style="background:#FFD2E1"';
                                            $Task = Task::GetTaskByNumber($T['number']);

                                            if ($Task)
                                                $Task->MarkAsOverdue();

                                            if($DateDiffUp > 0){
                                                
                                                $DueSoonColorStyle = 'style="background:#FFAA71"';
                                            }
                                        }
                                    } else {
                                        if ($queue_name == 'open' || $queue_name == 'assigned' || $queue_name == 'afm' || $queue_name == 'assignedToTeams') {
                                            $DueDateDiffS = abs($DueDate - $CreatedDate); //In Seconds
                                            $DueSoonPercentage = abs($DueDateDiffS) * 0.7;

                                            if ($NowDate >= $CreatedDate + $DueSoonPercentage) {
                                                $DueSoonColorStyle = 'style="background:#FFFBA8"';
                                            }
                                        }
                                    }
                                }
                            // }
                                $assignee = '';
                                $dept = Dept::getLocalById($T['dept_id'], 'name', $T['dept__name']);

                                if ($T['staff_id']) {
                                    $staff =  new AgentsName($T['staff__firstname'] . ' ' . $T['staff__lastname']);

                                    if ($staff != "") {
                                        $assignee = sprintf('<span class="Icon staffAssigned">%s</span>', Format::truncate((string) $staff, 40));
                                    } else {
                                        $assignee = 'No Agent (Old Ver)';
                                    }
                                } elseif ($T['team_id']) {
                                    $assignee = sprintf('<span class="Icon teamAssigned">%s</span>', Format::truncate(Team::getLocalById($T['team_id'], 'name', $T['team__name']), 40));
                                } else {
                                    $assignee = '<i class="Icon deptAssigned"></i><strong>' . $dept . ' Department</strong>';
                                }

                                $assignor = Staff::GetStaffNameByID($T['assignor_id']);

                                if ($assignor == "") {
                                    $assignor = "No Agent (Old Ver)";
                                } else {
                                    $assignor = '<span class="Icon staffAssigned">' . $assignor;
                                }



                                $LastThreadEntry = Thread::getLastThreadEntryTask($T['id']);
                                $Body = "Not available!";
                                $LastResponse = "";
                                $poster = "";
                                if ($LastThreadEntry != null) {
                                    $Body = $LastThreadEntry[0];
                                    $Body = strip_tags($Body, '<br /><br/><br>');
                                    $Body = substr($Body, 0, 200);
                                    $LastResponse = $LastThreadEntry[1];
                                    $poster = $LastThreadEntry[2];
                                }



                                $threadcount = $T['thread_count'];
                                $number = $T['number'];

                                if ($T['isopen'])
                                    $number = sprintf('<b>%s</b>', $number);

                                $title = Format::truncate($title_field->display($title_field->to_php($T['cdata__title'])), 120);
                        if($Progress[0] == 1 && $T['isopen'] ){
                            $title=$title." "."[InProgress]";
                            $ProgressStyle = 'style="background:#89E884"';
                        }
                        else{
                            $ProgressStyle ='style=""';
                        }
                        ?>
                                <tr id="<?php echo $T['id']; ?>">
                                    <?php
                                    if ($thisstaff->canManageTickets()) {
                                        $sel = false;
                                        if ($ids && in_array($T['id'], $ids))
                                            $sel = true;
                                    ?>
                                    <?php if($Progress[0] == 1 && $T['isopen'] ){ ?>
                                        <td align="center" class="nohover" <?php echo $ProgressStyle; ?>>
                                            <input class="ckb" type="checkbox" name="tids[]" value="<?php echo $T['id']; ?>" <?php echo $sel ? 'checked="checked"' : ''; ?>>
                                        </td>
                                        <?php } else{?>
                                        <td align="center" class="nohover" <?php echo $DueSoonColorStyle ?>>
                                            <input class="ckb" type="checkbox" name="tids[]" value="<?php echo $T['id']; ?>" <?php echo $sel ? 'checked="checked"' : ''; ?>>
                                        </td>
                                            <?php }?>
                                    <?php } ?>
                                    <?php if($Progress[0] == 1 && $T['isopen'] ){ ?>
                                        <td nowrap <?php echo $ProgressStyle; ?>>
                                        <a class="preview" onclick="window.open('tasks.php?id=<?php echo $T['id']; ?>','_blank');window.close();return false"  href="#" data-preview="#tasks/<?php echo $T['id']; ?>/preview"><?php echo $number; ?></a></td>
                                        <?php } else{?>
                                    <td nowrap <?php echo $DueSoonColorStyle ?>>
                                        <a class="preview" onclick="window.open('tasks.php?id=<?php echo $T['id']; ?>','_blank');window.close();return false"  href="#" data-preview="#tasks/<?php echo $T['id']; ?>/preview"><?php echo $number; ?></a></td>
                                            <?php }?>
                                    <td align="center" nowrap <?php echo $DueSoonColorStyle ?>><?php echo
                                        $newLastUpdate->format('d-m-Y H:i:s') ?></td>
                                    <td <?php echo $DueSoonColorStyle ?>><a <?php if ($flag) { ?> class="Icon <?php echo $flag; ?>Ticket" title="<?php echo ucfirst($flag); ?> Ticket" <?php } ?> href="tasks.php?id=<?php echo $T['id']; ?>"><?php
                                                                                                                                                                                                                                                echo $title; ?></a>
                                        <?php
                                        if ($threadcount > 1)
                                            echo "<small>($threadcount)</small>&nbsp;" . '<i
                                    class="icon-fixed-width icon-comments-alt"></i>&nbsp;';
                                        if ($T['collab_count'])
                                            echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;';
                                        if ($T['attachment_count'])
                                            echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';
                                        ?>
                                    </td>







                                    <td <?php echo $DueSoonColorStyle ?> nowrap>&nbsp;<?php echo Format::truncate($dept, 40); ?></td>
                                    <td <?php echo $DueSoonColorStyle ?> nowrap>&nbsp;<?php echo $assignor; ?></td>
                                   <?php if($T['staff_id'] == $thisstaff->getId() && $results_type . $showing == "My Tasks"){?>
                                    <td style="background-color:#84B1FD; color:#FFF" nowrap>&nbsp;<?php echo $assignee; ?></td>
                                    <?php } else {?>
                                        <td <?php echo $DueSoonColorStyle?> nowrap>&nbsp;<?php echo $assignee; ?></td>
                                    <?php } ?>
                                    <td <?php echo $DueSoonColorStyle ?> nowrap>&nbsp;<?php echo $poster . " " . $LastResponse ?></td>

                                </tr>
                        <?php
                            } //end of foreach
                        }

                        if (($queue_name != 'recurring' && !$total) || !$RecurringTotal)
                            $ferror = __('There are no tasks matching your criteria.');
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="12">
                                <?php if ($total && $thisstaff->canManageTickets()) { ?>
                                    <?php echo __('Select'); ?>:&nbsp;
                                    <a id="selectAll" href="#ckb"><?php echo __('All'); ?></a>&nbsp;&nbsp;
                                    <a id="selectNone" href="#ckb"><?php echo __('None'); ?></a>&nbsp;&nbsp;
                                    <a id="selectToggle" href="#ckb"><?php echo __('Toggle'); ?></a>&nbsp;&nbsp;
                                <?php } else {
                                    echo '<i>';
                                    if ($queue_name == 'recurring') {
                                        echo $ferror ? Format::htmlchars($ferror) : __("Query returned $RecurringTotal results.");
                                    } else {
                                        echo $ferror ? Format::htmlchars($ferror) : __('Query returned 0 results.');
                                    }
                                    echo '</i>';
                                } ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <br>
<!--yaseen added this -->
<?php if ($queue_name == 'assigned') 
  $DueDate = new DateTime("Asia/Damascus");
  $DueDate = $DueDate->format('Y-m-d H:i:s T');
 // echo $DueDate;
{
?>

    <h2>Assigned From Me</h2>
    <table style="margin-top:2em" class="list" border="0" cellspacing="1" cellpadding="2" width="100%">
        <thead>
            <?php if ($thisstaff->canManageTickets()) { ?>
                <th width="4%">&nbsp;</th>
            <?php } ?>
            <?php
            unset($args['sort'], $args['dir'], $args['_pjax']);
            $qstr = Http::build_query($args);
            foreach ($queue_columns as $k => $column) {
                echo sprintf(
                    '<th width="%s"><a href="?sort=%s&dir=%s&%s" class="%s">%s</a></th>',
                    $column['width'],
                    $column['sort'] ?: $k,
                    $column['sort_dir'] ? 0 : 1,
                    $qstr,
                    isset($column['sort_dir'])
                        ? ($column['sort_dir'] ? 'asc' : 'desc') : '',
                    $column['heading']
                );
            }
            ?>
        </thead>
        <tbody>
            <?php
           $ss =  "SELECT  task.`id`,task.`number`,task.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),task.`updated`,`ost_team`.`name`,task.duedate ,Count(ost_thread_entry.id) ,Count(ost_attachment.id),count(ost_thread_collaborator.id)
           FROM ost_task task 
           INNER JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=task.`id`  
           INNER JOIN `ost_staff` as from_id ON from_id.`staff_id`=task.`assignor_id` 
           LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=task.`staff_id` 
           INNER JOIN `ost_department`  ON `ost_department`.`id`=task.`dept_id` 
           LEFT JOIN `ost_team` ON `ost_team`.`team_id`=task.`team_id`
          INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=task.`id`
           Left join ost_thread_entry ON ost_thread.id = ost_thread_entry.thread_id 
           left JOIN ost_attachment ON ost_thread_entry.id = ost_attachment.object_id
           left join ost_thread_collaborator on ost_thread.id=ost_thread_collaborator.thread_id
           WHERE task.`assignor_id` = " . $thisstaff->getId() . "  AND ost_thread.object_type='A' AND  task.closed is null
           group by task.`id`order by task.`updated` desc ";
            if (($GetUsersStaffTickets_Res = db_query($ss)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Number, $Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update, $TeamId,$duedate,$threadscount,$attachments_count,$collab_count) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    $sql = "SELECT `status` FROM `ost_task` WHERE `id`=" . $ID;
                    if (($P_Res = db_query($sql)) && db_num_rows($P_Res)) {
                        $Progress = db_fetch_row($P_Res);
                    }
                    if ($Progress[0] == 1 ) {
                        $title = $title . " " . "[InProgress]";
                        $ProgressStyle = 'style="background:#89E884"';
                    } else {
                        $ProgressStyle = 'style=""';
                    }
                    if ($duedate) {
                        $CreatedDateD = new DateTime($Created);
                        $DueDateD = new DateTime($duedate);
                        $UpDateD = new DateTime($Update);
                        $NowDateD = new DateTime("Asia/Damascus");

                        $CreatedDate = strtotime($CreatedDateD->format('Y-m-d H:i:s'));
                        $DueDate = strtotime($DueDateD->format('Y-m-d H:i:s'));
                        $NowDate = strtotime($NowDateD->format('Y-m-d H:i:s'));
                        $NowDateUp = strtotime($NowDateD->format('Y-m-d'));
                        $UpDate = strtotime($UpDateD->format('Y-m-d H:i:s'));
                        $DateDiff = $NowDate - $DueDate;
                        $DateDiffUp =  $UpDate - $DueDate;

                        if ($DateDiff > 0) {
                           
                                $DueSoonColorStyle = 'style="background:#FFD2E1"';
                                $Task = Task::GetTaskByNumber($T['number']);
                                if ($Task)
                                    $Task->MarkAsOverdue();
                                if ($DateDiffUp > 0) {
                                    $DueSoonColorStyle = 'style="background:#FFAA71"';
                                }
                        } else {
                                $DueDateDiffS = abs($DueDate - $CreatedDate); //In Seconds
                                $DueSoonPercentage = abs($DueDateDiffS) * 0.7;

                                if ($NowDate >= $CreatedDate + $DueSoonPercentage) {
                                    $DueSoonColorStyle = 'style="background:#FFFBA8"';
                                }
                        }
                    } 
                    ?>
                    <tr id="<?php echo $Number; ?>">
                    <?php if ($Progress[0] == 1) { ?>
                            <td align="center" class="nohover" <?php echo $ProgressStyle; ?>>
                                <input class="ckb" type="checkbox" name="tids[]" value="<?php echo $ID; ?>" <?php echo $sel ? 'checked="checked"' : ''; ?>>
                            </td>
                        <?php } else { ?>
                            <td align="center" class="nohover" <?php echo $DueSoonColorStyle ?>>
                                <input class="ckb" type="checkbox" name="tids[]" value="<?php echo $ID; ?>" <?php echo $sel ? 'checked="checked"' : ''; ?>>
                            </td>
                        <?php } ?>
                  
                    <?php if ($Progress[0] == 1 ) { ?>
                        <td nowrap <?php echo $ProgressStyle; ?>>
                            <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false" href="#" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a>
                        </td>
                    <?php } else { ?>
                        <td nowrap <?php echo $DueSoonColorStyle ?>>
                            <a class="preview" onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false" href="#" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a>
                        </td>
                    <?php } ?>
                        <td <?php echo $DueSoonColorStyle ?>> <?php echo $Created; ?></td>
                        <td <?php echo $DueSoonColorStyle ?>><a onclick="window.open('tasks.php?id=<?php echo $ID; ?>','_blank');window.close();return false" href="#"><?php echo $Subject;  ?> </a>
                                        <?php
                                        if ($threadscount > 1)
                                            echo "<small>($threadscount)</small>&nbsp;" . '<i
                                    class="icon-fixed-width icon-comments-alt"></i>&nbsp;';
                                        if ($collab_count>0)
                                            echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;';
                                        if ($attachments_count>0)
                                            echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';
                                        ?>
                                    </td>
                        <td <?php echo $DueSoonColorStyle ?>> <?php echo  $Dep; ?></td>
                        <td <?php echo $DueSoonColorStyle ?>><span><?php echo '<span class="Icon staffAssigned">' . $FromStaff; ?></span></td>
                        <?php if ($ToStaff != null) { ?>
                            <td <?php echo $DueSoonColorStyle ?>><span class="truncate"><?php echo $ToStaff; ?></span></td>
                        <?php } else if ($TeamId != null) { ?>
                            <td <?php echo $DueSoonColorStyle ?>><span class="truncate"><?php echo $TeamId; ?></span></td>
                        <?php } else { ?>
                            <td <?php echo $DueSoonColorStyle ?>> </td><?php
                                    } ?>
                        <td <?php echo $DueSoonColorStyle ?>><span class="truncate"><?php echo $Update; ?></span></td>
                    </tr>
            <?php
                }
            } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="12">
                    <?php if ($total && $thisstaff->canManageTickets()) { ?>
                        <?php echo __('Select'); ?>:&nbsp;
                        <a id="selectAll" href="#ckb"><?php echo __('All'); ?></a>&nbsp;&nbsp;
                        <a id="selectNone" href="#ckb"><?php echo __('None'); ?></a>&nbsp;&nbsp;
                        <a id="selectToggle" href="#ckb"><?php echo __('Toggle'); ?></a>&nbsp;&nbsp;
                <?php } else {
                        echo '<i>';
                        if ($queue_name == 'recurring') {
                            echo $ferror ? Format::htmlchars($ferror) : __("Query returned $RecurringTotal results.");
                        } else {
                            echo $ferror ? Format::htmlchars($ferror) : __('Query returned 0 results.');
                        }
                        echo '</i>';
                    }
                } ?>
                </td>
            </tr>
        </tfoot>
    </table>
    <!--end yaseen code-->
                <?php
                //rahaf
                if ($total > 0) { //if we actually had any tasks returned.
                    // if($sort_cols !== 'From' || $sort_cols !== 'To' ){}
                    // else{
                    echo '<div>&nbsp;' . __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
                    $_SESSION["task_page_number"]=$page ;
                    echo sprintf(
                        '<a class="export-csv no-pjax" href="?%s">%s</a>',
                        Http::build_query(array(
                            'a' => 'export', 'h' => $hash,
                            'status' => $_REQUEST['status']
                        )),
                        __('Export')
                    );
                    echo '&nbsp;<i class="help-tip icon-question-sign" href="#export"></i></div>';

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
                        
                        echo '<div style="position:  position: relative;top: 250px;">&nbsp;' . __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
                    }
                    else{
                        echo '<div style="position: absolute;top: 230px;">&nbsp;' . __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
                    }
                   
                    echo sprintf(
                        '<a class="export-csv no-pjax" href="?%s">%s</a>',
                        Http::build_query(array(
                            'a' => 'export', 'h' => $hash,
                            'status' => $_REQUEST['status']
                        )),
                        __('Export')
                    );
                    echo '&nbsp;<i class="help-tip icon-question-sign" href="#export"></i></div>';
                }
                // } ?>
            </form>
        </div>

        <div style="display:none;" class="dialog" id="confirm-action">
            <h3><?php echo __('Please Confirm'); ?></h3>
            <a class="close" href=""><i class="icon-remove-circle"></i></a>
            <hr />
            <p class="confirm-action" style="display:none;" id="mark_overdue-confirm">
                <?php echo __('Are you sure want to flag the selected tasks as <font color="red"><b>overdue</b></font>?'); ?>
            </p>
            <div><?php echo __('Please confirm to continue.'); ?></div>
            <hr style="margin-top:1em" />
            <p class="full-width">
                <span class="buttons pull-left">
                    <input type="button" value="<?php echo __('No, Cancel'); ?>" class="close">
                </span>
                <span class="buttons pull-right">
                    <input type="button" value="<?php echo __('Yes, Do it!'); ?>" class="confirm">
                </span>
            </p>
            <div class="clear"></div>
        </div>
        <script type="text/javascript">
            $(function() {
                $(document).off('.new-task');
                $(document).on('click.new-task', 'a.new-task', function(e) {
                    e.preventDefault();
                    var url = 'ajax.php/' +
                        $(this).attr('href').substr(1) +
                        '?_uid=' + new Date().getTime();
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

<script>
    document.addEventListener('play', function(e){
    var audios = document.getElementsByTagName('audio');
    for(var i = 0, len = audios.length; i < len;i++){
        if(audios[i] != e.target){
            audios[i].pause();
        }
    }
}, true);
    </script>

