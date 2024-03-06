
<?php
if (!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('Access Denied');

$settings = &$_SESSION['client:Q'];

// Unpack search, filter, and sort requests
if (isset($_REQUEST['clear']))
    $settings = array();
if (isset($_REQUEST['keywords'])) {
    $settings['keywords'] = $_REQUEST['keywords'];
}
if (isset($_REQUEST['topic_id'])) {
    $settings['topic_id'] = $_REQUEST['topic_id'];
}
if (isset($_REQUEST['status'])) {
    $settings['status'] = $_REQUEST['status'];
}
if ($_REQUEST['a'] == 'Asearch'){
    $refresh_url = $path . '?a=Asearch';
}
// $OpenTasks =array();
// $sqlgetOpenTasksCount = "select count(*)  
// FROM `ost_task` 
// LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
// LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
// LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
// WHERE (`ost_thread_collaborator`.`user_id`=".$_REQUEST['user_id']." OR `ost_thread_collaborator`.`team_id` IN(SELECT `team_id` FROM `ost_team_user_member` WHERE `user_id`=".$_REQUEST['user_id'].") ) AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NULL ";
// if (($sqlgetOpenTasksCount_Res = db_query($sqlgetOpenTasksCount)) && db_num_rows($sqlgetOpenTasksCount_Res)) {
//     while (list($ID) = db_fetch_row($sqlgetOpenTasksCount_Res)) {
//         array_push($OpenTasks, $ID);
//     }
// }
// $closedTasks =array();
// $sqlgetclosedTasksCount = "SELECT count(*) from (
// SELECT `ost_task`.`id`
// FROM `ost_task` 
// LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
// LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
// LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
// LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
// LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
// WHERE `ost_thread_collaborator`.`user_id`=".$_REQUEST['closedTask']."  AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NOT NULL 
// UNION
// SELECT `ost_task`.`id`
// FROM `ost_task` 
// LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
// LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
// LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
// LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
// LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
// LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
// WHERE  `ost_thread_collaborator`.`team_id` IN(SELECT `team_id` FROM `ost_team_user_member` WHERE `user_id`=".$_REQUEST['closedTask'].")  AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NOT NULL 
// ) as s  ";
// if (($sqlgetclosedTasksCount_Res = db_query($sqlgetclosedTasksCount)) && db_num_rows($sqlgetclosedTasksCount_Res)) {
//     while (list($ID) = db_fetch_row($sqlgetclosedTasksCount_Res)) {
//         array_push($closedTasks, $ID);
//     }
// }

if(isset($_REQUEST['user_id']) && !isset($_REQUEST['closedTask'])&& ($_REQUEST['a'] !== 'Asearch')) {
    $_SESSION['userAsagent_name']="user";
    $_SESSION['userAsagent_pass']="mabco1234";
    $results = ucfirst($status) . ' ' . __('Tasks');
    $negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting

    echo "Open Tasks Count : " .$OpenTasks[0];
    ?>
      <div id="basic_search">
    <div style="min-height:60px;" >
   
        <!--<p><?php //echo __('Select the starting time and period for the system activity graph');?></p>-->
        <form method="post" action="tickets.php?a=Asearch" >
        <?php echo csrf_token(); ?>
        <label>
            <?php echo __( 'Search By');?>:
            <select name="gets" id="gets">
                <option value="0" >
                    <?php echo __( 'ID');?>
                </option>
                <option value="1">
                    <?php echo __( 'Number');?>
                </option>
                <option value="2">
                    <?php echo __( 'Title');?>
                </option>
                <option value="3" selected="selected">
                    <?php echo __( 'Content');?>
                </option>
            </select>
        </label>
            <input type="text" name="searchC">
            <button type="submit" class="attached button" ><i class="icon-search"></i>
            </button>
        </form>
        <br>
        <br>
    </div>
</div>
    <h1 style="margin: 0">
    <a href="tickets.php?user_id=<?php echo $_REQUEST['user_id'];?>"><i class="refresh icon-refresh"></i>
        <?php echo __('Tasks'); ?>
    </a>
    </h1>

    <h1 style="float: right; margin-bottom:10px  ">
    <a href="tickets.php?closedTask=<?php echo $_REQUEST['user_id'];?>"><i class="refresh icon-refresh"></i>
        <?php echo __('Closed Tasks'); ?>
    </a>
    </h1>
    <table id="ticketTable" width="100%" border="0" cellspacing="0" cellpadding="0">
    <caption><?php echo  $results; ?></caption>
    <thead>
        <tr>
            <th width="75">
                <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('Task #'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="120">
                <a  title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
            </th>
            <th width="120">
                <a  title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
            </th>
            <th width="110">
                <a title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
            </th>
            <th width="150">
                <a  title="Sort By Topic"><?php echo __('From'); ?>&nbsp;</a>
            </th>
            <th width="120">
                <a  title="Sort By 'To User'"><?php echo __('To'); ?>&nbsp;</a>
            </th>
            <th width="130">
                <?php echo __('Last Response'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        function encrypt_decrypt($action, $string,$secret_key = "supersecret_key") {
            $output = false;
            $encrypt_method = "AES-256-CBC";
            $secret_iv = 'randomString#12231'; // change this to one more secure
            $key = hash('sha256', $secret_key);
        
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ( $action == 'encrypt' ) {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } else if( $action == 'decrypt' ) {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
        }
 $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name` 

 FROM `ost_task` 
 LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
 LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
 LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
 LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
 LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
 WHERE (`ost_thread_collaborator`.`user_id`=".$_REQUEST['user_id']." OR `ost_thread_collaborator`.`team_id` IN(SELECT `team_id` FROM `ost_team_user_member` WHERE `user_id`=".$_REQUEST['user_id'].") ) AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NULL ORDER BY `updated` DESC ";
 //echo $GetUsersStaffTicketsQ ;
 if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
     while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamName) = db_fetch_row($GetUsersStaffTickets_Res)) {
        ?>
                <tr id="<?php echo $Number; ?>">
                    <td> <a class="preview" href="tasks.php?id=<?php echo encrypt_decrypt('encrypt',$ID,$secret_key);; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                    <td><?php echo $Created; ?></td>
                    <td><?php echo $Subject; ?></td>
                    
                    <td><span><?php echo  $Dep; ?></span></td>
                    <td style="text-align: right;"><span><?php echo $FromStaff; ?></span></td>
                    <?php if($ToStaff != null ){?>
                    <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                    <?php }else{  ?>
                        <td><span class="truncate"><?php echo $TeamName; ?></span></td>
                    <?php } ?>
                    <td><span class="truncate"><?php echo $Update; ?></span></td>
                   
                </tr>
        <?php
            }}

        ?>
    </tbody>
</table>
<?php
}
elseif(isset($_REQUEST['closedTask'])&& ($_REQUEST['a'] !== 'Asearch')){
    $_SESSION['userAsagent_name']="user";
    $_SESSION['userAsagent_pass']="mabco1234";
    $results = ucfirst($status) . ' ' . __('Tasks');
    $negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting
    echo  "closed Tasks Count : " . $closedTasks[0] ;
    ?>
    <div id="basic_search">
    <div style="min-height:60px;" >
   
        <!--<p><?php //echo __('Select the starting time and period for the system activity graph');?></p>-->
        <form method="post" action="tickets.php?a=Asearch" >
        <?php echo csrf_token(); ?>
        <label>
            <?php echo __( 'Search By');?>:
            <select name="gets" id="gets">
                <option value="0" >
                    <?php echo __( 'ID');?>
                </option>
                <option value="1">
                    <?php echo __( 'Number');?>
                </option>
                <option value="2">
                    <?php echo __( 'Title');?>
                </option>
                <option value="3" selected="selected">
                    <?php echo __( 'Content');?>
                </option>
            </select>
        </label>
            <input type="text" name="searchC">
            <button type="submit" class="attached button" ><i class="icon-search"></i>
            </button>
        </form>
        <br>
        <br>
    </div>
</div>
    <h1 style="margin: 0">
    <a href="tickets.php?user_id=<?php echo $_REQUEST['closedTask'];?>"><i class="refresh icon-refresh"></i>
        <?php echo __('Tasks'); ?>
    </a>
    </h1>
    <h1 style="float: right; margin-bottom:10px  ">
    <a href="tickets.php?closedTask=1"><i class="refresh icon-refresh"></i>
        <?php echo __('Closed Tasks'); ?>
    </a>
    </h1>
    <table id="ticketTable" width="100%" border="0" cellspacing="0" cellpadding="0">
    <caption><?php echo  $results; ?></caption>
    <thead>
        <tr>
            <th width="75">
                <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('Task #'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="120">
                <a  title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
            </th>
            <th width="120">
                <a  title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
            </th>
            <th width="110">
                <a title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
            </th>
            <th width="150">
                <a  title="Sort By Topic"><?php echo __('From'); ?>&nbsp;</a>
            </th>
            <th width="120">
                <a  title="Sort By 'To User'"><?php echo __('To'); ?>&nbsp;</a>
            </th>
            <th width="130">
                <?php echo __('Last Response'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        function encrypt_decrypt($action, $string,$secret_key = "supersecret_key") {
            $output = false;
            $encrypt_method = "AES-256-CBC";
            $secret_iv = 'randomString#12231'; // change this to one more secure
            $key = hash('sha256', $secret_key);
        
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ( $action == 'encrypt' ) {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } else if( $action == 'decrypt' ) {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
        }
 $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
 FROM `ost_task` 
 LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
 LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
 LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
 LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
 LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
 WHERE `ost_thread_collaborator`.`user_id`=".$_REQUEST['closedTask']."  AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NOT NULL 
 UNION
 SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
 FROM `ost_task` 
 LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
 LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
 LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
 LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
 LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
 LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
 WHERE  `ost_thread_collaborator`.`team_id` IN(SELECT `team_id` FROM `ost_team_user_member` WHERE `user_id`=".$_REQUEST['closedTask'].")  AND `ost_thread`.`object_type`='A' AND `ost_task`.`closed` IS NOT NULL 
 ORDER BY `updated` DESC ";
// echo $GetUsersStaffTicketsQ ;
 if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
     while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamName) = db_fetch_row($GetUsersStaffTickets_Res)) {
        ?>
                <tr id="<?php echo $Number; ?>">
                    <td> <a class="preview" href="tasks.php?id=<?php echo encrypt_decrypt('encrypt',$ID,$secret_key);; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                    <td><?php echo $Created; ?></td>
                    <td><?php echo $Subject; ?></td>
                    
                    <td><span><?php echo  $Dep; ?></span></td>
                    <td style="text-align: right;"><span><?php echo $FromStaff; ?></span></td>
                    <?php if($ToStaff != null ){?>
                    <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                    <?php }else{  ?>
                        <td><span class="truncate"><?php echo $TeamName; ?></span></td>
                    <?php } ?>
                    <td><span class="truncate"><?php echo $Update; ?></span></td>
                   
                </tr>
        <?php
            }}

        ?>
    </tbody>
</table>
<?php
}
else if ($_REQUEST['a'] == 'Asearch') {
    
    if (empty($_POST["searchC"])) {
       ?> <div id="msg_error"><?php echo "Please enter data to search for"; ?></div><?php 
die();}
else 
{
$_SESSION['userAsagent_name']="user";
$_SESSION['userAsagent_pass']="mabco1234";
$results = ucfirst($status) . ' ' . __('Tasks');
$negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting
?>
<div id="basic_search">
    <div style="min-height:60px;" >
   
        <!--<p><?php //echo __('Select the starting time and period for the system activity graph');?></p>-->
        <form method="post" action="tickets.php?a=Asearch" >
        <?php echo csrf_token(); ?>
        <label>
            <?php echo __( 'Search By');?>:
            <select name="gets" id="gets">
                <option value="0" >
                    <?php echo __( 'ID');?>
                </option>
                <option value="1">
                    <?php echo __( 'Number');?>
                </option>
                <option value="2">
                    <?php echo __( 'Title');?>
                </option>
                <option value="3" selected="selected">
                    <?php echo __( 'Content');?>
                </option>
            </select>
        </label>
            <input type="text" name="searchC">
            <button type="submit" class="attached button" ><i class="icon-search"></i>
            </button>
        </form>
        <br>
        <br>
    </div>
</div>
<h1 style="margin: 0">
<a href="tickets.php?user_id=<?php echo $_REQUEST['user_id'];?>"><i class="refresh icon-refresh"></i>
    <?php echo __('Tasks'); ?>
</a>
</h1>

<h1 style="float: right; margin-bottom:10px  ">
<a href="tickets.php?closedTask=<?php echo $_REQUEST['user_id'];?>"><i class="refresh icon-refresh"></i>
    <?php echo __('Closed Tasks'); ?>
</a>
</h1>
<table id="ticketTable" width="100%" border="0" cellspacing="0" cellpadding="0">
<caption><?php echo  $results; ?></caption>
<thead>
    <tr>
        <th width="75">
            <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('Task #'); ?>&nbsp;<i class="icon-sort"></i></a>
        </th>
        <th width="120">
            <a  title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;</a>
        </th>
        <th width="120">
            <a  title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;</a>
        </th>
        <th width="110">
            <a title="Sort By Department"><?php echo __('Department'); ?>&nbsp;</a>
        </th>
        <th width="150">
            <a  title="Sort By Topic"><?php echo __('From'); ?>&nbsp;</a>
        </th>
        <th width="120">
            <a  title="Sort By 'To User'"><?php echo __('To'); ?>&nbsp;</a>
        </th>
        <th width="130">
            <?php echo __('Last Response'); ?>
        </th>
    </tr>
</thead>
<tbody>
    <?php
    function encrypt_decrypt($action, $string,$secret_key = "supersecret_key") {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_iv = 'randomString#12231'; // change this to one more secure
        $key = hash('sha256', $secret_key);
    
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    if ($_POST['gets'] == 0  ) {
      
        $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
        FROM `ost_task` 
        LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
        LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
        LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
        LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
        LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
        
        WHERE `ost_task`.`id`=". $_POST["searchC"]." order by ost_task.created desc" ;
    }
    elseif ($_POST['gets'] == 1  ){
        $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
        FROM `ost_task` 
        LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
        LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
        LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
        LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
        LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
        where  `ost_task`.`number` =". $_POST["searchC"] ." order by ost_task.created desc";
    }
    elseif ($_POST['gets'] == 2  ){
        $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
        FROM `ost_task` 
        LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
        LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
        LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
        LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
        LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
        WHERE `ost_task__cdata`.`title` LIKE  '%". $_POST["searchC"]."%' order by ost_task.created  desc";
    }
    elseif ($_POST['gets'] == 3  ){
        $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
        FROM `ost_task` 
        LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
         INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
        LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
        LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
        LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
        LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
        LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
        INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id`
        WHERE `ost_thread_entry`.`body` LIKE '%". $_POST["searchC"]."%'  AND `ost_thread`.`object_type`='A'   order by ost_task.created  desc";
}
else 
{
    $GetUsersStaffTicketsQ = "SELECT `ost_task`.`id`,`ost_task`.`number`,`ost_task`.`created`,`ost_task__cdata`.`title` ,`ost_department`.`name` ,CONCAT(from_id.`firstname`, ' ', from_id.`lastname`),CONCAT(to_id.`firstname`, ' ', to_id.`lastname`),`ost_task`.`updated` ,`ost_team`.`name`
    FROM `ost_task` 
    LEFT JOIN `ost_task__cdata` ON `ost_task__cdata`.`task_id`=`ost_task`.`id`
     INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_task`.`id`
    LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
    LEFT JOIN `ost_staff` as from_id ON from_id.`staff_id`=`ost_task`.`assignor_id`
    LEFT JOIN `ost_staff` as to_id ON to_id.`staff_id`=`ost_task`.`staff_id`
    LEFT JOIN `ost_department` ON `ost_department`.`id`=`ost_task`.`dept_id`
    LEFT JOIN `ost_team` ON `ost_team`.`team_id`=`ost_task`.`team_id` 
    INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id`
    WHERE `ost_thread_entry`.`body` LIKE '%". $_POST["searchC"]."%'  AND `ost_thread`.`object_type`='A'  order by ost_task.created  desc ";
}

if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
 while (list($ID, $Number,$Created, $Subject, $Dep, $FromStaff, $ToStaff, $Update,$TeamName) = db_fetch_row($GetUsersStaffTickets_Res)) {
    ?>
            <tr id="<?php echo $Number; ?>">
                <td> <a class="preview" href="tasks.php?id=<?php echo encrypt_decrypt('encrypt',$ID,$secret_key);; ?>" data-preview="#tasks/<?php echo $ID; ?>/preview"><?php echo $Number; ?></a></td>
                <td><?php echo $Created; ?></td>
                <td><?php echo $Subject; ?></td>
                
                <td><span><?php echo  $Dep; ?></span></td>
                <td style="text-align: right;"><span><?php echo $FromStaff; ?></span></td>
                <?php if($ToStaff != null ){?>
                <td><span class="truncate"><?php echo $ToStaff; ?></span></td>
                <?php }else{  ?>
                    <td><span class="truncate"><?php echo $TeamName; ?></span></td>
                <?php } ?>
                <td><span class="truncate"><?php echo $Update; ?></span></td>
               
            </tr>
    <?php
        }}

    ?>
</tbody>
</table>
<?php
}
    }
else{
$org_tickets = $thisclient->canSeeOrgTickets();
if ($settings['keywords']) {
    // Don't show stat counts for searches
    $openTickets = $closedTickets = -1;
} elseif ($settings['topic_id']) {
    $openTickets = $thisclient->getNumTopicTicketsInState(
        $settings['topic_id'],
        'open',
        $org_tickets
    );
    $closedTickets = $thisclient->getNumTopicTicketsInState(
        $settings['topic_id'],
        'closed',
        $org_tickets
    );
} else {
    $openTickets = $thisclient->getNumOpenTickets($org_tickets);
    $closedTickets = $thisclient->getNumClosedTickets($org_tickets);
}
$refuseTickets = $thisclient->getNumRefusedTickets($org_tickets);
$tickets = Ticket::objects();

$qs = array();
$status = null;

$sortOptions = array(
    'id' => 'number', 'subject' => 'cdata__subject',
    'status' => 'status__name', 'dept' => 'dept__name', 'date' => 'created', 'to_user' => 'to_user_id', 'topic' => 'topic_id'
);
$orderWays = array('DESC' => '-', 'ASC' => '');
//Sorting options...
$order_by = $order = null;
$sort = ($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])]) ? strtolower($_REQUEST['sort']) : 'date';
if ($sort && $sortOptions[$sort])
    $order_by = $sortOptions[$sort];

$order_by = $order_by ?: $sortOptions['date'];
if ($_REQUEST['order'] && !is_null($orderWays[strtoupper($_REQUEST['order'])]))
    $order = $orderWays[strtoupper($_REQUEST['order'])];
else
    $order = $orderWays['DESC'];

$x = $sort . '_sort';
$$x = ' class="' . strtolower($_REQUEST['order'] ?: 'desc') . '" ';

$basic_filter = Ticket::objects();
if ($settings['topic_id']) {
    $basic_filter = $basic_filter->filter(array('topic_id' => $settings['topic_id']));
}

if ($settings['status'])
    $status = strtolower($settings['status']);
switch ($status) {
    case 'refuse':
        $results_type = ($status == 'Refuse') ? __('Refuse Tickets') : __('Refuse Tickets');
        $basic_filter->filter(array('status__state' => $status));
        break;
    default:
        $status = 'open';
    case 'open':
    case 'closed':
        $results_type = ($status == 'closed') ? __('Closed Tickets') : __('Open Tickets');
        $basic_filter->filter(array('status__state' => $status));
        break;
}

// Add visibility constraints — use a union query to use multiple indexes,
// use UNION without "ALL" (false as second parameter to union()) to imply
// unique values
$visibility = $basic_filter->copy()
    ->values_flat('ticket_id')
    ->filter(array('user_id' => $thisclient->getId()));

// Add visibility of Tickets where the User is a Collaborator if enabled
if ($cfg->collaboratorTicketsVisibility())
    $visibility = $visibility
        ->union(
            $basic_filter->copy()
                ->values_flat('ticket_id')
                ->filter(array('thread__collaborators__user_id' => $thisclient->getId())),
            false
        );

if ($thisclient->canSeeOrgTickets()) {
    $visibility = $visibility->union(
        $basic_filter->copy()->values_flat('ticket_id')
            ->filter(array('user__org_id' => $thisclient->getOrgId())),
        false
    );
}

// Perform basic search
if ($settings['keywords']) {
    $q = trim($settings['keywords']);
    // if (is_numeric($q)) {
    //     $tickets->filter(array('number__startswith' => $q));
    // } elseif (strlen($q) > 2) { //Deep search!
        // Use the search engine to perform the search
        $tickets = $ost->searcher->find($q, $tickets);
    }


$tickets->distinct('ticket_id');

TicketForm::ensureDynamicDataView();

$total = $visibility->count();
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$pageNav = new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;' . Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('tickets.php', $qs);
$tickets->filter(array('ticket_id__in' => $visibility));
$pageNav->paginate($tickets);

$showing = $total ? $pageNav->showing() : "";
if (!$results_type) {
    $results_type = ucfirst($status) . ' ' . __('Tickets');
}
$showing .= ($status) ? (' ' . $results_type) : ' ' . __('All Tickets');
if ($search)
    $showing = __('Search Results') . ": $showing";

$negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting

$tickets->order_by($order . $order_by);
$tickets->values(
    'ticket_id',
    'number',
    'created',
    'isanswered',
    'source',
    'status_id',
    'status__state',
    'status__name',
    'cdata__subject',
    'dept_id',
    'dept__name',
    'dept__ispublic',
    'user__default_email__address',
    'user_id',
    'to_user_id',
    'staff_id',
    'topic_id'
);

?>
<div class="search well">
    <div class="flush-left">
        <form action="tickets.php" method="get" id="ticketSearchForm">
            <input type="hidden" name="a" value="search">
            <input type="text" name="keywords" size="30" value="<?php echo Format::htmlchars($settings['keywords']); ?>">
            <input type="submit" value="<?php echo __('Search'); ?>">
            <div class="pull-right">
                <?php echo __('Help Topic'); ?>:
                <select name="topic_id" class="nowarn" onchange="javascript: this.form.submit(); ">
                    <option value="">&mdash; <?php echo __('All Help Topics'); ?> &mdash;</option>
                    <?php
                    foreach (Topic::getHelpTopics(true) as $id => $name) {
                        $count = $thisclient->getNumTopicTickets($id, $org_tickets);
                        if ($count == 0)
                            continue;
                    ?>
                        <option value="<?php echo $id; ?>" i <?php if ($settings['topic_id'] == $id) echo 'selected="selected"'; ?>><?php echo sprintf(
                                                                                                                                        '%s (%d)',
                                                                                                                                        Format::htmlchars($name),
                                                                                                                                        $thisclient->getNumTopicTickets($id)
                                                                                                                                    ); ?></option>
                    <?php } ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($settings['keywords'] || $settings['topic_id'] || $_REQUEST['sort']) { ?>
        <div style="margin-top:10px"><strong><a href="?clear" style="color:#777"><i class="icon-remove-circle"></i> <?php echo __('Clear all filters and sort'); ?></a></strong></div>
    <?php } ?>

</div>


<h1 style="margin:10px 0">
    <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
        <?php echo __('Tickets'); ?>
    </a>

    <div class="pull-right states">
        <small>
            <?php if ($openTickets) { ?>
                <i class="icon-file-alt"></i>
                <a class="state <?php if ($status == 'open') echo 'active'; ?>" href="?<?php echo Http::build_query(array('a' => 'search', 'status' => 'open')); ?>">
                    <?php echo _P('ticket-status', 'Open');
                    if ($openTickets > 0) echo sprintf(' (%d)', $openTickets); ?>
                </a>
                <?php if ($closedTickets) { ?>
                    &nbsp;
                    <span style="color:lightgray">|</span>
                <?php }
            }
            if ($closedTickets) { ?>
                &nbsp;
                <i class="icon-file-text"></i>
                <a class="state <?php if ($status == 'closed') echo 'active'; ?>" href="?<?php echo Http::build_query(array('a' => 'search', 'status' => 'closed')); ?>">
                    <?php echo __('Closed');
                    if ($closedTickets > 0) echo sprintf(' (%d)', $closedTickets); ?>
                </a>
            <?php } ?>
            <?php if ($refuseTickets) { ?>
                    &nbsp;
                    <span style="color:lightgray">|</span>
                <?php } ?>
            <?php if ($refuseTickets) {?>
                <i class="icon-file-alt"></i>
                <a class="state <?php if ($status == 'Refuse') echo 'active'; ?>" href="?a=search&status=<?php echo 'Refuse'; ?>">
                    <?php echo _P('ticket-status', 'Refuse');
                    if ($refuseTickets > 0) echo sprintf(' (%d)', $refuseTickets); ?>
                </a>
                
                <?php } ?>
            
        </small>
    </div>
</h1>
<table id="ticketTable" width="100%" border="0" cellspacing="0" cellpadding="0">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="75">
                <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('Ticket #'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="75">
                <a href="tickets.php?sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="75">
                <a href="tickets.php?sort=status&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Status"><?php echo __('Status'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="110">
                <a href="tickets.php?sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="150">
                <a href="tickets.php?sort=topic&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Topic"><?php echo __('Topic'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=to_user&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By 'To User'"><?php echo __('To User'); ?>&nbsp;<i class="icon-sort"></i></a>
            </th>
            <th width="170">
                <?php echo __('Body'); ?>
            </th>
            <th width="130">
                <?php echo __('Last Response'); ?>
            </th>
            <th width="130">
                <?php echo __('Current Agent(s)'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $subject_field = TicketForm::objects()->one()->getField('subject');
        $defaultDept = Dept::getDefaultDeptName(); //Default public dept.
        if ($tickets->exists(true)) {
            foreach ($tickets as $T) {
                $dept = $T['dept__ispublic']
                    ? Dept::getLocalById($T['dept_id'], 'name', $T['dept__name'])
                    : $defaultDept;
                $subject = $subject_field->display(
                    $subject_field->to_php($T['cdata__subject']) ?: $T['cdata__subject']
                );
                $status = TicketStatus::getLocalById($T['status_id'], 'value', $T['status__name']);
                $ToUser = $T['to_user_id'] ? User::getNameById($T['to_user_id']) : "No Direct User";

                // if ($T['staff_id'] && $T['staff_id'] > 0) {
                //     $ToAgent = Staff::lookup($T['staff_id']);
                // } else {
                //     $ToAgent = Topic::getTopicAgentById($T['topic_id']);
                //     $ToAgent = $ToAgent != "" ? $ToAgent : "No Direct Agent";
                // }
                
                $TicketCurrentStep = 0;
                $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $T['ticket_id'] . ";";

                if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                    $Res = db_fetch_row($GetTicketCurrentStep_Res);
                    $TicketCurrentStep = $Res[0];
                }

                $CurrentAgents = "None";
                $HelpTopicID = $T['topic_id'];

                if ($TicketCurrentStep > 0) {
                    $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";
                    
                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                        $Res = db_fetch_row($GetCurrentAgents_Res);
                        $CurrentAgents = $Res[0];
                    }
                    
                } else if ($TicketCurrentStep == -1) {
                    if (array_key_exists('closed', $T) && $T['closed'] !== '') {
                        $CurrentAgents = "Ticket Closed";
                    } else {
                        if (Ticket::GetClosedDate($T['ticket_id']) && Ticket::GetClosedDate($T['ticket_id']) !== '')
                            $CurrentAgents = "Ticket Closed";
                        else
                            $CurrentAgents = "Ticket To Be Closed";
                    }
                }

                if ($CurrentAgents == '') {
                    $CurrentAgents = 'Contact IT!';
                }

                $LastThreadEntry = Thread::getLastThreadEntry($T['ticket_id']);
                $Body = "Not available!";
                $LastResponse = "";

                if ($LastThreadEntry != null) {
                    $Body = $LastThreadEntry[0];
                    $Body = strip_tags($Body, '<br /><br/><br>');
                    $Body = substr($Body, 0, 100);
                    $LastResponse = $LastThreadEntry[1];
                }

                if (strpos($Body, '<br />') == true || strpos($Body, '<br>') == true || strpos($Body, '<br/>') == true)
                    $Body = substr($Body, 0, strpos($Body, "<br />"));


                $Topic = $T['topic_id'] ? Topic::getTopicName($T['topic_id']) : "No Topic";

                if (false) // XXX: Reimplement attachment count support
                    $subject .= '  &nbsp;&nbsp;<span class="Icon file"></span>';

                $ticketNumber = $T['number'];
                if ($T['isanswered'] && !strcasecmp($T['status__state'], 'open')) {
                    $subject = "<b>$subject</b>";
                    $ticketNumber = "<b>$ticketNumber</b>";
                }
                $thisclient->getId() != $T['user_id'] ? $isCollab = true : $isCollab = false;
                //Yaseen
                    $ticket_id=$T['ticket_id'];
                    $user_id = $thisclient->getId();
                $Getsteps = "SELECT count(*) from ost_ticket t inner join ost_help_topic_flow h on t.ticket_id=h.ticket_id where current_step = step_number and h.user_id= ".$user_id." and t.ticket_id=".$ticket_id."";
                if (($Getstepres = db_query($Getsteps)) && db_num_rows($Getstepres)) {
                    $Res = db_fetch_row($Getstepres);
                    $step = $Res[0];
                }
           
                if ($step>0)
                {
                    $trstyle="background-color: lightcoral ;";
                }
                else 
                {
                    $trstyle="";
                }
                
        ?>
                <tr style="<?php echo $trstyle?>" id="<?php echo $T['ticket_id']; ?>">
                    <td>
                        <a class="Icon <?php echo strtolower($T['source']); ?>Ticket" title="<?php echo $T['user__default_email__address']; ?>" href="tickets.php?id=<?php echo $T['ticket_id']; ?>"><?php echo $ticketNumber; ?></a>
                    </td>
                    <td><?php echo Format::date($T['created']); ?></td>
                    <td><?php echo $status; ?></td>
                    <td >
                        <?php if ($isCollab) { ?>
                            <div style="max-height: 1.2em; max-width: 320px;" class="link truncate" href="tickets.php?id=<?php echo $T['ticket_id']; ?>"><i class="icon-group"></i> <?php echo $subject; ?></div>
                        <?php } else { ?>
                            <div style="max-height: 1.2em; max-width: 320px;" class="link truncate" href="tickets.php?id=<?php echo $T['ticket_id']; ?>"><?php echo $subject; ?></div>
                        <?php } ?>
                    </td>
                    <td><span><?php echo $dept; ?></span></td>
                    <td style="text-align: right;"><span><?php echo $Topic; ?></span></td>
                    <td><span class="truncate"><?php echo $ToUser; ?></span></td>
                    <td><span class="truncate"><?php echo $Body; ?></span></td>
                    <td><span class="truncate"><?php echo $LastResponse; ?></span></td>
                    <td><span><?php echo $CurrentAgents; ?></span></td>
                </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="6">' . __('Your query did not match any records') . '</td></tr>';
        }
        ?>
    </tbody>
</table>
<?php
if ($total) {
    echo '<div>&nbsp;' . __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;</div>';
}
}
?>