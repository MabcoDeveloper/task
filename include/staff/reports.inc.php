<?php
if(isset($_GET["data"] ) )
{
    $data = $_GET["data"];
    if($data=="agentreport"){
      require('staff.inc.php');
      if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
          $report = new OverviewReport($_POST['start_date']);
          $report_e = new OverviewReport($_POST['end_date']);
      } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
          $report = new OverviewReport($_POST['start_date']);
          $report_e = new OverviewReport($_POST['end_date']);
      } else {
          $report = new OverviewReport($_POST['start_date']);
          $report_e = new OverviewReport($_POST['end_date']);
      }
      
      // $plots = $report->getPlotData();
      function secondsToTime($seconds)
      {
          $dtF = new \DateTime('@0');
          $dtT = new \DateTime("@$seconds");
          return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
      }
      
      
      
      ?>
      <script type="text/javascript" src="js/raphael-min.js?a5d898b"></script>
      <script type="text/javascript" src="js/g.raphael.js?a5d898b"></script>
      <script type="text/javascript" src="js/g.line-min.js?a5d898b"></script>
      <script type="text/javascript" src="js/g.dot-min.js?a5d898b"></script>
      <script type="text/javascript" src="js/dashboard.inc.js?a5d898b"></script>
      
      <link rel="stylesheet" type="text/css" href="css/dashboard.css?a5d898b" />
      
      
      <?php
      
      // if agent not manager
      if (!$thisstaff || !$thisstaff->isManager()) {
          // header("location:directory.php?error=access_denied");
          //get agent dep name 
          $z = array();
          $x = array();
          $types = array();
          $depts = $thisstaff->getDepts();
      
          $GetAllStaff = "SELECT `id`,`name` FROM `ost_department` WHERE `id` IN(SELECT `dept_id` FROM `ost_staff` WHERE `staff_id` = " . $thisstaff->getid() . ") ";
          //  echo $GetAllStaff;
          if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
              while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                  array_push($z, $RecurringTaskTitle);
                  array_push($x, $RecurringTaskID);
              }
          }
          $variable = $x[0];
          //choose date 
      ?>
          <form method="post"  action="reports.php?data=agentreport">
              <div id="basic_search">
                  <div style="min-height:25px;">
      
                      <?php echo csrf_token(); ?>
                      <label>
                          <?php echo __('From Date'); ?>:
                          <input type="text" class="dp input-medium search-query" name="start_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                          echo Format::htmlchars($report->getStartDate());
                                                                                                                                                          ?>" />
                      </label>
      
                      <label>
                          <?php echo __('To Date'); ?>:
                          <input type="text" class="dp input-medium search-query" name="end_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                          echo Format::htmlchars($report_e->getStartDate());
                                                                                                                                                          ?>" />
                      </label>
                      <!-- <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
                          <?php foreach ($z as $index => $item) { ?>
                              <option value="<?php echo $x[$index] . ":" . $item; ?>"><?php echo $item; ?></option>
                          <?php } ?>
                      </select> -->
                      <button class="green button action-button muted" type="submit" name="submit">
                          <?php echo __('submit'); ?>
                      </button>
                      <i class="help-tip icon-question-sign" href="#"></i>
      
                  </div>
      
              </div>
              <div class="clear"></div>
              <?php
              if ($_POST['start_date'] ) {
                // echo $_POST['start_date'];
                // echo "<br>";
                // echo $_POST['end_date'];
                  $FromDate = explode('.', str_replace('T', ' ', $_POST['start_date']))[0];
                  $ToDate = explode('.', str_replace('T', ' ', $_POST['end_date']))[0];
                  $your_FromDate = strtotime("1 day", strtotime($FromDate));
                  $new_FromDate = date("Y-m-d", $your_FromDate);
                  $your_ToDate = strtotime("1 day", strtotime($ToDate));
                  $new_ToDate = date("Y-m-d", $your_ToDate);

                  $FromDate = explode(' ', $new_FromDate)[0] . " " . "00:00:00";
                  $ToDate = explode(' ', $new_ToDate)[0] . " " . "21:00:00";
                  
                //   echo $FromDate;
                //   echo "<br>";
                //   echo  $ToDate ;
              }
              if (!isset($_POST['start_date'])) {
                  $FromDate = "2020/01/01 00:00:00";
                  $ToDate = "2020/12/01 21:00:00";
              }
              if (!isset($_POST['ddlViewBy'])) {
                  $variable = $x[0];
                  $text = $z[0];
              } else {
      
                  $variable = explode(':', $_POST['ddlViewBy'])[0];
                  $text = explode(':', $_POST['ddlViewBy'])[1];
              }
              $temp = array();
              $result = array();
              $result_second = array();
              $result_all = array();
      
      
      
              if (
                  !$thisstaff
                  || (!is_object($thisstaff) && !($thisstaff = Staff::lookup($thisstaff)))
                  || !$thisstaff->isStaff()
              )
                  return null;
              $sql1 = 'SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
              $agents = array();
              if (($res = db_query($sql1)) && db_num_rows($res)) {
                  while (list($id) = db_fetch_row($res))
                      $agents[] = (int) $id;
              }
              $where = array('(`ost_task`.staff_id IN(' . implode(',', db_input($agents))
                  . ')' . sprintf(' AND `ost_task`.flags  != 0 ', TaskModel::ISOPEN)
                  . ')  OR ' . '(`ost_task`.`assignor_id` IN(' . implode(',', db_input($agents))
                  . ')' . sprintf(' AND `ost_task`.flags  != 0 ', TaskModel::ISOPEN)
                  . ') ');
              $where2 = '';
      
      
              if (($teams = $thisstaff->getTeams()))
                  $where[] = ' ( `ost_task`.team_id IN(' . implode(',', db_input(array_filter($teams)))
                      . ') AND '
                      . sprintf('`ost_task`.flags!= 0 ', TaskModel::ISOPEN)
                      . ')';
      
              if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
              $where[] = '`ost_task`.dept_id IN(' . implode(',', db_input($depts)) . ') ';
      
              $where = implode(' OR ', $where);
              if ($where) $where = 'AND ( ' . $where . ' ) ';
      
      
      
              $GetAllStaff = "SELECT AVG (x.result) from (SELECT 
          
              (case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) 
               THEN
                  
              (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60)
               + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`)))
              
               ELSE
              ((( TIMESTAMPDIFF(day,`ost_task`.`created`, `ost_thread_entry`.`created`)*8) +
               MOD(HOUR(TIMEDIFF(`ost_task`.`created`, `ost_thread_entry`.`created`)), 24) )*60 )+  (MINUTE(TIMEDIFF(`ost_task`.`created`, `ost_thread_entry`.`created`)))
               
                  
               END) as result
          FROM `ost_thread_entry` 
          INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
          INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id`
          
          WHERE   `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= " . $thisstaff->getid() . " AND `ost_task`.`closed` IS NOT NUll
          And `ost_task`.`closed` between '" . $FromDate . " ' and  ' " . $ToDate . " '
          ORDER BY `ost_task`.`id` , `ost_thread_entry`.`created` ASC ) as x";
              // echo $GetAllStaff;
              if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
      
                  $Final_result = db_fetch_row($GetRecurringTasks_Res);
                  // while (list($AgentID, $ResponceDate,$TaskCreateDat,$id,$Old_agent_Id,$PPDate) = db_fetch_row($GetRecurringTasks_Res))
                  //  {
                  // array_push($z, $id);
                  //         array_push($x, $Taskcreated);
      
      
                  // 
                  // }
      
      
              }
      
              //Average Self Response
              ?>
              <table  style="border: 1px solid black; border-collapse: collapse;"  class="dashboard-stats table">
                  <tbody>
                      <tr>
      
                          <th style="color:#0492D0;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Average Self Response");
      
                                                                              ?>
      
                          </th>
      
      <th></th>
      <th></th>
      <th></th>
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
      
                  <tbody>
                      <tr>
      
                          <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("");
      
                                                                              ?>
      
                          </th>
      
                          <th style=" text-align:center;"  <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Minutes");
      
                                                                              ?>
      
                          </th>
                          <th style=" text-align:center;"  <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Hour");
      
                                                                              ?>
      
                          </th>
                          <th style=" text-align:center;" <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("D/H/M/S");
      
                                                                              ?>
      
                          </th>
      
      
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
                  <tbody>
                      <tr>
                          <th>
      
                              <tb></tb>
                          </th>
                      </tr>
                  </tbody>
                  <tbody>
                      <tr>
      
                          <th style="text-align:center; font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff;text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars("Total Average");
      
                                                                      ?>
      
                          </th>
                          <th   style="text-align:center; font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff;text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($Final_result[0]));
      
                                                                                                                                                                              ?>
      
                          </th>
                          <th  style="text-align:center; font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff;text-align:center; " <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round(($Final_result[0]) / 60));
      
                                                                                                                                                                              ?>
      
                          </th>
                          <th  style="text-align:center; font-weight: bold; background-color:#F4F4F4;border-right: 2px solid #ffffff;text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(secondsToTime(round($Final_result[0]) * 60));
      
                                                                                                                                                                          ?>
      
                          </th>
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
              </table>
      
      
      
      
      
      
              <div class="clear">
      
                  <br>
                  <br>
              </div>
      
              <?php
              //Average Response With Other Agents
              $result = array();
              $idd = array();
              $name = array();
              $sql1 = 'SELECT DISTINCT `staff_id` FROM `ost_staff` WHERE `dept_id` IN(' . implode(',', db_input($thisstaff->getDepts())) . ') ';
      
              $agents = array();
              if (($res = db_query($sql1)) && db_num_rows($res)) {
                  while (list($id) = db_fetch_row($res))
                      $agents[] = (int) $id;
              }
              $where = array('(`ost_task`.staff_id IN(' . implode(',', db_input($agents))
                  . ')' . sprintf(' AND `ost_task`.flags  != 0 ', TaskModel::ISOPEN)
                  . ')  OR ' . '(`ost_task`.`assignor_id` IN(' . implode(',', db_input($agents))
                  . ')' . sprintf(' AND `ost_task`.flags  != 0 ', TaskModel::ISOPEN)
                  . ') ');
              $where2 = '';
      
      
              if (($teams = $thisstaff->getTeams()))
                  $where[] = ' ( `ost_task`.team_id IN(' . implode(',', db_input(array_filter($teams)))
                      . ') AND '
                      . sprintf('`ost_task`.flags!= 0 ', TaskModel::ISOPEN)
                      . ')';
      
              if (!$thisstaff->showAssignedOnly() && ($depts = $thisstaff->getDepts())) //Staff with limited access just see Assigned tasks.
                  $where[] = '`ost_task`.dept_id IN(' . implode(',', db_input($depts)) . ') ';
      
              $where = implode(' OR ', $where);
              if ($where) $where = 'AND ( ' . $where . ' ) ';
      
      
      
              $GetAllStaffAvg = "SELECT 
      
               AVG((case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) 
                THEN
                   
               (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60)
                + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`)))
               
               
                   
                END)) as result ,`ost_thread_entry`.`agent_p_resp_id` as idd , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as name
                
           FROM `ost_thread_entry` 
           INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
           INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id`
           INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_thread_entry`.`agent_p_resp_id`
           WHERE   `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= " . $thisstaff->getid() . " AND `ost_task`.`closed` IS NOT NUll
           And `ost_task`.`closed` between '" . $FromDate . " ' and  ' " . $ToDate . " '
           GROUP BY `ost_thread_entry`.`agent_p_resp_id`
      ORDER BY `ost_thread_entry`.`created` ASC ";
                 // echo  $GetAllStaffAvg;
              if (($GetAllStaffAvg_Res = db_query($GetAllStaffAvg)) && db_num_rows($GetAllStaffAvg_Res)) {
                  while (list($result_, $idd_, $name_) = db_fetch_row($GetAllStaffAvg_Res)) {
                      array_push($result, $result_);
                      array_push($idd, $idd_);
                      array_push($name, $name_);
                  }
              }
      
      
      
              ?>
              <table style="border: 1px solid black; border-collapse: collapse;" class="dashboard-stats table">
              <tbody>
                      <tr>
      
                      <th style="color:#0492D0;" <?php echo 'width="30%" class="flush-left"'; ?>><?php echo Format::htmlchars("Average Response With Other Agents");
      
                                                                              ?>
      
                          </th>
      
                          <th></th>
      <th></th>
      <th></th>
      <th></th>
                          
                      </tr>
                  </tbody>
      
      
                  <tbody>
                      <tr>
      
                         
      
                                                
      
                          
                          <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Agent Name");
      
                                                                                                          ?>
      
                          </th>
                          <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Minutes");
      
                                                                                                          ?>
      
                          </th>
                          <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Hour");
      
                                                                                                          ?>
      
                          </th>
                          <th style=" text-align:center;" <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("D/H/M/S");
      
                                                                                                          ?>
      
                          </th>
      
      
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
                  <tbody>
                      <tr>
                          <th>
      
                              <tb></tb>
                          </th>
                      </tr>
                  </tbody>
                  <tbody>
                      <?php foreach ($result as $index => $item) {
                          
                      ?>
                          <tr>
                              
                              <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($name[$index]);
      
                                                                                                                                                                                                                      ?>
      
                              </th>
                              <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item));
      
                                                                                                                                                                                                                      ?>
      
                              </th>
                              <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item / 60));
      
                                                                                                                                                                                                                      ?>
      
                              </th>
                              <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo 'width="70%" class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(secondsToTime(round($item) * 60));
      
                                                                                                                                                                                                                      ?>
      
                              </th>
                              <?php
                              
                              ?>
                          </tr>
                      <?php
                      } ?>
                  </tbody>
      
              </table>
      
              <ul class="clean tabs">
              </ul>
          </form>
      <?php
      
      } else {
          // echo $_POST['ddlViewBy'];
          // if agent is  manager
          $deppp = array();
          //get dep name
          $Getdep = "SELECT `name` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid();
          //  echo $GetAllStaff;
          if (($Getdep_Res = db_query($Getdep)) && db_num_rows($Getdep_Res)) {
              while (list($Result) = db_fetch_row($Getdep_Res)) {
                  array_push($deppp, $Result);
              }
          }
      
      
          //get dep agents name 
      
          $agentsId = array();
          $agentsName = array();
      
      
          $GetAllStaffName = "SELECT `staff_id`,  CONCAT(`firstname`, ' ', `lastname`)  FROM `ost_staff` WHERE `dept_id` IN (SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid() . ") ";
          //  echo $GetAllStaffName;
          if (($GetAllStaffName_Res = db_query($GetAllStaffName)) && db_num_rows($GetAllStaffName_Res)) {
              while (list($agentsId_, $agentsName_) = db_fetch_row($GetAllStaffName_Res)) {
                  array_push($agentsId, $agentsId_);
                  array_push($agentsName, $agentsName_);
              }
          }
      
      ?>
          <form method="post" action="reports.php?data=agentreport">
              <div id="basic_search">
                  <div style="min-height:25px;">
      
                      <?php echo csrf_token(); ?>
                      <label>
                          <?php echo __('From Date'); ?>:
                          <input type="text" class="dp input-medium search-query" name="start_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                          echo Format::htmlchars($report->getStartDate());
                                                                                                                                                          ?>" />
                      </label>
      
                      <label>
                          <?php echo __('To Date'); ?>:
                          <input type="text" class="dp input-medium search-query" name="end_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                          echo Format::htmlchars($report_e->getStartDate());
                                                                                                                                                          ?>" />
                      </label>
                      <div class="clear">
      <h3>Choose To See  Details of Agents</h3>
              <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
              <option disabled selected value> -- select an agent -- </option>
                          <?php foreach ($agentsName as $index => $item) { ?>
                              <option value="<?php echo $agentsId[$index]. ":" .$item; ?>"><?php echo $item; ?></option>
                          <?php } ?>
                      </select>
              </div>
              <hr><br>
                      <button class="green button action-button muted" type="submit">
                          <?php echo __('submit'); ?>
                      </button>
                      <i class="help-tip icon-question-sign" href="#"></i>
      
                  </div>
      
              </div>
              
              <div class="clear">
                  <br>
                  <br>
              </div>
              <?php
              if ($_POST['start_date']) {
      
                  $FromDate = explode('.', str_replace('T', ' ', $_POST['start_date']))[0];
                  $ToDate = explode('.', str_replace('T', ' ', $_POST['end_date']))[0];
                  $your_FromDate = strtotime("1 day", strtotime($FromDate));
                  $new_FromDate = date("Y-m-d", $your_FromDate);
                  $your_ToDate = strtotime("1 day", strtotime($ToDate));
                  $new_ToDate = date("Y-m-d", $your_ToDate);

                  $FromDate = explode(' ', $new_FromDate)[0] . " " . "00:00:00";
                  $ToDate = explode(' ', $new_ToDate)[0] . " " . "21:00:00";
                  // echo $FromDate;
                  // echo "<br>";
                  // echo $ToDate;
              }
              if (!isset($_POST['start_date'])) {
                  $FromDate = "2020/01/01 00:00:00";
                  $ToDate = "2020/12/01 21:00:00";
              }
              //get all agents reports
              $z = array();
              $x = array();
              $res = array();
              $GetAllStaff = "SELECT `staff_id`, CONCAT(`firstname`, ' ', `lastname`),(SELECT CalcAvg ( `staff_id` , '" . $FromDate . "' ,'" . $ToDate . "' )) as s FROM `ost_staff` WHERE `dept_id` IN (SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid() . ") ";
            //    echo $GetAllStaff;
              if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
                  while (list($RecurringTaskID, $RecurringTaskTitle, $RecurringTaskAVG) = db_fetch_row($GetRecurringTasks_Res)) {
                      array_push($z, $RecurringTaskTitle);
                      array_push($x, $RecurringTaskID);
                      array_push($res, $RecurringTaskAVG);
                  }
              }
      
      
      
      
              if (isset($_POST['ddlViewBy'])) {
                  //get agent to  agents reports
                  //Average To One  Agent Maneger
                  $variable = explode(':', $_POST['ddlViewBy'])[0];
                  $text = explode(':', $_POST['ddlViewBy'])[1];
                  $zz = array();
                  $xx = array();
                  $resx = array();
                  $depidfrom=array();
                  $sumavg=array();
                  $depname=array();
                  $depidto=array();
                  $GetAllStaffx = "SELECT 
                  
                  AVG((case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) 
                   THEN
                      
                  (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60)
                   + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`)))
                  
                  
                      
                   END)) as result ,`ost_thread_entry`.`agent_p_resp_id` as idd , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as name , (SELECT CalcAvgAgent(idd)) as depid
                   
              FROM `ost_thread_entry` 
              INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
              INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id`
              INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_thread_entry`.`agent_p_resp_id`
              WHERE   `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= ".$variable ." AND `ost_task`.`closed` IS NOT NUll And `ost_task`.`closed` between '" . $FromDate . " ' and  ' " . $ToDate . " '
              GROUP BY `ost_thread_entry`.`agent_p_resp_id`
              ORDER BY `depid`  ASC ";
                    // echo $GetAllStaffx;
                  if (($GetAllStaffx_Res = db_query($GetAllStaffx)) && db_num_rows($GetAllStaffx_Res)) {
                      while (list($resx_,$zz_, $xx_,$depidfrom_ ) = db_fetch_row($GetAllStaffx_Res)) {
                          array_push($zz, $zz_);
                          array_push($xx, $xx_);
                          array_push($resx, $resx_);
                          array_push($depidfrom, $depidfrom_);
                          
                      }
                  }
      
      
                  $GetAllStaffdep = "SELECT  SUM(x.result) , `ost_department`.`name`, (SELECT CalcAvgAgent(x.idd))
                  FROM
                 
                 (SELECT AVG((case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) THEN (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60) + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`))) END)) as result ,`ost_thread_entry`.`agent_p_resp_id` as idd , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as name FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_thread_entry`.`agent_p_resp_id` WHERE `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= ".$variable. " AND `ost_task`.`closed` IS NOT NUll  And `ost_task`.`closed` between '".$FromDate." ' and ' ".$ToDate." ' GROUP BY `ost_thread_entry`.`agent_p_resp_id` ORDER BY `ost_thread_entry`.`created` ASC)  as x
                 INNER JOIN `ost_department` ON `ost_department`.`id`=(SELECT CalcAvgAgent(x.idd))
                 GROUP BY (SELECT CalcAvgAgent(x.idd))  ORDER BY (SELECT CalcAvgAgent(x.idd))";
                //  echo $GetAllStaffdep;
                  if (($GetAllStaffdep_Res = db_query($GetAllStaffdep)) && db_num_rows($GetAllStaffdep_Res)) {
                      while (list($sumavg_,$depname_,$depidto_) = db_fetch_row($GetAllStaffdep_Res)) {
                          array_push($sumavg, $sumavg_);
                          array_push($depname, $depname_);
                          array_push($depidto, $depidto_);
                          
                      }
                  }
                  
              ?>
      
                  <table style="border: 1px solid black; border-collapse: collapse;"  class="dashboard-stats table">
                  
                  <tbody>
                      <tr>
      
                          <th style="color:#0492D0;"  <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("Average To ".$text);
      
                                                                              ?>
      
                          </th>
      
                          <th></th>
      <th></th>
      <th></th>
      <th></th>
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
                      <tbody>
                          <tr>
      
                              <th <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("From");
      
                                                                                  ?>
      
                              </th>
                              <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Agent Name");
      
                                                                                                              ?>
      
                              </th>
                              <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Minutes");
      
                                                                                                              ?>
      
                              </th>
                              <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Hour");
      
                                                                                                              ?>
      
                              </th>
                              <th style=" text-align:center;" <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("D/H/M/S");
      
                                                                                                              ?>
      
                              </th>
      
      
                              <?php
                              ?>
                          </tr>
                      </tbody>
      
                      <tbody>
                          <tr>
                              <th>
      
                                  <tb></tb>
                              </th>
                          </tr>
                      </tbody>
                      <tbody>
                          <?php
                          $i=0;
                          foreach ($resx as $index => $item) {
                                     $depvalue=$depidfrom[$index]; ?>
                              <tr>
                                  <th <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($text);
      
                                                                              ?>
      
                                  </th>
                                  <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($xx[$index]);
      
                                                                                                                                                                                                                          ?>
      
                                  </th>
                                  <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item));
      
                                                                                                                                                                                                                          ?>
      
                                  </th>
                                  <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item / 60) );
      
                                                                                                                                                                                                                          ?>
      
                                  </th>
                                  <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(secondsToTime(round($item) * 60 ));
      
                                                                                                                                                                                                                          ?>
      
                                  </th>
                                  <?php
                                  
                                   if( $index == count($resx)-1 ){?>
                                  <tr align="center">
                                      <!-- <th></th>
                                      <th></th> -->
      
                                      <th  style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars("Average  Department ".$depname[$i]);?>
                                      
                                      </th>
                                      <th></th>
                                      <th style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($sumavg[$i]);?>
                                      </th>
                                      <th></th>
                                      <th></th>
                                      
                                   </tr>
                                      <?php
                                      
                                      $i+=1;                           
                                                                  }
                                  elseif( $depidfrom[$index+1] != $depidto[$i] ){?>
      
                                  <tr align="center">
      
                                      <!-- <th ></th>  -->
                                      
      
                                      <th  style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars("Average  Department ".$depname[$i]);?>
                                      
                                      </th>
                                      <th></th>
                                      <th style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($sumavg[$i]);?>
                                      
                                                                                                                                                                                                                                                          
                                      
                                      </th>
                                      <th></th>
                                      <th></th>
                                  </tr>
                                      <?php
                                      
                                      $i+=1;                           
                                  }
                                  else{continue;}
                                                                  
      
      
                                                                     
                                  ?>
      
      <?php
                                  
      
                                                                     
                                  ?>
                              </tr>
                          <?php
                          } ?>
                      </tbody>
      
                  </table>
      
                  <ul class="clean tabs">
                  </ul>
          </form>
      
      
      <?php
      
              } else {
                  //Average To All Department Agents
      
      ?>
          <table style="border: 1px solid black; border-collapse: collapse;" class="dashboard-stats table">
      
      
          <tbody>
                      <tr>
      
                          <th style="color:#0492D0;" <?php echo 'width="30%" class="flush-left"'; ?>><?php echo Format::htmlchars("Average To All Department Agents");
      
                                                                              ?>
      
                          </th>
      
                          <th></th>
      <th></th>
      <th></th>
      <th></th>
                          <?php
                          ?>
                      </tr>
                  </tbody>
      
      
              <tbody>
                  <tr>
      
                     
                      <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Agent Name");
      
                                                                                                      ?>
      
                      </th>
                      <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Minutes");
      
                                                                                                      ?>
      
                      </th>
                      <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Hour");
      
                                                                                                      ?>
      
                      </th>
                      <th style=" text-align:center;" <?php echo 'width="70%" class="flush-left"'; ?>><?php echo Format::htmlchars("D/H/M/S");
      
                                                                                                      ?>
      
                      </th>
      
      
                      <?php
                      ?>
                  </tr>
              </tbody>
      
              <tbody>
                  <tr>
                      <th>
      
                          <tb></tb>
                      </th>
                  </tr>
              </tbody>
              <tbody>
                  <?php foreach ($z as $index => $item) {
                  ?>
                      <tr>
                         
                          <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($item);
      
                                                                                                                                                                                                                  ?>
      
                          </th>
                          <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($res[$index]) );
      
                                                                                                                                                                                                                  ?>
      
                          </th>
                          <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($res[$index] / 60) );
      
                                                                                                                                                                                                                  ?>
      
                          </th>
                          <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(secondsToTime(round($res[$index])*60) );
      
                                                                                                                                                                                                                  ?>
      
                          </th>
                          <?php
                          ?>
                      </tr>
                  <?php
                  } ?>
              </tbody>
      
          </table>
      <?php
              }
              // if (
      // !$thisstaff
      // || (!is_object($thisstaff) && !($thisstaff = Staff::lookup($thisstaff)))
      // || !$thisstaff->isStaff()
      //
      // return null;
      
      ?>
      
      
      <div class="clear"></div>
      
      
      <ul class="clean tabs">
      </ul>
      </form>
      <?php
      } 
    }
    elseif($data=="manegerreport"){
      require('staff.inc.php');
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $report = new OverviewReport($_POST['start_date']);
    $report_e = new OverviewReport($_POST['end_date']);
} elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $report = new OverviewReport($_POST['start_date']);
    $report_e = new OverviewReport($_POST['end_date']);
} else {
    $report = new OverviewReport($_POST['start_date']);
    $report_e = new OverviewReport($_POST['end_date']);
}

// $plots = $report->getPlotData();
function secondsToTime($seconds)
{
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}



?>
<script type="text/javascript" src="js/raphael-min.js?a5d898b"></script>
<script type="text/javascript" src="js/g.raphael.js?a5d898b"></script>
<script type="text/javascript" src="js/g.line-min.js?a5d898b"></script>
<script type="text/javascript" src="js/g.dot-min.js?a5d898b"></script>
<script type="text/javascript" src="js/dashboard.inc.js?a5d898b"></script>

<link rel="stylesheet" type="text/css" href="css/dashboard.css?a5d898b" />
<?php


    // echo $_POST['ddlViewBy'];
    // if agent is  manager
    $deppp = array();
    //get dep name
    $Getdep = "SELECT `name` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid();
    //  echo $GetAllStaff;
    if (($Getdep_Res = db_query($Getdep)) && db_num_rows($Getdep_Res)) {
        while (list($Result) = db_fetch_row($Getdep_Res)) {
            array_push($deppp, $Result);
        }
    }


    //get dep agents name 

    $agentsId = array();
    $agentsName = array();


    $GetAllStaffName = "SELECT `staff_id`,  CONCAT(`firstname`, ' ', `lastname`)  FROM `ost_staff` WHERE `dept_id` IN (SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid() . ") ";
    //  echo $GetAllStaffName;
    if (($GetAllStaffName_Res = db_query($GetAllStaffName)) && db_num_rows($GetAllStaffName_Res)) {
        while (list($agentsId_, $agentsName_) = db_fetch_row($GetAllStaffName_Res)) {
            array_push($agentsId, $agentsId_);
            array_push($agentsName, $agentsName_);
        }
    }

?>
    <form method="post" action="reports.php?data=manegerreport">
        <div id="basic_search">
            <div style="min-height:25px;">

                <?php echo csrf_token(); ?>
                <label>
                    <?php echo __('From Date'); ?>:
                    <input type="text" class="dp input-medium search-query" name="start_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                    echo Format::htmlchars($report->getStartDate());
                                                                                                                                                    ?>" />
                </label>

                <label>
                    <?php echo __('To Date'); ?>:
                    <input type="text" class="dp input-medium search-query" name="end_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                    echo Format::htmlchars($report_e->getStartDate());
                                                                                                                                                    ?>" />
                </label>
                <div class="clear">
<h3>Choose To See  Details of Agents</h3>
        <select class="modal-body" id="ddlViewBy" name="ddlViewBy">
        <option disabled selected value> -- select an agent -- </option>
                    <?php foreach ($agentsName as $index => $item) { ?>
                        <option value="<?php echo $agentsId[$index]. ":" .$item; ?>"><?php echo $item; ?></option>
                    <?php } ?>
                </select>
        </div>
        <hr>
        <br>
                <button class="green button action-button muted" type="submit">
                    <?php echo __('submit'); ?>
                </button>
                <i class="help-tip icon-question-sign" href="#"></i>

            </div>

        </div>
        
        <div class="clear">
            <br>
            <br>
        </div>
        <?php
        if ($_POST['start_date']) {

            $FromDate = explode('.', str_replace('T', ' ', $_POST['start_date']))[0];
            $ToDate = explode('.', str_replace('T', ' ', $_POST['end_date']))[0];
            $your_FromDate = strtotime("1 day", strtotime($FromDate));
            $new_FromDate = date("Y-m-d", $your_FromDate);
            $your_ToDate = strtotime("1 day", strtotime($ToDate));
            $new_ToDate = date("Y-m-d", $your_ToDate);

            $FromDate = explode(' ', $new_FromDate)[0] . " " . "00:00:00";
            $ToDate = explode(' ', $new_ToDate)[0] . " " . "21:00:00";
            // echo $FromDate;
            // echo "<br>";
            // echo $ToDate;
        }
        
        //get all agents reports
        $z = array();
        $x = array();
        $res = array();
        $GetAllStaff = "SELECT `staff_id`, CONCAT(`firstname`, ' ', `lastname`),(SELECT CalcAvg ( `staff_id` , '" . $FromDate . "' ,'" . $ToDate . "' , (SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid() . "))) as s FROM `ost_staff` WHERE `dept_id` IN (SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getid() . ") ";
        //  echo $GetAllStaff;
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle, $RecurringTaskAVG) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
                array_push($res, $RecurringTaskAVG);
            }
        }




        if (isset($_POST['ddlViewBy'])) {
            //get agent to  agents reports
            //Average To One  Agent Maneger
            if (!isset($_POST['start_date'])) {
                $FromDate = "2020/01/01 00:00:00";
                $ToDate = "2020/12/01 21:00:00";
            }
            $variable = explode(':', $_POST['ddlViewBy'])[0];
            $text = explode(':', $_POST['ddlViewBy'])[1];
            $zz = array();
            $xx = array();
            $resx = array();
            $depidfrom=array();
            $sumavg=array();
            $depname=array();
            $depidto=array();
            $GetAllStaffx = "SELECT 
            
            SUM((case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) 
             THEN
                
            (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60)
             + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`)))
            
            
                
             END)) as result ,`ost_thread_entry`.`agent_p_resp_id` as idd , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as name , (SELECT CalcAvgAgent(idd)) as depid
             
        FROM `ost_thread_entry` 
        INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
        INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id`
        INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_thread_entry`.`agent_p_resp_id`
        WHERE   `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= " .  $variable . " AND `ost_task`.`closed` IS NOT NUll
        And `ost_task`.`closed` between '" . $FromDate . " ' and  ' " . $ToDate . " '
        GROUP BY `ost_thread_entry`.`agent_p_resp_id`
        ORDER BY `depid`  ASC";
            //   echo $GetAllStaffx;
            if (($GetAllStaffx_Res = db_query($GetAllStaffx)) && db_num_rows($GetAllStaffx_Res)) {
                while (list($resx_,$zz_, $xx_,$depidfrom_ ) = db_fetch_row($GetAllStaffx_Res)) {
                    array_push($zz, $zz_);
                    array_push($xx, $xx_);
                    array_push($resx, $resx_);
                    array_push($depidfrom, $depidfrom_);
                    
                }
            }


            $GetAllStaffdep = "SELECT  SUM(x.result) , `ost_department`.`name`, (SELECT CalcAvgAgent(x.idd))
            FROM
           
           (SELECT SUM((case when (`ost_thread_entry`.`p_resp_date` IS NOT NULL) THEN (((TIMESTAMPDIFF(day,`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`) *8) + MOD(HOUR(TIMEDIFF(`ost_thread_entry`.`p_resp_date`,`ost_thread_entry`.`created`)), 24) ) *60) + (MINUTE(TIMEDIFF(`ost_thread_entry`.`p_resp_date`, `ost_thread_entry`.`created`))) END)) as result ,`ost_thread_entry`.`agent_p_resp_id` as idd , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as name FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` INNER JOIN `ost_task` ON `ost_thread`.`object_id`=`ost_task`.`id` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_thread_entry`.`agent_p_resp_id` WHERE `ost_thread`.`object_type`='A' AND `ost_thread_entry`.`staff_id`= ".$variable.
           " AND `ost_task`.`closed` IS NOT NUll And `ost_task`.`closed` between '".$FromDate." ' and ' ".$ToDate." ' GROUP BY `ost_thread_entry`.`agent_p_resp_id` ORDER BY `ost_thread_entry`.`created` ASC)  as x
           INNER JOIN `ost_department` ON `ost_department`.`id`=(SELECT CalcAvgAgent(x.idd))
           GROUP BY (SELECT CalcAvgAgent(x.idd))   ORDER BY (SELECT CalcAvgAgent(x.idd)) ";
        //    echo $GetAllStaffdep;
            if (($GetAllStaffdep_Res = db_query($GetAllStaffdep)) && db_num_rows($GetAllStaffdep_Res)) {
                while (list($sumavg_,$depname_,$depidto_) = db_fetch_row($GetAllStaffdep_Res)) {
                    array_push($sumavg, $sumavg_);
                    array_push($depname, $depname_);
                    array_push($depidto, $depidto_);
                    
                }
            }
            
        ?>

            <table style="border: 1px solid black; border-collapse: collapse;" class="dashboard-stats table">
            
            <tbody>
                <tr>

                    <th style="color:#0492D0;"  <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("Average To ".$text);

                                                                        ?>

                    </th>

                    <th></th>
<th></th>
<th></th>
<th></th>
<th></th>
                    <?php
                    ?>
                </tr>
            </tbody>

                <tbody>
                    <tr>

                        <th <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("From");

                                                                            ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Agent Name");

                                                                                                        ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Minutes");

                                                                                                        ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Hour");

                                                                                                        ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("D/H/M/S");

                                                                                                        ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("Rate");

?>

</th>


                        <?php
                        ?>
                    </tr>
                </tbody>

                <tbody>
                    <tr>
                        <th>

                            <tb></tb>
                        </th>
                    </tr>
                </tbody>
                <tbody>
                    <?php
                    $i=0;
                    foreach ($resx as $index => $item) {
                               $depvalue=$depidfrom[$index]; ?>
                        <tr>
                            <th <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($text);

                                                                        ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($xx[$index]);

                                                                                                                                                                                                                    ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item));

                                                                                                                                                                                                                    ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item / 60) );

                                                                                                                                                                                                                    ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(secondsToTime(round($item) * 60 ));

                                                                                                                                                                                                                    ?>

                            </th>

                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(round($item)/array_sum($sumavg));

                                                                                                                                                                                                                    ?>

                            </th>
                            <?php
                            if( $index == count($resx)-1 ){?>
                                <tr align="center">
                                    <!-- <th></th> -->
                                   
    
                                    <th  style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars("Average  Department ".$depname[$i]);?>
                                    
                                    </th>
                                    <th></th>
                                    <th style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($sumavg[$i]);?>
                                    </th>
                                    <th></th>
                                      <th></th>
                                 </tr>
                                    <?php
                                    
                                    $i+=1;                           
                                                                }
                                elseif( $depidfrom[$index+1] != $depidto[$i] ){?>
    
                                <tr align="center">
    
                                    <!-- <th ></th>  -->
                                    
    
                                    <th  style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars("Average  Department ".$depname[$i]);?>
                                    
                                    </th>
                                    <th></th>
                                    <th style="font-weight: bold; background-color:#ADD8E6; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($sumavg[$i]);?>
                                    
                                                                                                                                                                                                                                                        
                                    
                                    </th>
                                    <th></th>
                                      <th></th>
                                </tr>
                                    <?php
                                    
                                    $i+=1;                           
                                }
                                else{continue;}
                            ?>
                        </tr>
                    <?php
                    } ?>
                </tbody>
                
                <tbody>
<tr>
    <th>
        
    </th>
</tr>
                <tr>

                    <th style="color:#0492D0;"  <?php echo 'width="20%" class="flush-left"'; ?>><?php echo Format::htmlchars("Total time spend");

                                                                        ?>

                    </th>
<th></th>
                    <th style="font-weight: bold; background-color:#FFE4C4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars(array_sum($sumavg) );

?>

</th>
<th></th>
<th></th>
<th></th>
                    <?php
                    ?>
                </tr>
            </tbody>
            </table>

            <ul class="clean tabs">
            </ul>
    </form>


<?php

        } ?>


<div class="clear"></div>


<ul class="clean tabs">
</ul>
</form><?php
    }




    elseif($data=="adminreport"){
// echo "ddddddddddddddd";
        require('staff.inc.php');
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            $report = new OverviewReport($_POST['start_date']);
            $report_e = new OverviewReport($_POST['end_date']);
        } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $report = new OverviewReport($_POST['start_date']);
            $report_e = new OverviewReport($_POST['end_date']);
        } else {
            $report = new OverviewReport($_POST['start_date']);
            $report_e = new OverviewReport($_POST['end_date']);
        }
        
        // $plots = $report->getPlotData();
        function secondsToTime($seconds)
        {
            $dtF = new \DateTime('@0');
            $dtT = new \DateTime("@$seconds");
            return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
        }
        
        
        
        ?>
        <script type="text/javascript" src="js/raphael-min.js?a5d898b"></script>
        <script type="text/javascript" src="js/g.raphael.js?a5d898b"></script>
        <script type="text/javascript" src="js/g.line-min.js?a5d898b"></script>
        <script type="text/javascript" src="js/g.dot-min.js?a5d898b"></script>
        <script type="text/javascript" src="js/dashboard.inc.js?a5d898b"></script>
        
        <link rel="stylesheet" type="text/css" href="css/dashboard.css?a5d898b" />
        
            <form method="post" action="reports.php?data=adminreport">
                <div id="basic_search">
                    <div style="min-height:25px;">
        
                        <?php echo csrf_token(); ?>
                        <label>
                            <?php echo __('From Date'); ?>:
                            <input type="text" class="dp input-medium search-query" name="start_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                            echo Format::htmlchars($report->getStartDate());
                                                                                                                                                            ?>" />
                        </label>
        
                        <label>
                            <?php echo __('To Date'); ?>:
                            <input type="text" class="dp input-medium search-query" name="end_date" placeholder="<?php echo __('Last month'); ?>" value="<?php
                                                                                                                                                            echo Format::htmlchars($report_e->getStartDate());
                                                                                                                                                            ?>" />
                        </label>
                        <div class="clear">
        
                <hr>
                <br>
                        <button class="green button action-button muted" type="submit" name="str1">
                            <?php echo __('submit'); ?>
                        </button>
                        <i class="help-tip icon-question-sign" href="#"></i>
        
                    </div>
        
                </div>
                
                <div class="clear">
                    <br>
                    <br>
                </div>
                <?php
                if (isset($_POST["str1"])){
        
                    $FromDate = explode('.', str_replace('T', ' ', $_POST['start_date']))[0];
                    $ToDate = explode('.', str_replace('T', ' ', $_POST['end_date']))[0];
                    $your_FromDate = strtotime("1 day", strtotime($FromDate));
                    $new_FromDate = date("Y-m-d", $your_FromDate);
                    $your_ToDate = strtotime("1 day", strtotime($ToDate));
                    $new_ToDate = date("Y-m-d", $your_ToDate);
        
                    $FromDate = explode(' ', $new_FromDate)[0] . " " . "00:00:00";
                    $ToDate = explode(' ', $new_ToDate)[0] . " " . "21:00:00";
                    // echo $FromDate;
                    // echo "<br>";
                    // echo $ToDate;
                }
                if (!isset($_POST["str1"])) {
                    $FromDate = "2021/01/01 00:00:00";
                    $ToDate = "2021/12/01 21:00:00";
                }
$task_count=array();
$reply_count=array();
$reply_avg=array();
$Dep_Id=array();
$Dep_name=array();


                 $GetSQl="SELECT COUNT(`ost_task`.`id`),(SELECT getResponceCount( `ost_task`.`dept_id`,'".$FromDate." ','".$ToDate."')) as reply, (SELECT getResponceCount( `ost_task`.`dept_id`,'".$FromDate."','".$ToDate."'))/COUNT(`ost_task`.`id`) as result_avg , `ost_task`.`dept_id`,`ost_department`.`name`
                  FROM `ost_task` INNER JOIN `ost_department` ON `ost_department`.`id`= `ost_task`.`dept_id` WHERE `ost_task`.`closed` IS NOT NUll And `ost_task`.`closed` between '".$FromDate." ' and ' ".$ToDate."' GROUP BY `ost_task`.`dept_id`";
            // echo $GetSQl; 

           if (($GetSQl_Res = db_query($GetSQl)) && db_num_rows($GetSQl_Res)) {
            while (list($sumavg_,$depname_,$depidto_,$Dep_Id_,$Dep_name_) = db_fetch_row($GetSQl_Res)) {
                array_push($task_count, $sumavg_);
                array_push($reply_count, $depname_);
                array_push($reply_avg, $depidto_);
                array_push($Dep_Id, $Dep_Id_);
                array_push($Dep_name, $Dep_name_);
                
            }
        }
?>
<table style="border: 1px solid black; border-collapse: collapse;" class="dashboard-stats table">
            
<tbody>
</tbody>

                <tbody>
                    <tr>
                    <th <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Department");

?>

</th>
                        <th <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Task Count");

                                                                            ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Responses Count");

                                                                                                        ?>

                        </th>
                        <th style=" text-align:center;" <?php echo 'width="10%" class="flush-left"'; ?>><?php echo Format::htmlchars("Avg");

                                                                                                        ?>

                        </th>
                        <?php
                        ?>
                    </tr>
                </tbody>

               
                <tbody>
                    <?php
                    foreach ($task_count as $index => $item) {
                             ?>
                        <tr>
                        <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($Dep_name[$index]);

?>

</th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($item);

                                                                        ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($reply_count[$index]);

                                                                                                                                                                                                                    ?>

                            </th>
                            <th style="font-weight: bold; background-color:#F4F4F4; border-right: 2px solid #ffffff; text-align:center;" <?php echo ' class="flush-left"'; ?> <?php echo ' class="flush-left"'; ?>><?php echo Format::htmlchars($reply_avg[$index]);

                                                                                                                                                                                                                    ?>

                            </th>
                           

                        </tr>
                    <?php }?>
                </tbody>
                
                
            </table>

<?php
                }
}

else{

if (isset($_POST['start']) && isset($_POST['period'])) {
    $report = new OverviewReport($_POST['start'], $_POST['period']);
} elseif (isset($_GET['start']) && isset($_GET['period'])) {
    $report = new OverviewReport($_GET['start'], $_GET['period']);
} else {
    $report = new OverviewReport($_POST['start'], $_POST['period']);
}
echo $_POST['start'];
echo "<br>";
echo $_GET['period'];
?>
<script type="text/javascript" src="js/raphael-min.js?a5d898b"></script>
<script type="text/javascript" src="js/g.raphael.js?a5d898b"></script>
<script type="text/javascript" src="js/g.line-min.js?a5d898b"></script>
<script type="text/javascript" src="js/g.dot-min.js?a5d898b"></script>
<script type="text/javascript" src="js/dashboard.inc.js?a5d898b"></script>

<link rel="stylesheet" type="text/css" href="css/dashboard.css?a5d898b"/>

<form method="post" action="reports.php">
<div id="basic_search">
    <div style="min-height:25px;">
        <!--<p><?php //echo __('Select the starting time and period for the system activity graph');?></p>-->
        <?php echo csrf_token(); ?>
        <label>
            <?php echo __( 'Report timeframe'); ?>:
            <input type="text" class="dp input-medium search-query"
                name="start" placeholder="<?php echo __('3/7/20');?>"
                value="<?php echo Format::htmlchars($report->getStartDate()); ?>" />
        </label>
        <label>
            <?php echo __( 'period');?>:
            <select name="period">
                <option value="now" selected="selected">
                    <?php echo __( 'Up to today');?>
                </option>
                <option value="+7 days">
                    <?php echo __( 'One Week');?>
                </option>
                <option value="+14 days">
                    <?php echo __( 'Two Weeks');?>
                </option>
                <option value="+1 month">
                    <?php echo __( 'One Month');?>
                </option>
                <option value="+3 months">
                    <?php echo __( 'One Quarter');?>
                </option>
            </select>
        </label>
        <button class="green button action-button muted" type="submit">
            <?php echo __( 'Refresh');?>
        </button>
        <i class="help-tip icon-question-sign" href="#report_timeframe"></i>
    </div>
</div>
<div class="clear"></div>

<hr/>
<h2><?php echo __('Reports'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#reports"></i></h2>
<p><?php echo __('Reports of agents organized by department, help topic.');?></p>
<p><b><?php echo __('Range: '); ?></b>
  <?php
  $range = array();
  foreach ($report->getDateRange() as $date)
  {
    $date = str_ireplace('FROM_UNIXTIME(', '',$date);
    $date = str_ireplace(')', '',$date);
    $date = new DateTime('@' . $date);
    $date->setTimeZone(new DateTimeZone($cfg->getTimezone()));
    $timezone = $date->format('e');
    $range[] = $date->format('F j, Y');
  }
  echo __($range[0] . ' - ' . $range[1] .  ' (' . Format::timezone($timezone) . ')');
?>

<ul class="clean tabs">
<?php
$first = true;
$groups = $report->enumTabularGroups();

foreach ($groups as $g=>$desc) {
	if (Format::slugify($g) === 'staff-ht') { ?>
		<li class="<?php echo $first ? 'active' : ''; ?>" style="display:none;">
	<?php } else { ?>
		<li class="<?php echo $first ? 'active' : ''; ?>">
	<?php } ?>
			<a href="#<?php echo Format::slugify($g); ?>">
				<?php echo Format::htmlchars($desc); ?>
			</a>
		</li>
	<?php $first = false;
} ?>
</ul>

<?php
$first = true;
foreach ($groups as $g=>$desc) {
    $data = $report->getTabularData($g); ?>
    <div class="tab_content <?php echo (!$first) ? 'hidden' : ''; ?>" id="<?php echo Format::slugify($g); ?>">
    <table class="dashboard-stats table"><tbody><tr>
<?php
    foreach ($data['columns'] as $j=>$c) { ?>
        <th <?php if ($j === 0) echo 'width="30%" class="flush-left"'; ?>><?php echo Format::htmlchars($c);
        switch ($c) {
          case 'Opened':
            ?>
              <i class="help-tip icon-question-sign" href="#opened"></i>
            <?php
            break;
          case 'Assigned':
            ?>
              <i class="help-tip icon-question-sign" href="#assigned"></i>
            <?php
            break;
            case 'Overdue':
              ?>
                <i class="help-tip icon-question-sign" href="#overdue"></i>
              <?php
              break;
            case 'Closed':
              ?>
                <i class="help-tip icon-question-sign" href="#closed"></i>
              <?php
              break;
            case 'Reopened':
              ?>
                <i class="help-tip icon-question-sign" href="#reopened"></i>
              <?php
              break;
            case 'Deleted':
              ?>
                <i class="help-tip icon-question-sign" href="#deleted"></i>
              <?php
              break;
            case 'Service Time':
              ?>
                <i class="help-tip icon-question-sign" href="#service_time"></i>
              <?php
              break;
            case 'Response Time':
              ?>
                <i class="help-tip icon-question-sign" href="#response_time"></i>
              <?php
              break;
        }
        ?></th>
<?php
    } ?>
    </tr></tbody>
    <tbody>
<?php
    foreach ($data['data'] as $i=>$row) {
        echo '<tr>';
        foreach ($row as $j=>$td) {
            if ($j === 0) {
                if ($data['columns'][0] === 'Help Topic' && strpos(Format::htmlchars($td), 'Disabled')  <= -1) {
					$HelpTopicID = 0;
					$FullHelpTopic = explode('/ ', Format::htmlchars($td));
					$HelpTopic = '';
					
					if (is_array($FullHelpTopic) && array_key_exists(1, $FullHelpTopic)) {
						$HelpTopic = $FullHelpTopic[1];
					}
					
					$GetHelpTopicQ = "SELECT `topic_id` FROM `ost_help_topic` WHERE `topic` LIKE '$HelpTopic';";
					
					if (($GetHelpTopicRes = db_query($GetHelpTopicQ)) && db_num_rows($GetHelpTopicRes)) {
						$Res = db_fetch_row($GetHelpTopicRes);
						
						if (isset($Res) && isset($Res[0]) && $Res[0] !== '') {
							$HelpTopicID = $Res[0];
						}
					}
						
					$QueryString = "?ht_id=$HelpTopicID";

					if (isset($_POST['start']) && isset($_POST['period'])) {
						$QueryString = $QueryString . "&start=" . $_POST['start'] . "&period=" . $_POST['period'];
					}
?>
                  	<th class="flush-left"><a href="<?php echo $QueryString ?>#staff-ht"><?php echo Format::htmlchars($td); ?></a></th>
<?php           } else if ($data['columns'][0] === 'Agent' || strpos(Format::htmlchars($td), '-') !== true) { ?>
					<th class="flush-left"><?php echo Format::htmlchars($td); ?></th>
<?php           }
            } else { ?>
                <td><?php echo Format::htmlchars($td);
                if ($td) {} // TODO Add head map
                echo '</td>';
            }
        }
        echo '</tr>';
    }
    $first = false; ?>
    </tbody></table>
    <div style="margin-top: 5px"><button type="submit" class="link button" name="export"
        value="<?php echo Format::htmlchars($g); ?>">
        <i class="icon-download"></i>
        <?php echo __('Export'); ?></a></div>
    </div>
<?php
}
?>
</form>
<?php }?>