<?php
/*********************************************************************
    reports.php

    Staff's Reports - basic stats...etc.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

if (!$thisstaff || !$thisstaff->isManager()) {
    // header("location:directory.php?error=access_denied");
}

require_once INCLUDE_DIR . 'class.reports.php';


$nav->setTabActive('cal');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.dashboard" />', "$('#content').data('tipNamespace', 'dashboard.dashboard');");

require(STAFFINC_DIR.'header.inc.php');
// require_once(STAFFINC_DIR.'reports.inc.php');
if($_REQUEST['cal']=="mine") {
  $GetStaffTask = "SELECT `number`,`created`,`duedate` FROM `ost_task` WHERE `duedate`>= CURDATE() AND `staff_id`=". $thisstaff->getid() ." AND `closed` IS NULL";

  $z = array();
  $x = array();
  $y = array();
  $Date = array();
  $Day = array();
  //  echo $GetAllStaff;
  if (($GetStaffTask_Res = db_query($GetStaffTask)) && db_num_rows($GetStaffTask_Res)) {
      while (list($TaskNumber, $TaskCreatedDate,$TaskDueDate) = db_fetch_row($GetStaffTask_Res)) {
          array_push($z, $TaskNumber);
          array_push($x, $TaskCreatedDate);
          array_push($y, $TaskDueDate);
      }
  }
  $sql="UPDATE `ost_staff_calendar` SET `Staff_id`=".$thisstaff->getid()." WHERE `id`=1";
  if(db_query($sql)){
  $timestamp = strtotime(date("Y-m-d"));

  $daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
  // echo date('Y-m-d', strtotime('+1 month'));
  $daysRemainingnext = (int)date('t',  strtotime('next month'));
  $sqlgetrecurrentTask="WITH RECURSIVE offdays as(
    SELECT 
    LAST_DAY(CURDATE()-INTERVAL 1 MONTH) + INTERVAL 1 DAY AS `Date`,
    DAYNAME(LAST_DAY(CURDATE()-INTERVAL 1 MONTH) + INTERVAL 1 DAY) AS `DayName`
    UNION ALL
    SELECT `Date` + INTERVAL 1 DAY, DAYNAME(`Date` + INTERVAL 1 DAY) 
    FROM offdays WHERE `DATE` < LAST_DAY(CURDATE()) 
    ) SELECT * FROM offdays where DAYNAME(DATE) = (SELECT DAYNAME(`start_recurring_date`)  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK')";
    
    if (($sqlgetrecurrentTask_Res = db_query($sqlgetrecurrentTask)) && db_num_rows($sqlgetrecurrentTask_Res)) {
      while (list($TaskDate, $TaskDateDay) = db_fetch_row($sqlgetrecurrentTask_Res)) {
          array_push($Date, $TaskDate);
          array_push($Day, $TaskDateDay);
         
      }
  }
  $sqlgetcurrentnext="WITH RECURSIVE offdays as(
    SELECT 
    LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."'-INTERVAL 1 MONTH) + INTERVAL 1 DAY AS `Date`,
    DAYNAME(LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."'-INTERVAL 1 MONTH) + INTERVAL 1 DAY) AS `DayName`
    UNION ALL
    SELECT `Date` + INTERVAL 1 DAY, DAYNAME(`Date` + INTERVAL 1 DAY) 
    FROM offdays WHERE `DATE` < LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."')) SELECT * FROM offdays where DAYNAME(DATE) = (SELECT DAYNAME(`start_recurring_date`)  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK')";
    if (($sqlgetcurrentnext_Res = db_query($sqlgetcurrentnext)) && db_num_rows($sqlgetcurrentnext_Res)) {
      while (list($TaskDate, $TaskDateDay) = db_fetch_row($sqlgetcurrentnext_Res)) {
          array_push($Date, $TaskDate);
          array_push($Day, $TaskDateDay);
         
      }
  }
  $test="SELECT `rt_id`,`rt_title` AS title , `start_recurring_date`  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id` FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK'";
  if(($test_Res = db_query($test)) && db_num_rows($test_Res)) {
    $Res = db_fetch_row($test_Res);
  }
  $tst11="SELECT  `rt_id`,`rt_title`  , `start_recurring_date`  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'DAY'";
  if(($tst11_Res = db_query($tst11)) && db_num_rows($tst11_Res)) {
    $Res1 = db_fetch_row($tst11_Res);
  }

  $test12="SELECT `rt_id`,`rt_title`,`start_recurring_date` FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id` FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'MONTH'";
  $delete="DELETE FROM `cal_temp`";
  db_query($delete);
  // print_r($Res[0]);
  // print_r($Date);
  foreach ($Date as $index ){
    // echo $index."<br>";
    $inser="INSERT INTO `cal_temp`(`rt_id`, `rt_title`, `start_recurring_date`, `end_recurring_date`) VALUES (".$Res[0].",'".$Res[1]."',DATE_SUB( cast(concat('".$index."', ' ', DATE_FORMAT('".$Res[2]."','%H:%i:%s')) as datetime), INTERVAL 2 HOUR),cast(concat('".$index."', ' ', DATE_FORMAT('".$Res[2]."','%H:%i:%s')) as datetime))";
    // echo $inser;
    db_query($inser);
    }
    for ($x = 0; $x <= $daysRemaining+$daysRemainingnext; $x++){
      $inser="INSERT INTO `cal_temp`(`rt_id`, `rt_title`, `start_recurring_date`, `end_recurring_date`) VALUES (".$Res1[0].",'".$Res1[1]."',DATE_ADD(DATE_SUB( cast(concat(CURDATE(), ' ', DATE_FORMAT('".$Res1[2]."','%H:%i:%s')) as datetime), INTERVAL 2 HOUR), INTERVAL ".$x." DAY),DATE_ADD(cast(concat(CURDATE(), ' ', DATE_FORMAT('".$Res1[2]."','%H:%i:%s')) as datetime), INTERVAL ".$x." DAY))";
      // echo $inser;
      db_query($inser);
    }
    //   print_r($y);
  
  // $sql = "UPDATE `ost_staff` SET `calendar_staff`= ".$thisstaff->getid()." WHERE `staff_id`= ".$thisstaff->getid();
  
    ?>
    <button type="button"><a href="https://task.mabcoonline.com:444/create-event-calendar-with-jquery-php-and-mysql">View My Calendar</a></button><?php
  }
 

}
elseif($_REQUEST['cal']=="stafff"){
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
  <form method="post" action="cal.php?cal=stafff">
  <?php echo csrf_token(); ?>
  <h3>Choose an agent To See calender</h3>
  <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
  <option disabled selected value> -- select an agent -- </option>
              <?php foreach ($staffname as $index => $item) { ?>
                  <option value="<?php echo $staffid[$index] ?>"><?php echo $item; ?></option>
              <?php } ?>
          </select>
  
  <hr><br>
  <button class="green button action-button muted" type="submit">
                          <?php echo __('submit'); ?>
                      </button>
                      <i class="help-tip icon-question-sign" href="#"></i>

      

  
  
  </form>
  <?php
  if(isset($_POST['ddlViewBy'])){
    $Date = array();
  $Day = array();
    $sql="UPDATE `ost_staff_calendar` SET `Staff_id`=".$_POST['ddlViewBy']." WHERE `id`=1";
    // $sql = "UPDATE `ost_staff` SET `calendar_staff`= ".$_POST['ddlViewBy']." WHERE `staff_id`= ".$thisstaff->getid();
    if(db_query($sql)){

      $timestamp = strtotime(date("Y-m-d"));

  $daysRemaining = (int)date('t', $timestamp) - (int)date('j', $timestamp);
  // echo date('Y-m-d', strtotime('+1 month'));
  $daysRemainingnext = (int)date('t',  strtotime('next month'));
  $sqlgetrecurrentTask="WITH RECURSIVE offdays as(
    SELECT 
    LAST_DAY(CURDATE()-INTERVAL 1 MONTH) + INTERVAL 1 DAY AS `Date`,
    DAYNAME(LAST_DAY(CURDATE()-INTERVAL 1 MONTH) + INTERVAL 1 DAY) AS `DayName`
    UNION ALL
    SELECT `Date` + INTERVAL 1 DAY, DAYNAME(`Date` + INTERVAL 1 DAY) 
    FROM offdays WHERE `DATE` < LAST_DAY(CURDATE()) 
    ) SELECT * FROM offdays where DAYNAME(DATE) = (SELECT DAYNAME(`start_recurring_date`)  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK')";
    
    if (($sqlgetrecurrentTask_Res = db_query($sqlgetrecurrentTask)) && db_num_rows($sqlgetrecurrentTask_Res)) {
      while (list($TaskDate, $TaskDateDay) = db_fetch_row($sqlgetrecurrentTask_Res)) {
          array_push($Date, $TaskDate);
          array_push($Day, $TaskDateDay);
         
      }
  }
  $sqlgetcurrentnext="WITH RECURSIVE offdays as(
    SELECT 
    LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."'-INTERVAL 1 MONTH) + INTERVAL 1 DAY AS `Date`,
    DAYNAME(LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."'-INTERVAL 1 MONTH) + INTERVAL 1 DAY) AS `DayName`
    UNION ALL
    SELECT `Date` + INTERVAL 1 DAY, DAYNAME(`Date` + INTERVAL 1 DAY) 
    FROM offdays WHERE `DATE` < LAST_DAY('".date('Y-m-d', strtotime('+1 month'))."')) SELECT * FROM offdays where DAYNAME(DATE) = (SELECT DAYNAME(`start_recurring_date`)  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK')";
    if (($sqlgetcurrentnext_Res = db_query($sqlgetcurrentnext)) && db_num_rows($sqlgetcurrentnext_Res)) {
      while (list($TaskDate, $TaskDateDay) = db_fetch_row($sqlgetcurrentnext_Res)) {
          array_push($Date, $TaskDate);
          array_push($Day, $TaskDateDay);
         
      }
  }
  $test="SELECT `rt_id`,`rt_title` AS title , `start_recurring_date`  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id` FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'WEEK'";
  if(($test_Res = db_query($test)) && db_num_rows($test_Res)) {
    $Res = db_fetch_row($test_Res);
  }
  $tst11="SELECT  `rt_id`,`rt_title`  , `start_recurring_date`  FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id`  FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'DAY'";
  if(($tst11_Res = db_query($tst11)) && db_num_rows($tst11_Res)) {
    $Res1 = db_fetch_row($tst11_Res);
  }

  $test12="SELECT `rt_id`,`rt_title`,`start_recurring_date` FROM `ost_recurring_tasks` WHERE `rt_staff_id`=(SELECT `Staff_id` FROM `ost_staff_calendar` WHERE `id`=1) AND `rt_period` LIKE 'MONTH'";
  $delete="DELETE FROM `cal_temp`";
  db_query($delete);
  // print_r($Res[0]);
  // print_r($Date);
  foreach ($Date as $index ){
    // echo $index."<br>";
    $inser="INSERT INTO `cal_temp`(`rt_id`, `rt_title`, `start_recurring_date`, `end_recurring_date`) VALUES (".$Res[0].",'".$Res[1]."',DATE_SUB( cast(concat('".$index."', ' ', DATE_FORMAT('".$Res[2]."','%H:%i:%s')) as datetime), INTERVAL 2 HOUR),cast(concat('".$index."', ' ', DATE_FORMAT('".$Res[2]."','%H:%i:%s')) as datetime))";
    // echo $inser;
    db_query($inser);
    }
    for ($x = 0; $x <= $daysRemaining+$daysRemainingnext; $x++){
      $inser="INSERT INTO `cal_temp`(`rt_id`, `rt_title`, `start_recurring_date`, `end_recurring_date`) VALUES (".$Res1[0].",'".$Res1[1]."',DATE_ADD(DATE_SUB( cast(concat(CURDATE(), ' ', DATE_FORMAT('".$Res1[2]."','%H:%i:%s')) as datetime), INTERVAL 2 HOUR), INTERVAL ".$x." DAY),DATE_ADD(cast(concat(CURDATE(), ' ', DATE_FORMAT('".$Res1[2]."','%H:%i:%s')) as datetime), INTERVAL ".$x." DAY))";
      // echo $inser;
      db_query($inser);
    }
      ?>
      
      <button type="button"><a href="https://task.mabcoonline.com:444/create-event-calendar-with-jquery-php-and-mysql">View Calendar</a></button><?php
    }
  }
}



include(STAFFINC_DIR.'footer.inc.php');


?>
<?php
class Recurrent_Task {
  // Properties
  public $id;
  public $title;
  public $Start_date;
  public $End_date;

  // Methods
  function set_id($id) {
    $this->id = $id;
  }
  function get_id() {
    return $this->id;
  }
  function set_title($title) {
    $this->title = $title;
  }
  function get_title() {
    return $this->title;
  }

  function set_Start_date($Start_date) {
    $this->Start_date = $Start_date;
  }
  function get_Start_date() {
    return $this->Start_date;
  }

  function set_End_date($End_date) {
    $this->End_date = $End_date;
  }
  function get_End_date() {
    return $this->id;
  }
}
?>
