<?php
// Calling convention (assumed global scope):
// $tickets - <QuerySet> with all columns and annotations necessary to
//      render the full page


// Impose visibility constraints
// ------------------------------------------------------------
//filter if limited visibility or if unlimited visibility and in a queue
$ignoreVisibility = $queue->ignoreVisibilityConstraints($thisstaff);
if (
    !$ignoreVisibility || //limited visibility
    ($ignoreVisibility && ($queue->isAQueue() || $queue->isASubQueue())) //unlimited visibility + not a search
)
    $tickets->filter($thisstaff->getTicketsVisibility());

// Make sure the cdata materialized view is available
TicketForm::ensureDynamicDataView();

// Identify columns of output
$columns = $queue->getColumns();

// Figure out REFRESH url — which might not be accurate after posting a
// response
list($path,) = explode('?', $_SERVER['REQUEST_URI'], 2);
$args = array();
parse_str($_SERVER['QUERY_STRING'], $args);
// if (isset(($_POST['end_date']))) {
//     $report_e = new OverviewReport($_POST['end_date']);
// } elseif ( isset($_GET['end_date'])) {
//     $report_e = new OverviewReport($_POST['end_date']);
// } else {
//     $report_e = new OverviewReport($_POST['end_date']);
// }
// Remove commands from query

$Q = 'select `ticket_id` from `ost_ticket` WHERE

`ost_ticket`.`topic_id` IN (160,161,159)
and `ost_ticket`.`ticket_type` IS NULL';

if (($Q_Res = db_query($Q)) && db_num_rows($Q_Res)) {
    while (list($ID) = db_fetch_row($Q_Res)) {
        $HelpTopicConstraint_Q = 'SELECT `ost_help_topic`.`topic` FROM `ost_ticket` inner JOIN `ost_help_topic` on `ost_help_topic`.`topic_id`=`ost_ticket`.`topic_id` WHERE `ticket_id` = ' . $ID;
        $HelpTopicID = 0;
        if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
            $Res = db_fetch_row($HelpTopicConstraint_Res);
            $HelpTopicID = $Res[0];
        }

        if (trim($HelpTopicID) == "restaurant activation") {
            $type = 'R';
        }
        if (trim($HelpTopicID) == "driver activation") {
            $type = 'D';
        }
        $get_type = 'SELECT  `ticket_type`  FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
        if (($get_type_Res = db_query($get_type)) && db_num_rows($get_type_Res)) {

            $ticket_type = db_fetch_row($get_type_Res);
        }

        if ($ticket_type[0] == null) {
            $update_type = "UPDATE `ost_ticket` SET  `ticket_type` = '" . $type . "' WHERE `ticket_id` = " . $ID;
            db_query($update_type);
        }

        // if(in_array(trim($HelpTopicID),$ID)){
        $get_D = "SELECT `body`  FROM `ost_thread_entry` 
                INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
                WHERE `ost_thread`.`object_id`=" . $ID . " AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'";
        if (($get_D_Res = db_query($get_D)) && db_num_rows($get_D_Res)) {

            $DD = db_fetch_row($get_D_Res);
        }



        $get_city = 'SELECT `city` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
        if (($get_city_Res = db_query($get_city)) && db_num_rows($get_city_Res)) {

            $city = db_fetch_row($get_city_Res);
        }

        if ($city[0] == null && explode("المدينة", explode("من قِبل", $DD[0])[0])[1] != null) {
            $update_city = "UPDATE `ost_ticket` SET `city`='" . strip_tags(trim(explode(":", explode("المدينة", explode("من قِبل", $DD[0])[0])[1])[1])) . "'  WHERE `ticket_id` = " . $ID;
            db_query($update_city);
        }

        // }

    }
}
unset($args['id']);
if ($args['a'] !== 'search') unset($args['a']);

if ($_REQUEST['a'] !== 'afms') {
    $refresh_url = $path . '?' . http_build_query($args);
} else if ($_REQUEST['a'] == 'afms') {
    $refresh_url = $path . '?a=afms';
} else if ($_REQUEST['a'] == 'cbs') {
    $refresh_url = $path . '?a=cbs';
} else if ($_REQUEST['a'] == 'Msearch') {
    $refresh_url = $path . '?a=Msearch';
} else if ($_REQUEST['a'] == 'BookMark') {
    $refresh_url = $path . '?a=BookMark';
} else if ($_REQUEST['a'] == 'sbt') {
    $refresh_url = $path . '?a=sbt';
} else if ($_REQUEST['a'] == 'sbc') {
    $refresh_url = $path . '?a=sbc';
}
// Establish the selected or default sorting mechanism
if (isset($_GET['sort']) && is_numeric($_GET['sort'])) {
    $sort = $_SESSION['sort'][$queue->getId()] = array(
        'col' => (int) $_GET['sort'],
        'dir' => (int) $_GET['dir'],
    );
} elseif (
    isset($_GET['sort'])
    // Drop the leading `qs-`
    && (strpos($_GET['sort'], 'qs-') === 0)
    && ($sort_id = substr($_GET['sort'], 3))
    && is_numeric($sort_id)
    && ($sort = QueueSort::lookup($sort_id))
) {
    $sort = $_SESSION['sort'][$queue->getId()] = array(
        'queuesort' => $sort,
        'dir' => (int) $_GET['dir'],
    );
} elseif (isset($_SESSION['sort'][$queue->getId()])) {
    $sort = $_SESSION['sort'][$queue->getId()];
} elseif ($queue_sort = $queue->getDefaultSort()) {
    $sort = $_SESSION['sort'][$queue->getId()] = array(
        'queuesort' => $queue_sort,
        'dir' => (int) $_GET['dir'] ?: 0,
    );
}

// Handle current sorting preferences

$sorted = false;
foreach ($columns as $C) {
    // Sort by this column ?
    if (isset($sort['col']) && $sort['col'] == $C->id) {
        $tickets = $C->applySort($tickets, $sort['dir']);
        $sorted = true;
    }
}
if (!$sorted && isset($sort['queuesort'])) {
    // Apply queue sort-dropdown selected preference
    $sort['queuesort']->applySort($tickets, $sort['dir']);
}

// Apply pagination

$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$pageNav = new Pagenate(PHP_INT_MAX, $page, PAGE_LIMIT);
$tickets = $pageNav->paginateSimple($tickets);

if (isset($tickets->extra['tables'])) {
    // Creative twist here. Create a new query copying the query criteria, sort, limit,
    // and offset. Then join this new query to the $tickets query and clear the
    // criteria, sort, limit, and offset from the outer query.
    $criteria = clone $tickets;
    $criteria->limit(500);
    $criteria->annotations = $criteria->related = $criteria->aggregated =
        $criteria->annotations = $criteria->ordering = [];
    $tickets->constraints = $tickets->extra = [];
    $tickets = $tickets->filter(['ticket_id__in' =>
    $criteria->values_flat('ticket_id')]);
    # Index hint should be used on the $criteria query only
    $tickets->clearOption(QuerySet::OPT_INDEX_HINT);
}

$tickets->distinct('ticket_id');
$count = $queue->getCount($thisstaff) ?: (PAGE_LIMIT * 3);
$pageNav->setTotal($count, true);
$pageNav->setURL('tickets.php', $args);

$result = array();
$sql = "SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=" . $thisstaff->getId();

if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
    while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
        array_push($result, $RecurringTaskID);
    }
}
$GetTeamId = "SELECT `team_id` FROM `ost_team_member` WHERE  `staff_id`=" . $thisstaff->getId() . "";
if (($GetTeamId_Res = db_query($GetTeamId)) && db_num_rows($GetTeamId_Res)) {
    $Res = db_fetch_row($GetTeamId_Res);
    $GetTeamId_ = $Res[0];
}

$GetRoleIdQ = "SELECT role_id from ost_staff where `staff_id`=" . $thisstaff->getId() . "";
if (($GetRoleId_Res = db_query($GetRoleIdQ)) && db_num_rows($GetRoleId_Res)) {
    $Res = db_fetch_row($GetRoleId_Res);
    $GetRoleId = $Res[0];
}

$Getfalgs_ = "SELECT `flags` FROM `ost_queue` WHERE  `id`=" . $_SESSION[$queue_key] . "";

if (($Getfalgs_Res = db_query($Getfalgs_)) && db_num_rows($Getfalgs_Res)) {
    $Res = db_fetch_row($Getfalgs_Res);
    $Getfalgs = $Res[0];
}
$SQLQueue = "select  id ,city,vehicle from ost_external_status e inner join (select case WHEN pq.title in ('city','vehicle type') then gq.title  when pq.title = 'Bee order' THEN q.title else pq.title END title , case when pq.title='city' then q.title else '' end as city  , case when pq.title = 'vehicle type' then q.title else '' end as vehicle from ost_queue q left join ost_queue pq on pq.id = q.parent_id LEFT join ost_queue gq on gq.id = pq.parent_id where q.id =  " . $_SESSION[$queue_key] . ") as queue on e.status = queue.title";
if (($Queuevalues_Res = db_query($SQLQueue)) && db_num_rows($Queuevalues_Res)) {
    while (list($Status_id_, $City_, $Vehicle_) = db_fetch_row($Queuevalues_Res)) {
        $Status_id = $Status_id_;
        $City =  $City_;
        $Vehicle =  $Vehicle_;
    }
}
session_start();
$_SESSION["City"] = $City;
$_SESSION["Vehicle"] =  $Vehicle;

$GetCities = array();
$sqlCities = "SELECT ost_queue.title FROM ost_queue AS ost_queue_1 INNER JOIN ost_queue ON ost_queue_1.id = ost_queue.parent_id  WHERE (((ost_queue_1.title)='City')) GROUP BY ost_queue.title";
if (($GetCities_Res = db_query($sqlCities)) && db_num_rows($GetCities_Res)) {
    while (list($Cities_) = db_fetch_row($GetCities_Res)) {
        array_push($GetCities, $Cities_);
    }
}

$GetVehicles = array();
$sqlVehicles = "SELECT ost_queue.title FROM ost_queue AS ost_queue_1 INNER JOIN ost_queue ON ost_queue_1.id = ost_queue.parent_id  WHERE (((ost_queue_1.title)='vehicle type')) GROUP BY ost_queue.title";
if (($GetVehicles_Res = db_query($sqlVehicles)) && db_num_rows($GetVehicles_Res)) {
    while (list($Vehicles_) = db_fetch_row($GetVehicles_Res)) {
        array_push($GetVehicles, $Vehicles_);
    }
}

?>

<!-- SEARCH FORM START -->
<div id='basic_search'>
    <div class="pull-right" style="height:25px">
        <span class="valign-helper"></span>
        <?php
        require 'queue-quickfilter.tmpl.php';
        if ($queue->getSortOptions())
            require 'queue-sort.tmpl.php';
        ?>
    </div>
    <div class="pull-right" style="height:25px">
        <span class="action-button">
            <a class="tasks-status-action" href="tickets.php?a=sbt" title="<?php echo __('Sort By Type'); ?>"><i class="icon-truck"></i></a>
        </span>
    </div>
    <div class="pull-right" style="height:25px">
        <span class="action-button">
            <a class="tasks-status-action" href="tickets.php?a=sbc" title="<?php echo __('Sort By City'); ?>"><i class="icon-building"></i></a>
        </span>
    </div>
    <div class="attached input" style="display: inline;">
        <form action="tickets.php" method="get" onsubmit="javascript:
  $.pjax({
    url:$(this).attr('action') + '?' + $(this).serialize(),
    container:'#pjax-container',
    timeout: 2000
  });
return false;" style="display: inline;">
            <input type="hidden" name="a" value="search">
            <input type="hidden" name="search-type" value="" />
            <div class="attached input">
                <input type="text" class="basic-search" data-url="ajax.php/tickets/lookup" name="query" autofocus size="30" value="<?php echo Format::htmlchars($_REQUEST['query'], true); ?>" autocomplete="off" autocorrect="off" autocapitalize="off">
                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </div>
            <a href="#" onclick="javascript:
        $.dialog('ajax.php/tickets/search', 201);">[<?php echo __('advanced'); ?>]</a>
            <i class="help-tip icon-question-sign" href="#advanced"></i>

        </form>

        <form action="tickets.php" <?php if ($GetTeamId_ != 154) echo "style='display: none;'"; else echo "style='display: inline;'";  ?> method="get" onsubmit="javascript:$.pjax({ url:$(this).attr('action')+'?queue='+getParameterByName('queue') + '&City='+document.getElementById('ddlCities').value.replace(/[^a-zA-Z ]/g, '')+'&Vehicle='+document.getElementById('ddlVehicles').value.replace(/[^a-zA-Z ]/g, ''), container:'#pjax-container', timeout: 2000});return false;" >
            <input type="hidden" name="<?php $_SESSION[$queue_key] ?>" value="queue" />
            <input type="hidden" name="<?php $_SESSION["City"] ?>" value="City" id="City">

            <input type="hidden" name="Type" value="City" />
            <div class="attached input">

                <select class="modal-body" id="ddlCities" name="ddlCities">
                    <?php 
                    if (isset($_REQUEST['City']) && $_REQUEST['City'] !=null ) {
                    ?>
                        <option <?php echo $_REQUEST['City']; ?>> <?php echo $_REQUEST['City']; ?> </option>
                    <?php
                    }
                     else if  (isset($_SESSION["City"]) && $_SESSION["City"]!=null)
                    {
                        ?>
                        <option <?php echo $_SESSION["City"]; ?>> <?php echo $_SESSION["City"]; ?> </option>

                    <?php }
                    else {?>
                  
                        <option disabled selected value> -- select a City -- </option>

                    <?php } ?>
                    <?php foreach ($GetCities as $index => $item) {
                        if($item != $_REQUEST['City'] && $item != $_SESSION['City'] ) {
                    ?>
                        <option value="<?php echo $x[$index] . ":" . $item; ?>"><?php echo $item; ?></option>
                    <?php
                    }} ?>
                </select>

                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </div>

            <input type="hidden" name="<?php $_SESSION[$queue_key] ?>" value="queue">
            <input type="hidden" name="<?php $_SESSION["Vehicle"] ?>" value="Vehicle" id="Vehicle">
            <div class="attached input">

                <select class="modal-body" id="ddlVehicles" name="ddlVehicles">
                    <?php if (isset($_REQUEST['Vehicle']) && $_REQUEST['Vehicle'] != null) {
                    ?>
                        <option <?php echo $_REQUEST['Vehicle']; ?>> <?php echo $_REQUEST['Vehicle']; ?> </option>
                    <?php  }
                     else if  (isset($_SESSION["Vehicle"]) && $_SESSION["Vehicle"]!=null)
                    {
                        ?>
                        <option <?php echo $_SESSION["Vehicle"]; ?>> <?php echo $_SESSION["Vehicle"]; ?> </option>

                    <?php }
                    else {?>

                        <option disabled selected value> -- select a Vehicle -- </option>

                    <?php } ?>
                    <?php foreach ($GetVehicles as $index => $item) {
                           if($item != $_REQUEST['Vehicle']) {
                    ?>
                        <option value="<?php echo $x[$index] . ":" . $item; ?>"><?php echo $item; ?></option>
                    <?php
                    }} ?>
                </select>

                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<?php

  if (($GetTeamId_ == '154' && $GetRoleId == '1')  ) {
?>

    <form action="export_data.php" method="get" onsubmit="javascript:$.pjax({
    url:$(this).attr('action') + '?export=true&fromdate=' +document.getElementById('txt_from_date').value+'&todate='+document.getElementById('txt_to_date').value+'&type='+document.getElementById('type').value,
    container:'#pjax-container',
    timeout: 2000 }); return false;" style="display: inline;">
    <div id="reports_div" style="text-align:right">
        <br />
        <input type="date" name="txt_from_date" title="From date" id="txt_from_date" onchange="compare();" required="true" value="<?php echo date("Y-m-d");?>" />
        <input type="date" name="txt_to_date"  title="To date" id="txt_to_date" onchange="compare();" required="true" value="<?php echo date("Y-m-d");?>" />
        <input type= "hidden" name= "type" />
        <br />
        <br />
        <input  value = "جرد مواد بي أوردر" name="btn_beeorder_stk" class="action-button" id="btn_beeorder_stk" type="submit" class="btn btn-info btn-sm"  />
        <input  value = "جرد كافة مواد بي أوردر" name="btn_all_beeorder_stks" class="action-button" id="btn_all_beeorder_stks" type="submit" class="btn btn-info btn-sm"  />
        <input  value = "أداء الموظفين" name="btn_employeesPref_report" class="action-button" id="btn_employeesPref_report" type="submit" class="btn btn-info btn-sm" />
        <input  value = "حركة المواد الداخلة" name="btn_beeorder_stocks_actions_recive" class="action-button" id="btn_beeorder_stocks_actions_recive" type="submit" class="btn btn-info btn-sm" />
        <input  value = "حركة المواد المخرجة" name="btn_beeorder_stocks_actions_send" class="action-button" id="btn_beeorder_stocks_actions_send" type="submit" class="btn btn-info btn-sm" />

        <br>
        <script>
            function compare() {
                var startDt = document.getElementById("txt_from_date").value;
                var endDt = document.getElementById("txt_to_date").value;

                if ((new Date(startDt).getTime() > new Date(endDt).getTime())) {
                    alert("please check dates");
                    document.getElementById("btn_employeesPref_report").disabled = true;
                } else {
                    document.getElementById("btn_employeesPref_report").disabled = false;
                }
            }
        </script>
  
  <br />
    <br />
</div>
<!-- <?php
// }
//  if (($thisstaff->getId() == '223')) { ?> -->
 <div id="reports_div" style="text-align:right ;border-top: 1px solid gray;" width = "100px">
 </br>
   <input type="date" name="txt_Date"  title=" date" id="txt_Date" value="<?php echo date("Y-m-d");?>" />
 </br></br>

   <input  value = "جرد  مواد بي أوردر بتاريخ" name="btn_stk_on_hand" class="action-button" id="btn_stk_on_hand" type="submit" class="btn btn-info btn-sm" />
 </div>
 </form>
    <br /> <br />
<?php
}
 ?>


<!-- SEARCH FORM END -->

<div class="clear"></div>
<?php if ($thisstaff->getId() == 10) { ?>
    <div id="basic_search">
        <div style="min-height:60px;">

            <!--<p><?php //echo __('Select the starting time and period for the system activity graph');
                    ?></p>-->
            <form method="post" action="tickets.php?a=Msearch">
                <?php echo csrf_token(); ?>
                <label>
                    <?php echo __('Search By'); ?>:
                    <select name="gets" id="gets">
                        <option value="0">
                            <?php echo __('ID'); ?>
                        </option>
                        <option value="1">
                            <?php echo __('Number'); ?>
                        </option>
                        <option value="2">
                            <?php echo __('Title'); ?>
                        </option>
                        <option value="3" selected="selected">
                            <?php echo __('Content'); ?>
                        </option>
                    </select>
                </label>
                <input type="text" name="searchC">
                <button type="submit" class="attached button"><i class="icon-search"></i>
                </button>
            </form>
            <br>
            <br>
        </div>
    </div>
<?php } ?>
<div class="clear"></div>
<div style="margin-bottom:20px; padding-top:5px;">
    <div class="sticky bar opaque">
        <div class="content" style="width: 100%;">
            <div class="pull-left flush-left">
                <?php if (($_REQUEST['a'] !== 'sbc') && ($_REQUEST['a'] !== 'sbt') && ($_REQUEST['a'] !== 'afms') && ($_REQUEST['a'] !== 'cbs') && ($_REQUEST['a'] !== 'Msearch') && ($_REQUEST['a']  !== 'BookMark')) { ?>
                    <!--yaseen-->
                    <?php $Getparent_id = "SELECT CONCAT (title,' / ')  FROM `ost_queue` WHERE `parent_id` is not null and `parent_id`>0 and `id` IN (select `parent_id` from `ost_queue` where id =" . $_SESSION[$queue_key] . ")";

                    if (($Getparent_id_Res = db_query($Getparent_id)) && db_num_rows($Getparent_id_Res)) {
                        $Res = db_fetch_row($Getparent_id_Res);
                        $Getparent_id_ = $Res[0];
                    }

                    ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo
                                                                                                                                $Getparent_id_ . "" .  $queue->getName(); ?></a>
                        <?php
                        if (($crit = $queue->getSupplementalCriteria()))
                            echo sprintf(
                                '<i class="icon-filter"
                                    data-placement="bottom" data-toggle="tooltip"
                                    title="%s"></i>&nbsp;',
                                Format::htmlchars($queue->describeCriteria($crit))
                            );
                        ?>
                    </h2>
                <?php } else  if ($_REQUEST['a'] == 'afms') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> From My Staff</a>
                    </h2>
                <?php } else  if ($_REQUEST['a'] == 'cbs') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> Closed By System</a>
                    </h2>
                <?php  } else  if ($_REQUEST['a'] == 'Msearch') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> Search </a>
                    </h2>
                <?php  } else  if ($_REQUEST['a'] == 'BookMark') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> Search </a>
                    </h2>
                <?php } else  if ($_REQUEST['a'] == 'sbt') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> Search </a>
                    </h2>
                <?php } else  if ($_REQUEST['a'] == 'sbc') { ?>
                    <h2><a href="<?php echo $refresh_url; ?>" title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> Search </a>
                    </h2>
                <?php } ?>
            </div>
            <div class="configureQ">
                <i class="icon-cog"></i>
                <div class="noclick-dropdown anchor-left">
                    <ul>
                        <li>
                            <a class="no-pjax" href="#" data-dialog="ajax.php/tickets/search/<?php echo
                                                                                                urlencode($queue->getId()); ?>"><i class="icon-fixed-width icon-pencil"></i>
                                <?php echo __('Edit'); ?></a>
                        </li>
                        <li>
                            <a class="no-pjax" href="#" data-dialog="ajax.php/tickets/search/create?pid=<?php
                                                                                                        echo $queue->getId(); ?>"><i class="icon-fixed-width icon-plus-sign"></i>
                                <?php echo __('Add Sub Queue'); ?></a>
                        </li>
                        <?php

                        if ($queue->id > 0 && $queue->isOwner($thisstaff)) { ?>
                            <li class="danger">
                                <a class="no-pjax confirm-action" href="#" data-dialog="ajax.php/queue/<?php
                                                                                                        echo $queue->id; ?>/delete"><i class="icon-fixed-width icon-trash"></i>
                                    <?php echo __('Delete'); ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="pull-right flush-right">
                <?php
                // TODO: Respect queue root and corresponding actions
                if ($count) {
                    Ticket::agentActions($thisstaff, array('status' => $status));
                } ?>
            </div>
        </div>
    </div>

    <?php
    //rahaf
    foreach ($tickets as $T) {
        if ($T['dept_id'] == 26) {
            $ID = array();
            $sql_get_hp = "SELECT `topic` FROM `ost_help_topic` WHERE `dept_id` = 26";
            if (($sql_Res = db_query($sql_get_hp)) && db_num_rows($sql_Res)) {
                while (list($ID_) = db_fetch_row($sql_Res)) {

                    array_push($ID, $ID_);
                    // $ID = db_fetch_row($sql_Res);
                }
            }
            $HelpTopicConstraint_Q = 'SELECT `ost_help_topic`.`topic` FROM `ost_ticket` inner JOIN `ost_help_topic` on `ost_help_topic`.`topic_id`=`ost_ticket`.`topic_id` WHERE `ticket_id` = ' . $T['ticket_id'];
            $HelpTopicID = 0;
            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                $Res = db_fetch_row($HelpTopicConstraint_Res);
                $HelpTopicID = $Res[0];
            }

            if (trim($HelpTopicID) == "restaurant activation") {
                $type = 'R';
            }
            if (trim($HelpTopicID) == "driver activation") {
                $type = 'D';
            }
            $get_type = 'SELECT  `ticket_type`  FROM `ost_ticket` WHERE `ticket_id` = ' . $T['ticket_id'];
            if (($get_type_Res = db_query($get_type)) && db_num_rows($get_type_Res)) {

                $ticket_type = db_fetch_row($get_type_Res);
            }

            if ($ticket_type[0] == null) {
                $update_type = "UPDATE `ost_ticket` SET  `ticket_type` = '" . $type . "' WHERE `ticket_id` = " . $T['ticket_id'];
                db_query($update_type);
            }

            if (in_array(trim($HelpTopicID), $ID)) {
                $get_D = "SELECT `body`  FROM `ost_thread_entry` 
        INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
        WHERE `ost_thread`.`object_id`=" . $T['ticket_id'] . " AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'";
                if (($get_D_Res = db_query($get_D)) && db_num_rows($get_D_Res)) {

                    $DD = db_fetch_row($get_D_Res);
                }



                $get_city = 'SELECT `city` FROM `ost_ticket` WHERE `ticket_id` = ' . $T['ticket_id'];
                if (($get_city_Res = db_query($get_city)) && db_num_rows($get_city_Res)) {

                    $city = db_fetch_row($get_city_Res);
                }

                if ($city[0] == null && explode("المدينة", explode("من قِبل", $DD[0])[0])[1] != null) {
                    $update_city = "UPDATE `ost_ticket` SET `city`='" . strip_tags(trim(explode(":", explode("المدينة", explode("من قِبل", $DD[0])[0])[1])[1])) . "'  WHERE `ticket_id` = " . $T['ticket_id'];
                    db_query($update_city);
                }
            }
        } else {
            continue;
        }
    } //rahaf
    if ($_REQUEST['a'] == 'afms') {

        // if (isset($_GET['end_date'])) {
        //     $report_e = new OverviewReport($_GET['end_date']);
        // } 
        // if(isset($_POST['end_date'])) {
        //     $report_e = new OverviewReport($_POST['end_date']);
        // }
    ?>
        <form method="post" action="tickets.php?a=afms">
            <div id="basic_search">
                <div style="min-height:25px;">

                    <?php echo csrf_token(); ?>


                    <label style="margin-left: 25px;">
                        <?php echo __('To Date'); ?>:
                        <input type="text" class="dp input-medium search-query" name="end_date" placeholder="<?php echo __('Last month'); ?>" />
                    </label>
                    <button class="green button action-button muted" type="submit" name="submit">
                        <?php echo __('submit'); ?>
                    </button>
                    <i class="help-tip icon-question-sign" href="#"></i>

                </div>

            </div>


        </form>
    <?php }
    $getstaffofagentT = array();
    $getstaffofagent = "SELECT `ost_staff`.`firstname` FROM `ost_staff` INNER JOIN `ost_team_member` ON `ost_staff`.`staff_id`=`ost_team_member`.`staff_id` INNER JOIN `ost_team` ON `ost_team`.`team_id`=`ost_team_member`.`team_id` WHERE `ost_staff`.`dept_id`=(SELECT `id` FROM `ost_department` WHERE `manager_id`=" . $thisstaff->getId() . ") GROUP BY `ost_staff`.`staff_id`";
    if (($getstaffofagent_Res = db_query($getstaffofagent)) && db_num_rows($getstaffofagent_Res)) {
        while (list($RecurringTaskTitle) = db_fetch_row($getstaffofagent_Res)) {
            array_push($getstaffofagentT, $RecurringTaskTitle);
        }
    }
    ?>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<?php $orderWays = array('DESC' => '-', 'ASC' => '');
if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $negorder = $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $negorder = $order = 'ASC';
} ?>
<form action="?" method="POST" name='tickets' id="tickets">
    <?php csrf_token(); ?>
    <input type="hidden" name="a" value="mass_process">
    <input type="hidden" name="do" id="action" value="">

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <thead>
            <tr>
                <?php
                $canManageTickets = $thisstaff->canManageTickets();
                if ($canManageTickets) { ?>
                    <th style="width:12px"></th>
                <?php
                }
                if (($_REQUEST['a'] !== 'sbc') &&  ($_REQUEST['a'] !== 'sbt')  && ($_REQUEST['a'] !== 'afms') && ($_REQUEST['a'] !== 'cbs') && ($_REQUEST['a'] !== 'Msearch') && ($_REQUEST['a'] !== 'BookMark')) {
                    foreach ($columns as $C) {
                        $heading = Format::htmlchars($C->getLocalHeading());

                        if ($C->isSortable()) {
                            $args = $_GET;
                            $dir = $sort['col'] != $C->id ?: ($sort['dir'] ? 'desc' : 'asc');
                            $args['dir'] = $sort['col'] != $C->id ?: (int) !$sort['dir'];
                            $args['sort'] = $C->id;
                            $heading = sprintf('<a href="?%s" class="%s">%s</a>', Http::build_query($args), $dir, $heading);
                        }

                        echo sprintf('<th width="%s" data-id="%d">%s</th>', $C->getWidth(), $C->id, $heading);
                    }

                    echo "<th>Last Response</th>";
                    //yaseen
                    $Getparent_id = "SELECT `parent_id` FROM `ost_queue` WHERE  `id`=" . $_SESSION[$queue_key] . "";

                    if (($Getparent_id_Res = db_query($Getparent_id)) && db_num_rows($Getparent_id_Res)) {
                        $Res = db_fetch_row($Getparent_id_Res);
                        $Getparent_id_ = $Res[0];
                    }
                    if ($_SESSION[$queue_key] == 20 || $_SESSION[$queue_key] == 21 || $_SESSION[$queue_key] == 22 || $_SESSION[$queue_key] == 23 || $_SESSION[$queue_key] == 24 || $_SESSION[$queue_key] == 25 || $_SESSION[$queue_key] == 26 || $_SESSION[$queue_key] == 27 | $_SESSION[$queue_key] == 28 || $_SESSION[$queue_key] == 29 || $Getparent_id_ == 21 || $Getparent_id_ == 22 || $Getparent_id_ == 23) {
                        echo "<th style='width:10%'>Phone Number</th>";
                        echo "<th style='width:10%'>Driver Name</th>";
                    } else {
                        echo "<th style='width:10%'>Current Agent/s</th>";
                        echo "<th style='width:10%'>Created by</th>";
                    }
                } elseif ($_REQUEST['a'] == 'afms') {
                    $negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting
                    if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                        $order = $orderWays[strtoupper($_REQUEST['order'])];
                    } else {
                        $order = 'ASC';
                    }
                ?><th>
                        <a href="tickets.php?a=afms&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task ID"><?php echo __('Number'); ?>&nbsp;</a>
                    </th>

                    <th>
                        <a href="tickets.php?a=afms&sort=Subject&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task Subject"><?php echo __('Subject'); ?>&nbsp;</a>
                    </th>
                    <th>
                        <a href="tickets.php?a=afms&sort=From&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Task From"><?php echo __('From'); ?>&nbsp;</a>
                    </th>


                <?php
                    // echo "<th>Number</th>";
                    // echo "<th>Subject</th>";
                    // echo "<th>From</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    echo "<th style='width:10%'>Current Agent/s</th>";
                } elseif ($_REQUEST['a'] == 'sbt') {
                    $negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting
                    if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                        $order = $orderWays[strtoupper($_REQUEST['order'])];
                    } else {
                        $order = 'ASC';
                    }
                ?><th>
                        <?php echo __('Number'); ?>&nbsp;
                    </th>

                    <th>
                        <?php echo __('Subject'); ?>&nbsp;
                    </th>
                    <th>
                        <?php echo __('From'); ?>&nbsp;
                    </th>


                    <?php
                    // echo "<th>Number</th>";
                    // echo "<th>Subject</th>";
                    // echo "<th>From</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    echo "<th style='width:10%'>Current Agent/s</th>"; ?>

                    <th>
                        <a href="tickets.php?a=sbt&sort=Type&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Type"><?php echo __('Type'); ?>&nbsp;</a>
                    </th>
                <?php
                    // echo "<th style='width:10%'>Type</th>";
                } elseif ($_REQUEST['a'] == 'sbc') {
                    $negorder = $order == '-' ? 'ASC' : 'DESC'; //Negate the sorting
                    if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
                        $order = $orderWays[strtoupper($_REQUEST['order'])];
                    } else {
                        $order = 'ASC';
                    }
                ?><th>
                        <?php echo __('Number'); ?>&nbsp;
                    </th>

                    <th>
                        <?php echo __('Subject'); ?>&nbsp;
                    </th>
                    <th>
                        <?php echo __('From'); ?>&nbsp;
                    </th>


                    <?php
                    // echo "<th>Number</th>";
                    // echo "<th>Subject</th>";
                    // echo "<th>From</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    echo "<th style='width:10%'>Current Agent/s</th>"; ?>

                    <th>
                        <a href="tickets.php?a=sbc&sort=City&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By City"><?php echo __('City'); ?>&nbsp;</a>
                    </th>
                <?php
                    // echo "<th style='width:10%'>Type</th>";
                } elseif ($_REQUEST['a'] == 'cbs') {
                    echo "<th>Number</th>";
                    echo "<th>Subject</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    // echo "<th style='width:10%'>Current Agent/s</th>";
                } elseif ($_REQUEST['a'] == 'Msearch') {
                    echo "<th>Number</th>";
                    echo "<th>Subject</th>";
                    echo "<th>From</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    echo "<th style='width:10%'>Current Agent/s</th>";
                } elseif ($_REQUEST['a'] == 'BookMark') {
                    echo "<th>Number</th>";
                    echo "<th>Subject</th>";
                    echo "<th>From</th>";
                    echo "<th style='width:30%'>Body</th>";
                    echo "<th>Last Response</th>";
                    echo "<th style='width:10%'>Current Agent/s</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if (($_REQUEST['a'] !== 'sbc') && ($_REQUEST['a'] !== 'sbt') && ($_REQUEST['a'] !== 'afms') && ($_REQUEST['a'] !== 'cbs') && ($_REQUEST['a'] !== 'Msearch') && ($_REQUEST['a'] !== 'BookMark')) {
                $queue_key = sprintf('::Q:%s', ObjectModel::OBJECT_TYPE_TICKET);
                // echo $_SESSION[$queue_key];
                // echo $_SESSION['closed_search'];
                if (strpos($_SESSION[$queue_key], "adhoc,") !== false) {
                    // 
                    // print_r($_SESSION['advsearch']);
                    // echo explode(",", $_SESSION[$queue_key])[1];
                    echo $_SESSION['advsearch'][explode(",", $_SESSION[$queue_key])[1]][0][2];
                }
                if ($_SESSION[$queue_key] == 7) {
                    $CurrentStaffID = $thisstaff->getId();
                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`closed`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as namee,`ost_thread_event`.`username` , `ost_ticket`.`topic_id` ,`ost_department`.`name`,`ost_user`.`name` , `ost_help_topic_flow`.`staff_id` as orderrrr FROM `ost_ticket` 

                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    INNER JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id`
                    INNER JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` 
                    INNER JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id`
                    INNER JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` 
                    INNER JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id`
                    LEFT JOIN `ost_user` ON `ost_user`.`id`=`ost_ticket`.`user_id`
                    
                    
                    WHERE  `ost_ticket`.`closed` IS  NULL 
                     AND `object_type` LIKE 'T'
                    AND ( `ost_ticket`.`team_id` IN ( " . implode(",", $thisstaff->getTeams()) . ")  OR `ost_help_topic_flow`.`team_id` IN ( " . implode(",", $thisstaff->getTeams()) . ") )
                    GROUP BY `ost_ticket`.`number`
                    ORDER BY FIELD(`ost_help_topic_flow`.`staff_id`, " . $thisstaff->getId() . ") DESC ";
                    // echo  $GetUsersStaffTicketsQ ;
                    if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                        while (list($ID, $Number, $ClosedDate, $Subject,  $Staff, $ClosedBy, $Topic_id, $dep, $userName) = db_fetch_row($GetUsersStaffTickets_Res)) {
                            $StaffTeam = 'Not Found!';



                            echo '<tr>';

                            if ($canManageTickets) { ?>
                                <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                                <?php }
                            $LastThreadEntry = Thread::getLastThreadEntry($ID);
                            $Body = "Not available!";
                            $LastResponse = "";

                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body, '<br /><br/><br>');
                                $Body = substr($Body, 0, 200);
                                $LastResponse = $LastThreadEntry[1];
                                $poster = $LastThreadEntry[2];
                            }
                            // echo $LastResponse ;
                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $ID . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }

                            // echo $TicketCurrentStep;

                            $CurrentAgents = "None";
                            $HelpTopicID = $Topic_id;

                            if ($TicketCurrentStep > 0) {
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                // echo $CurrentAgentsID;
                                if ($CurrentAgentsID != 0) {
                                    $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                } else {
                                    $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                }
                            } else if ($TicketCurrentStep == -1) {
                                if ($Closed !== '') {
                                    $CurrentAgents = "Ticket Closed";
                                } else {
                                    if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                        $CurrentAgents = "Ticket Closed";
                                    else
                                        $CurrentAgents = "Ticket To Be Closed";
                                }
                            }

                            if ($CurrentAgents == '') {
                                $CurrentAgents = 'Contact IT!';
                            }
                            $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                            if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                $teamId = $Res11[0];
                            }
                            $CreatedBy_ = "";
                            $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $ID . ");";

                            if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                $Res = db_fetch_row($GetCreatedBy_Res);
                                $CreatedBy_ = $Res[0];
                            }

                            if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                $CreatedBy = 'No Agent';
                            } else {

                                $CreatedBy = $CreatedBy_;
                            }

                            if ($Staff == '' || is_null($Staff)) {
                                $Staff_ = $userName;
                            } else {

                                $Staff_ = $Staff;
                            }
                            if ($thisstaff->getId() == 6) {
                                $CurrentAgentColor = "#c456ce";
                            }
                            $CurrentAgentColor = "#fd7f7f";
                            if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                            } else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style=""';
                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                    // echo $teamId;
                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                } else {
                                    $CurrentAgentsCellStyle = 'style=""';
                                }
                            }

                            // echo $CreatedBy;
                            echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                            echo "<td style='text-align: left;'>" . Format::datetime($LastResponse) . "</td>";

                            echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                            // echo "<td style='text-align: right;'>$ClosedBy</td>";
                            echo "<td style='text-align: left;'>$userName</td>";
                            echo "<td  style='text-align: left;'>Normal</td>";
                            echo "<td  style='text-align: left;'>$dep</td>";
                            echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                            echo "<td $CurrentAgentsCellStyle style='text-align: left;'>$CurrentAgents</td>";
                            echo "<td>$CreatedBy</td>";
                            echo '</tr>';
                        }
                    }
                }
                //yaseen
$City= isset($_REQUEST["City"])? $_REQUEST["City"] :$_SESSION["City"];
$Vehicle= isset($_REQUEST["Vehicle"])? $_REQUEST["Vehicle"] :$_SESSION["Vehicle"];

//echo $Vehicle;
                if (isset($Status_id) && $Status_id != "" &&  $Status_id != "9")
                    $status = "=" . $Status_id;
                else
                    $status = "is null";

                if (isset($City) && $City != "")
                    $citystatus =  " and `ost_ticket`.`city`= '" . $City . "'";
                if (isset($Vehicle) && $Vehicle != "")
                    $VehicleQ =  " and `ost_thread_entry`.`body` like '%" . $Vehicle . "%'";

                if ($Getfalgs == '154' && $GetTeamId_ == '154') {

                    $CurrentStaffID = $thisstaff->getId();
                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`updated`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_thread_event`.`username` , `ost_ticket`.`topic_id` , `ost_department`.`name`,`ost_user`.`name`
                    FROM `ost_ticket` LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    LEFT JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id` 
                    LEFT JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` 
                    LEFT JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                    LEFT JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id` = `ost_thread`.`id` 
                    LEFT JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` 
                   
                    LEFT  join (select  id , ticket_id, (STATUS_id)STATUS_id , (staff_id)staff_id ,(user_id)user_id , (created)created from ost_connect_status c where  id in (select max(id) from ost_connect_status where ticket_id =c .ticket_id) ) ost_connect_status on `ost_connect_status`.`ticket_id` = `ost_ticket`.`ticket_id`
                    LEFT JOIN `ost_user` ON `ost_user`.`id`=`ost_ticket`.`user_id`
                    WHERE  `object_type` LIKE 'T'   and `ost_connect_status`.`status_id`  " . $status . " " . $citystatus . " " . $VehicleQ . " and `ost_ticket`.`team_id`=154 
                    GROUP BY `ost_ticket`.`ticket_id`
                    ORDER BY  `ost_ticket`.`created` DESC  ";
                    // LEFT JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id` 
                    // and `ost_ticket`.`created`>( CURDATE() - INTERVAL 30 DAY )
                   // echo  $GetUsersStaffTicketsQ;
                    if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                        while (list($ID, $Number, $UpdateDate, $Subject,  $Staff, $ClosedBy, $Topic_id, $DepName, $FromUser) = db_fetch_row($GetUsersStaffTickets_Res)) {
                            $StaffTeam = 'Not Found!';
                            $LastThreadEntry = Thread::getLastThreadEntry($ID);
                            $Body = "Not available!";
                            $LastResponse = "";
                            $get_D = "SELECT `body`  FROM `ost_thread_entry` 
                                INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
                                WHERE `ost_thread`.`object_id`=" . $ID . " AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'";
                            if (($get_D_Res = db_query($get_D)) && db_num_rows($get_D_Res)) {

                                $DD = db_fetch_row($get_D_Res);
                            }
                            $phone = explode(':', explode('نوع المركبة', explode('اسم السائق', explode('رقم الموبايل', $DD[0])[1])[0])[0])[1];
                            $Driver_Name = explode("اسم السائق", explode("رقم الموبايل", $DD[0])[0])[1];

                            $get_city = 'SELECT `city` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            if (($get_city_Res = db_query($get_city)) && db_num_rows($get_city_Res)) {

                                $city = db_fetch_row($get_city_Res);
                                $city_ = $city[0];
                                if ($city_ == null) {
                                    $city_ = "Not Found";
                                }
                            }
                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body);
                                $Body = substr($Body, 0, 200);
                                $LastResponse = $LastThreadEntry[1];
                                $poster = $LastThreadEntry[2];
                            }
                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $ID . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }
                            $CurrentAgents = "None";
                            $HelpTopicID = $Topic_id;

                            if ($TicketCurrentStep > 0) {
                                echo '<tr>';
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                // echo $CurrentAgentsID;
                             

                                if ($canManageTickets) { ?>
                                    <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                                    <?php }
                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                                if ($CurrentAgents == '') {
                                    $CurrentAgents = 'Contact IT!';
                                }
                                $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                    $teamId = $Res11[0];
                                }
                                $CreatedBy_ = "";
                                $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $ID . ");";

                                if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                    $Res = db_fetch_row($GetCreatedBy_Res);
                                    $CreatedBy_ = $Res[0];
                                }

                                echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                                echo "<td style='font-weight:bold;'><a style='display: inline;text-align: left;' href='/task/scp/tickets.php?id=$ID'>" . Format::datetime($LastResponse) . "</a></td>";
                                echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                                echo "<td style='text-align: left;'>$FromUser</td>";
                                echo "<td style='text-align: left;'>$city_</td>";
                                echo "<td style='text-align: left;'>$DepName</td>";
                                echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . $Body . "</td>";
                                echo "<td style='text-align: left;'> $phone</td>";
                                if(isset($Driver_Name))
                                echo "<td style='text-align: left;'> $Driver_Name</td>";
                            
                            else 
                                echo "<td style='text-align: left;'> </td>";
                               

                                echo '</tr>';
                            }
                        }
                    }
                }
                if ($_SESSION[$queue_key] == 6) {
                    $CurrentStaffID = $thisstaff->getId();
                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`closed`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) as namee,`ost_thread_event`.`username` , `ost_ticket`.`topic_id` ,`ost_department`.`name`,`ost_user`.`name` , `ost_help_topic_flow`.`staff_id` as orderrrr FROM `ost_ticket` 

                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    INNER JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id`
                    INNER JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` 
                    INNER JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id`
                    INNER JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` 
                    INNER JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id`
                    LEFT JOIN `ost_user` ON `ost_user`.`id`=`ost_ticket`.`user_id`
                    
                    
                    WHERE  `ost_ticket`.`closed` IS  NULL 
                     AND `object_type` LIKE 'T'
                    AND ( `ost_help_topic_flow`.`staff_id`=" . $CurrentStaffID . "     )
                    GROUP BY `ost_ticket`.`ticket_id`
                    ORDER BY FIELD(`ost_help_topic_flow`.`staff_id`, " . $thisstaff->getId() . ") DESC ";
                    // echo  $GetUsersStaffTicketsQ ;
                    if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                        while (list($ID, $Number, $ClosedDate, $Subject,  $Staff, $ClosedBy, $Topic_id, $dep, $userName) = db_fetch_row($GetUsersStaffTickets_Res)) {
                            $StaffTeam = 'Not Found!';




                            $LastThreadEntry = Thread::getLastThreadEntry($ID);
                            $Body = "Not available!";
                            $LastResponse = "";

                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body, '<br /><br/><br>');
                                $Body = substr($Body, 0, 200);
                                $LastResponse = $LastThreadEntry[1];
                                $poster = $LastThreadEntry[2];
                            }
                            // echo $poster ;
                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $ID . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }

                            // echo $TicketCurrentStep;

                            $CurrentAgents = "None";
                            $HelpTopicID = $Topic_id;

                            if ($TicketCurrentStep > 0) {
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                // echo $CurrentAgentsID;
                                if ($CurrentAgentsID != 0) {
                                    if ($CurrentAgentsID == $thisstaff->getId()) {
                                        echo '<tr>';

                                        if ($canManageTickets) { ?>
                                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                                <?php }
                                        $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                            $Res = db_fetch_row($GetCurrentAgents_Res);
                                            $CurrentAgents = $Res[0];
                                        }


                                        // else if ($TicketCurrentStep == -1) {
                                        //     if ($Closed !== '') {
                                        //         $CurrentAgents = "Ticket Closed";
                                        //     } else {
                                        //         if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                        //             $CurrentAgents = "Ticket Closed";
                                        //         else
                                        //             $CurrentAgents = "Ticket To Be Closed";
                                        //     }
                                        // }

                                        if ($CurrentAgents == '') {
                                            $CurrentAgents = 'Contact IT!';
                                        }
                                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                            $teamId = $Res11[0];
                                        }
                                        $CreatedBy_ = "";
                                        $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $ID . ");";

                                        if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                            $Res = db_fetch_row($GetCreatedBy_Res);
                                            $CreatedBy_ = $Res[0];
                                        }

                                        if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                            $CreatedBy = 'No Agent';
                                        } else {

                                            $CreatedBy = $CreatedBy_;
                                        }

                                        if ($Staff == '' || is_null($Staff)) {
                                            $Staff_ = $userName;
                                        } else {

                                            $Staff_ = $Staff;
                                        }
                                        if ($thisstaff->getId() == 6) {
                                            $CurrentAgentColor = "#c456ce";
                                        }
                                        $CurrentAgentColor = "#fd7f7f";
                                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                        } else {
                                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                                $CurrentAgentsCellStyle = 'style=""';
                                            }
                                            // elseif(in_array($teamId,$thisstaff->getTeams())){
                                            //     // echo $teamId;
                                            //         $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                                            //     }
                                            else {
                                                $CurrentAgentsCellStyle = 'style=""';
                                            }
                                        }

                                        // echo $CreatedBy;
                                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                                        echo "<td style='text-align: left;'>" . Format::datetime($LastResponse) . "</td>";

                                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                                        // echo "<td style='text-align: right;'>$ClosedBy</td>";
                                        echo "<td style='text-align: left;'>$userName</td>";
                                        echo "<td  style='text-align: left;'>Normal</td>";
                                        echo "<td  style='text-align: left;'>$dep</td>";
                                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                                        echo "<td $CurrentAgentsCellStyle style='text-align: left;'>$CurrentAgents</td>";
                                        echo "<td>$CreatedBy</td>";
                                        echo '</tr>';
                                    } else {
                                        continue;
                                    }
                                }
                                // else{
                                //     $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                //     if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                //         $Res = db_fetch_row($GetCurrentAgents_Res);
                                //         $CurrentAgents = $Res[0];
                                //     } 
                                // }
                            }
                        }
                    }
                }
                if ($_SESSION[$queue_key] == 8) {
                    $CurrentStaffID = $thisstaff->getId();
                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`closed`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_thread_event`.`username` , `ost_ticket`.`topic_id` FROM `ost_ticket` 

                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    INNER JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id`
                    INNER JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` 
                    INNER JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id`
                    INNER JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` 
                    INNER JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id`
                    
                    
                    
                    WHERE  `ost_ticket`.`closed` IS NOT NULL 
                     AND `object_type` LIKE 'T'
                    AND ( `ost_help_topic_flow`.`staff_id`=" . $CurrentStaffID . " OR  `ost_ticket`.`staff_id` = " . $thisstaff->getId() . "   OR `ost_ticket`.`team_id` IN ( " . implode(",", $thisstaff->getTeams()) . ") OR `ost_ticket`.`staff_id` = " . $thisstaff->getId() . " OR `ost_help_topic_flow`.`team_id` IN ( " . implode(",", $thisstaff->getTeams()) . ") )
                    AND `ost_ticket`.`closed`  BETWEEN NOW() - INTERVAL 34 DAY AND NOW()
                    GROUP BY `ost_ticket`.`ticket_id`
                    ORDER BY  `ost_ticket`.`closed` DESC";
                    // echo  $GetUsersStaffTicketsQ ;
                    if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                        while (list($ID, $Number, $ClosedDate, $Subject,  $Staff, $ClosedBy, $Topic_id) = db_fetch_row($GetUsersStaffTickets_Res)) {
                            $StaffTeam = 'Not Found!';



                            echo '<tr>';

                            if ($canManageTickets) { ?>
                                <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                            <?php }
                            $LastThreadEntry = Thread::getLastThreadEntry($ID);
                            $Body = "Not available!";
                            $LastResponse = "";

                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body, '<br /><br/><br>');
                                $Body = substr($Body, 0, 200);
                                $LastResponse = $LastThreadEntry[1];
                                $poster = $LastThreadEntry[2];
                            }

                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $ID . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }



                            $CurrentAgents = "None";
                            $HelpTopicID = $Topic_id;

                            if ($TicketCurrentStep > 0) {
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                // echo $CurrentAgentsID;
                                if ($CurrentAgentsID != 0) {
                                    $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                } else {
                                    $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                }
                            } else if ($TicketCurrentStep == -1) {
                                if ($Closed !== '') {
                                    $CurrentAgents = "Ticket Closed";
                                } else {
                                    if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                        $CurrentAgents = "Ticket Closed";
                                    else
                                        $CurrentAgents = "Ticket To Be Closed";
                                }
                            }

                            if ($CurrentAgents == '') {
                                $CurrentAgents = 'Contact IT!';
                            }
                            $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                            if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                $teamId = $Res11[0];
                            }
                            $CreatedBy_ = "";
                            $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $ID . ");";

                            if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                $Res = db_fetch_row($GetCreatedBy_Res);
                                $CreatedBy_ = $Res[0];
                            }

                            if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                $CreatedBy = 'No Agent';
                            } else {

                                $CreatedBy = $CreatedBy_;
                            }
                            if ($thisstaff->getId() == 6) {
                                $CurrentAgentColor = "#c456ce";
                            }
                            $CurrentAgentColor = "#fd7f7f";
                            if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                            }

                            // elseif(in_array($teamId,$thisstaff->getTeams())){
                            //         $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                            //     }
                            else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                } else {
                                    if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                        $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                    } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                        // echo $teamId;
                                        $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                    } else {
                                        $CurrentAgentsCellStyle = 'style=""';
                                    }
                                }
                            }
                            // echo $CreatedBy;
                            echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                            echo "<td style='font-weight:bold;text-align: left;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$ClosedDate</a></td>";

                            echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                            echo "<td style='text-align: left;'>$ClosedBy</td>";
                            echo "<td style='text-align: left;'>$Staff</td>";
                            echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                            echo "<td $CurrentAgentsCellStyle style='text-align: left;'>$CurrentAgents</td>";
                            echo "<td>$CreatedBy</td>";
                            // echo "<td>$CreatedBy</td>";
                            echo '</tr>';
                        }
                    }
                }

                if ($_SESSION[$queue_key] == 5 && count($result) > 0) {


                    $CurrentStaffID = $thisstaff->getId();
                    $GetUsersStaffTicketsQ = "(SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`updated`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_thread_event`.`username` , `ost_ticket`.`topic_id` , `ost_department`.`name`,`ost_user`.`name`,ost_ticket.created
              
                    FROM `ost_ticket` LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` LEFT JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id` LEFT JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` LEFT JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` LEFT JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` LEFT JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id` 
                    LEFT JOIN `ost_user` ON `ost_user`.`id`=`ost_ticket`.`user_id`
                    WHERE  `object_type` LIKE 'T' AND ost_ticket.`user_id` IN (SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=" . $thisstaff->getId() . ") AND `external_approval`=1 AND `closed` IS NULL 
                    GROUP BY `ost_ticket`.`ticket_id`)
                    union
                    (SELECT `ost_ticket`.`ticket_id`, `number`,`ost_ticket`.`updated`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_thread_event`.`username` , `ost_ticket`.`topic_id` , `ost_department`.`name`,`ost_user`.`name`,ost_ticket.created
              
                    FROM `ost_ticket` LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` LEFT JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id` LEFT JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` LEFT JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` LEFT JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id` LEFT JOIN `ost_help_topic_flow` ON `ost_help_topic_flow`.`help_topic_id`=`ost_ticket`.`topic_id` 
                    LEFT JOIN `ost_user` ON `ost_user`.`id`=`ost_ticket`.`user_id`
                    WHERE  `object_type` LIKE 'T'   AND `ost_help_topic_flow`.`staff_id`=" . $CurrentStaffID . "   AND `closed` IS NULL 
                    GROUP BY `ost_ticket`.`ticket_id`)
                    ORDER BY  `created` DESC LIMIT 200";


                    if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                        while (list($ID, $Number, $UpdateDate, $Subject,  $Staff, $ClosedBy, $Topic_id, $DepName, $FromUser) = db_fetch_row($GetUsersStaffTickets_Res)) {
                            $StaffTeam = 'Not Found!';


                            $DriverColor = "#F69462";
                            $ResturantColor = "#CCCCCC";

                            if ($Topic_id == 161) {  //161

                                $CellStyle = 'style="background-color:' . $DriverColor . ';color:#FFF"';
                            } elseif ($Topic_id == 160) {  //160

                                $CellStyle = 'style="background-color:' . $ResturantColor . ';color:#FFF"';
                            } else {
                                $CellStyle = 'style=""';
                            }

                            echo "<tr>";
                            if ($canManageTickets) { ?>
                                <td <?php echo $CellStyle; ?>><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                                <?php }
                            $LastThreadEntry = Thread::getLastThreadEntry($ID);
                            $Body = "Not available!";
                            $LastResponse = "";

                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body, '<br /><br/><br>');
                                $Body = substr($Body, 0, 200);
                                $LastResponse = $LastThreadEntry[1];
                                $poster = $LastThreadEntry[2];
                            }

                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $ID;
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $ID . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }



                            $CurrentAgents = "None";
                            $HelpTopicID = $Topic_id;

                            if ($TicketCurrentStep > 0) {
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                if ($CurrentAgentsID != 0) {
                                    $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                } else {
                                    $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                }
                            } else if ($TicketCurrentStep == -1) {
                                if ($Closed !== '') {
                                    $CurrentAgents = "Ticket Closed";
                                } else {
                                    if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                        $CurrentAgents = "Ticket Closed";
                                    else
                                        $CurrentAgents = "Ticket To Be Closed";
                                }
                            }

                            if ($CurrentAgents == '') {
                                $CurrentAgents = 'Contact IT!';
                            }
                            $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                            if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                $teamId = $Res11[0];
                            }
                            $CreatedBy_ = "";
                            $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $ID . ");";

                            if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                $Res = db_fetch_row($GetCreatedBy_Res);
                                $CreatedBy_ = $Res[0];
                            }

                            if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                $CreatedBy = 'No Agent';
                            } else {

                                $CreatedBy = $CreatedBy_;
                            }
                            if ($thisstaff->getId() == 6) {
                                $CurrentAgentColor = "#c456ce";
                            }
                            $CurrentAgentColor = "#fd7f7f";
                            if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                            }
                            // elseif(in_array($teamId,$thisstaff->getTeams())){
                            //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                            // }

                            else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                } else {
                                    if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                        $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                    }
                                    // elseif(in_array($teamId,$thisstaff->getTeams())){
                                    //     // echo $teamId;
                                    //         $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                                    //     }
                                    else {
                                        $CurrentAgentsCellStyle = 'style=""';
                                    }
                                }
                            }


                            $CreatedBy_ = "";
                            $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                            WHERE  `staff_id`  = (
                            SELECT `ost_thread_event`.`staff_id`
                            FROM `ost_thread_event` 
                            INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                            WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $T['ticket_id'] . ");";

                            if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                $Res = db_fetch_row($GetCreatedBy_Res);
                                $CreatedBy_ = $Res[0];
                            }

                            if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                $CreatedBy = 'No Agent';
                            } else {

                                $CreatedBy = $CreatedBy_;
                            }
                            $arr_s = array();
                            $sql_get_s = "select max(e.status),MAX(c.id) id from ost_connect_status c inner join ost_external_status e on e.id = c.status_id  WHERE `ticket_id`=".$ID." group by ticket_id having id = max(c.id)";
                            if (($sql_Res = db_query($sql_get_s)) && db_num_rows($sql_Res)) {
                                while (list($ID_) = db_fetch_row($sql_Res)) {

                                    array_push($arr_s, $ID_);
                                    // $ID = db_fetch_row($sql_Res);
                                }
                            }
                            if ($arr_s[0] != null) {
                                $Subject = $Subject . "  " . "                           [" . $arr_s[0] . "]";
                            }
                            $arr_city = array();
                            $sqlCity = "SELECT `body` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_type`='T' AND `ost_thread`.`object_id`=" . $ID . " AND `ost_thread_entry`.`type` LIKE 'M'";
                            if (($sqlCity_Res = db_query($sqlCity)) && db_num_rows($sqlCity_Res)) {
                                while (list($ID_) = db_fetch_row($sqlCity_Res)) {

                                    array_push($arr_city, $ID_);
                                    // $ID = db_fetch_row($sql_Res);
                                }
                            }
                            //explode("من قِبل", $arr_city[0])[0]
                            if ($Topic_id == 161 && explode("المدينة", explode("من قِبل", $arr_city[0])[0])[1] != null) {
                                $Subject = $Subject . "  " . "                           [" . explode(":", explode("المدينة", explode("من قِبل", $arr_city[0])[0])[1])[1] . "]";
                            }
                            $arr_users = array();
                            $sql_get_users = "SELECT u.username from ost_help_topic_flow f inner join ost_user_account u on u.user_id =f.user_id  where ticket_id =" . $ID;
                            if (($sql_Res1 = db_query($sql_get_users)) && db_num_rows($sql_Res1)) {
                                while (list($names) = db_fetch_row($sql_Res1)) {
                                    array_push($arr_users, $names);
                                }
                            }
                            if (isset($arr_users[0])) {
                                $Subject = $Subject . " user 1 :" . "$arr_users[0]" . " user 2 :" . "$arr_users[1]";
                            }

                            echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                            echo "<td style='font-weight:bold;'><a style='display: inline;text-align: left;' href='/task/scp/tickets.php?id=$ID'>" . Format::datetime($LastResponse) . "</a></td>";

                            echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                            echo "<td style='text-align: left;'>$FromUser</td>";
                            echo "<td style='text-align: left;'>" . 'high' . "</td>";
                            echo "<td style='text-align: left;'>$DepName</td>";
                            echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                            echo "<td $CurrentAgentsCellStyle style='text-align: left;'>$CurrentAgents</td>";
                            echo "<td style='text-align: left;'>$CreatedBy</td>";
                            // echo "<td>$CreatedBy</td>";
                            echo '</tr>';
                        }
                    }
                } else {
                    if ($_SESSION[$queue_key] != 7) {
                        foreach ($tickets as $T) {

                            if ($_SESSION[$queue_key] == 6) {
                                break;
                            }
                            if ($_SESSION[$queue_key] == 1) {
                                break;
                            }
                            if ($_SESSION[$queue_key] == 2) {
                                break;
                            }
                            if ($_SESSION[$queue_key] == 3) {
                                break;
                            }


                            // Close open tickets that have been like this for more that 7 days
                            $TicketCreatedDate = Ticket::GetCreatedDateById($T['ticket_id']);

                            if ($TicketCreatedDate != '') {
                                $TicketCreatedDate = strtotime($TicketCreatedDate);
                                $Now = time();
                                $DateDiff = $Now - $TicketCreatedDate;
                                $DiffInDays = round($DateDiff / (60 * 60 * 24));

                                $Ticket = Ticket::GetTicketById($T['ticket_id']);
                                $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $T['ticket_id'];
                                $HelpTopicID = 0;

                                if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                    $Res = db_fetch_row($HelpTopicConstraint_Res);
                                    $HelpTopicID = $Res[0];
                                }
                                $DriverColor = "#F69462";
                                $ResturantColor = "#CCCCCC";

                                if ($HelpTopicID == 161) {  //161

                                    $CellStyle = 'style="background-color:' . $DriverColor . ';color:#FFF"';
                                } elseif ($HelpTopicID == 160) {  //160

                                    $CellStyle = 'style="background-color:' . $ResturantColor . ';color:#FFF"';
                                } else {
                                    $CellStyle = 'style=""';
                                }
                                if ($Ticket->ht['status_id'] == 1) {
                                    $ClosedStatus = TicketStatus::lookup(3);

                                    $errors = array();

                                    // if ($HelpTopicID == 54){
                                    //     if ($DiffInDays > 2) {
                                    //         $Ticket->setStatus($ClosedStatus, '', $errors, false);
                                    //     }
                                    // }
                                    // if ($HelpTopicID == 133){
                                    //     if ($DiffInDays > 2) {
                                    //         $Ticket->setStatus($ClosedStatus, '', $errors, false);
                                    //     }
                                    // }
                                    // if ($HelpTopicID == 134){
                                    //     if ($DiffInDays > 2) {
                                    //         $Ticket->setStatus($ClosedStatus, '', $errors, false);
                                    //     }
                                    // }
                                    // if ($DiffInDays > 11) {
                                    //     $Ticket->setStatus($ClosedStatus, '', $errors, false);
                                    // }
                                }
                            }

                            $LastThreadEntry = Thread::getLastThreadEntry($T['ticket_id']);
                            $Body = "Not available!";
                            $arr_s = array();
                            $sql_get_s = "select max(e.status),MAX(c.id) id from ost_connect_status c inner join ost_external_status e on e.id = c.status_id  WHERE `ticket_id`=".$T['ticket_id']." group by ticket_id having id = max(c.id)";

                            if (($sql_Res = db_query($sql_get_s)) && db_num_rows($sql_Res)) {
                                while (list($ID_) = db_fetch_row($sql_Res)) {

                                    array_push($arr_s, $ID_);
                                    // $ID = db_fetch_row($sql_Res);
                                }
                            }
                            if ($arr_s[0] != null) {
                                $T['cdata__subject'] = $T['cdata__subject'] . "  " . "[" . $arr_s[0] . "]";
                            }

                            $arr_city = array();
                            $sqlCity = "SELECT `body` FROM `ost_thread_entry` INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id` WHERE `ost_thread`.`object_type`='T' AND `ost_thread`.`object_id`=" . $T['ticket_id'] . " AND `ost_thread_entry`.`type` LIKE 'M'";
                            if (($sqlCity_Res = db_query($sqlCity)) && db_num_rows($sqlCity_Res)) {
                                while (list($ID_) = db_fetch_row($sqlCity_Res)) {

                                    array_push($arr_city, $ID_);
                                }
                            }
                            //explode("من قِبل", $arr_city[0])[0]
                            if ($HelpTopicID == 111 && explode("المدينة", explode("من قِبل", $arr_city[0])[0])[1] != null) {
                                $T['cdata__subject'] = $T['cdata__subject'] . "" . "[" . explode(":", explode("المدينة", explode("من قِبل", $arr_city[0])[0])[1])[1] . "]";
                            }
                            if ($LastThreadEntry != null) {
                                $Body = $LastThreadEntry[0];
                                $Body = strip_tags($Body, '<br /><br/><br>');
                                $poster = $LastThreadEntry[2];
                                $LastResponse = $LastThreadEntry[1];
                            }

                            if (strpos($Body, '<br />') == true || strpos($Body, '<br>') == true || strpos($Body, '<br/>') == true)
                                $Body = substr($Body, 0, strpos($Body, "<br />"));

                            $HelpTopicConstraint_Q = 'SELECT `topic_id` FROM `ost_ticket` WHERE `ticket_id` = ' . $T['ticket_id'];
                            $HelpTopicID = 0;
                            $EndOfYearInventoryCheckup = false; // Change to 'true' to enter the end of the year inventory checkup mode

                            if (($HelpTopicConstraint_Res = db_query($HelpTopicConstraint_Q)) && db_num_rows($HelpTopicConstraint_Res)) {
                                $Res = db_fetch_row($HelpTopicConstraint_Res);
                                $HelpTopicID = $Res[0];
                            }

                            $TicketCurrentStep = 0;
                            $GetTicketCurrentStepQ = "SELECT `current_step` FROM `ost_ticket` WHERE `ticket_id` = " . $T['ticket_id'] . ";";

                            if (($GetTicketCurrentStep_Res = db_query($GetTicketCurrentStepQ)) && db_affected_rows($GetTicketCurrentStep_Res)) {
                                $Res = db_fetch_row($GetTicketCurrentStep_Res);
                                $TicketCurrentStep = $Res[0];
                            }

                            $CurrentAgents = "None";

                            if ($TicketCurrentStep > 0) {
                                $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                    $Res_staff = db_fetch_row($checkIfstaff_Res);
                                    $CurrentAgentsID = $Res_staff[0];
                                }
                                // echo $CurrentAgentsID;
                                if ($CurrentAgentsID != 0) {
                                    $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
                                } else {
                                    $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                    if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                        $Res = db_fetch_row($GetCurrentAgents_Res);
                                        $CurrentAgents = $Res[0];
                                    }
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
                            $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                            if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                $teamId = $Res11[0];
                            }
                            if (($EndOfYearInventoryCheckup && $HelpTopicID == 84) || !$EndOfYearInventoryCheckup) {
                                $CurrentAgentColor = "#fd7f7f";

                                if ($thisstaff->getId() == 6) {
                                    $CurrentAgentColor = "#c456ce";
                                }

                                if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                }
                                // elseif(in_array($teamId,$thisstaff->getTeams())){
                                //         $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                //     }
                                else {
                                    if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                        $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                    } else {
                                        if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                            $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                        }
                                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                                        //     // echo $teamId;
                                        //         $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                                        //     }
                                        else {
                                            $CurrentAgentsCellStyle = 'style=""';
                                        }
                                    }
                                }

                                if (strpos($CurrentAgents, "Store") !== false)
                                    $CurrentAgents = "Store Team";

                                echo '<tr>';

                                if ($canManageTickets) { ?>
                                    <td <?php echo $CellStyle; ?>><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $T['ticket_id']; ?>" /></td>
                            <?php }

                                foreach ($columns as $C) {
                                    list($contents, $styles) = $C->render($T);
                                    if ($style = $styles ? 'style="' . $styles . '"' : '') {
                                        // if($arr_s[0] != null)
                                        // echo "<td $style><div $style>$contents  $arr_s[0]</div></td>";
                                        // else
                                        echo "<td $style><div $style>$contents</div></td>";
                                    } else {
                                        echo "<td>$contents</td>";
                                    }
                                }
                                $CreatedBy_ = "";
                                $GetCreatedBy = "SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff`
                        WHERE  `staff_id`  = (
                        SELECT `ost_thread_event`.`staff_id`
                        FROM `ost_thread_event` 
                        INNER JOIN `ost_thread` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id` 
                        WHERE `ost_thread_event`.`event_id` = 1  AND `object_type` LIKE 'T' AND `ost_thread`.`object_id` = " . $T['ticket_id'] . ");";

                                if (($GetCreatedBy_Res = db_query($GetCreatedBy)) && db_num_rows($GetCreatedBy_Res)) {

                                    $Res = db_fetch_row($GetCreatedBy_Res);
                                    $CreatedBy_ = $Res[0];
                                }

                                if ($CreatedBy_ == '' || is_null($CreatedBy_)) {
                                    $CreatedBy = 'No Agent';
                                } else {

                                    $CreatedBy = $CreatedBy_;
                                }
                                //yaseen
                                $Res2 = array();
                                $Res3 = array();
                                $GetTopicIdEx = "SELECT  ost_ticket.ticket_id FROM `ost_ticket` LEFT join ost_help_topic_flow on ost_help_topic_flow.help_topic_id=ost_ticket.topic_id where ost_help_topic_flow.help_topic_id is Null and ost_ticket.ticket_id=" . $T['ticket_id'] . ";";
                                if (($GetTopicIdEx_Res = db_query($GetTopicIdEx)) && db_num_rows($GetTopicIdEx_Res)) {
                                    $Res2 = db_fetch_row($GetTopicIdEx_Res);
                                    $GetTopicIdEx_ = $Res2[0];
                                }

                                $GetAssignTo = "select staff_id from ost_ticket where ticket_id=" . $T['ticket_id'] . "";
                                if (($GetAssignTo_Res = db_query($GetAssignTo)) && db_num_rows($GetAssignTo_Res)) {
                                    $Res3 = db_fetch_row($GetAssignTo_Res);
                                    $GetAssignTo_ = $Res3[0];
                                }

                                if ($GetAssignTo_ == $thisstaff->getId() && ($GetTopicIdEx_ != null || $GetTopicIdEx_ != "")) {
                                    if (!isset($CurrentAgentsCellStyle)) {
                                        $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                    }
                                }

                                echo "<td><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                                echo "<td $CurrentAgentsCellStyle>$CurrentAgents</td>";
                                echo "<td>$CreatedBy</td>";
                                echo '</tr>';
                            }
                        }
                    }
                }
            } else if ($_REQUEST['a'] == 'afms') {
                $CurrentStaffID = $thisstaff->getId();
                if (isset($_GET["sort"])) {
                    //Sort By Number    
                    if ($_GET["sort"] == "ID") {
                        if ($_GET["order"] == "DESC") {
                            $OrderBy = "   `ost_ticket`.`number`  DESC";
                        } elseif ($_GET["order"] == "ASC") {
                            $OrderBy = "  `ost_ticket`.`number`  ASC";
                        }
                    }
                    //Subject
                    if ($_GET["sort"] == "Subject") {
                        if ($_GET["order"] == "DESC") {
                            $OrderBy = "   `subject`  DESC";
                        } elseif ($_GET["order"] == "ASC") {
                            $OrderBy = "  `subject`  ASC";
                        }
                    }
                    //From
                    if ($_GET["sort"] == "From") {
                        if ($_GET["order"] == "DESC") {
                            $OrderBy = "   `firstname`  DESC";
                        } elseif ($_GET["order"] == "ASC") {
                            $OrderBy = "  `firstname`  ASC";
                        }
                    }
                } else {
                    $OrderBy = "  `ost_ticket`.`created`  ASC";
                }


                //yaseen edit this query
                if (isset($_POST['end_date'])) {
                    $ToDate = explode('.', str_replace('T', ' ', $_POST['end_date']))[0];
                    $your_ToDate = strtotime("1 day", strtotime($ToDate));
                    $new_ToDate = date("Y-m-d", $your_ToDate);
                    $ToDate = explode(' ', $new_ToDate)[0] . " " . "21:00:00";
                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `ost_ticket`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                    FROM `ost_ticket` 
                    INNER JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    INNER JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_ticket`.`ticket_id`
                    LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
                    WHERE  `ost_ticket`.`closed` IS NULL  AND  `ost_agent_users_tickets`.`staff_id` =" . $CurrentStaffID . "  AND `ost_ticket`.`created`  BETWEEN  '" . $ToDate . "'  AND NOW()
                        UNION
                    SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `ost_ticket`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                    FROM `ost_ticket` 
                    INNER JOIN `ost_reservation` ON `ost_ticket`.`ticket_id` = `ost_reservation`.`ticket_id`
                    INNER JOIN `ost_agent_users_tickets` ON `ost_reservation`.`user_id` = `ost_agent_users_tickets`.`user_id`
                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                    INNER JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                    inner join (select max(id) id , ticket_id , max(STATUS_id) STATUS_id , max(staff_id)staff_id ,MAX(user_id)user_id , max(created)created from ost_connect_status  group by ticket_id having id = max(id)) ost_connect_status on `ost_ticket`.`ticket_id` = `ost_connect_status`.`ticket_id`
                    LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                    LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_ticket`.`ticket_id`
                    LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
                    WHERE  `ost_ticket`.`closed` IS NULL  AND  `ost_agent_users_tickets`.`staff_id` =" . $CurrentStaffID . " and ost_connect_status.status_id=4
                    ";
                } else {
                    $GetUsersStaffTicketsQ = "(SELECT `T1`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `T1`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`T1`.`updated` ,T1.created
                    FROM `ost_ticket`  as T1
                    INNER JOIN `ost_agent_users_tickets` ON `T1`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `T1`.`ticket_id` 
                    INNER JOIN `ost_user` ON `ost_user`.`id` = `T1`.`user_id` 
                    LEFT JOIN `ost_staff` ON `T1`.`staff_id` = `ost_staff`.`staff_id` 
                    LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`T1`.`ticket_id`
                    LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
                    WHERE `T1`.`closed` IS NULL  AND  `ost_agent_users_tickets`.`staff_id` = " . $CurrentStaffID . "   )
                     UNION
                  (  SELECT `T2`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `T2`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`T2`.`updated` ,T2.created
                    FROM `ost_ticket`  as T2
                    INNER JOIN `ost_reservation` ON `T2`.`ticket_id` = `ost_reservation`.`ticket_id`
                    INNER JOIN `ost_agent_users_tickets` ON `ost_reservation`.`user_id` = `ost_agent_users_tickets`.`user_id`
                    INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `T2`.`ticket_id` 
                    INNER JOIN `ost_user` ON `ost_user`.`id` = `T2`.`user_id` 
                    inner join (select max(id) id , ticket_id , max(STATUS_id) STATUS_id , max(staff_id)staff_id ,MAX(user_id)user_id , max(created)created from ost_connect_status  group by ticket_id having id = max(id)) ost_connect_status on `T2`.`ticket_id` = `ost_connect_status`.`ticket_id`
                    LEFT JOIN `ost_staff` ON `T2`.`staff_id` = `ost_staff`.`staff_id` 
                    LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`T2`.`ticket_id`
                    LEFT JOIN `ost_thread_collaborator` ON `ost_thread_collaborator`.`thread_id`=`ost_thread`.`id`
                    WHERE  `T2`.`closed` IS NULL  AND  `ost_agent_users_tickets`.`staff_id` = " . $CurrentStaffID . " and ost_connect_status.status_id=4)
                    UNION
                  (  SELECT DISTINCT `T3`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `T3`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`T3`.`updated` ,T3.created
                        FROM `ost_ticket` as T3
                        INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `T3`.`ticket_id` 
                        INNER JOIN `ost_user` ON `ost_user`.`id` = `T3`.`user_id` 
                        LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`T3`.`ticket_id`
                        INNER JOIN `ost_help_topic_flow` ON `T3`.`ticket_id`=`ost_help_topic_flow`.`ticket_id`  and `T3`.`current_step`=`ost_help_topic_flow`.`step_number`
                        INNER JOIN `ost_agent_users_tickets` ON `ost_help_topic_flow`.`user_id`=`ost_agent_users_tickets`.`user_id`
                         LEFT JOIN `ost_staff` ON `ost_agent_users_tickets`.`staff_id` = `ost_staff`.`staff_id`
                        WHERE  `T3`.`closed` IS NULL and ost_agent_users_tickets.staff_id = " . $CurrentStaffID . "
                        ) 
                        order by created DESC
                       ";
                }

                // echo $GetUsersStaffTicketsQ;
                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed) = db_fetch_row($GetUsersStaffTickets_Res)) {
                        $StaffTeam = 'Not Found!';

                        if ($Staff != '') {
                            $StaffTeam = $Staff;
                        } else {
                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                $Res = db_fetch_row($GetTeam_Res);
                                $StaffTeam = $Res[0];
                            }
                        }

                        echo '<tr>';

                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                        <?php }
                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                        $Body = "Not available!";
                        $LastResponse = "";

                        if ($LastThreadEntry != null) {
                            $Body = $LastThreadEntry[0];
                            $Body = strip_tags($Body, '<br /><br/><br>');
                            $Body = substr($Body, 0, 200);
                            $LastResponse = $LastThreadEntry[1];
                            $poster = $LastThreadEntry[2];
                        }

                        $CurrentAgents = "None";
                        $HelpTopicID = $Topic_id;

                        if ($TicketCurrentStep > 0) {
                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                $CurrentAgentsID = $Res_staff[0];
                            }
                            // echo $CurrentAgentsID;
                            if ($CurrentAgentsID != 0) {
                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            } else {
                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            }
                        } else if ($TicketCurrentStep == -1) {
                            if ($Closed !== '') {
                                $CurrentAgents = "Ticket Closed";
                            } else {
                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                    $CurrentAgents = "Ticket Closed";
                                else
                                    $CurrentAgents = "Ticket To Be Closed";
                            }
                        }

                        if ($CurrentAgents == '') {
                            $CurrentAgents = 'Contact IT!';
                        }
                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                            $teamId = $Res11[0];
                        }
                        $CurrentAgentColor = "#fd7f7f";

                        if ($thisstaff->getId() == 6) {
                            $CurrentAgentColor = "#c456ce";
                        }

                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                        }
                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                        //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                        // }
                        else {
                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                            } else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                    // echo $teamId;
                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                } else {
                                    $CurrentAgentsCellStyle = 'style=""';
                                }
                            }
                        }
                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                        echo "<td style='text-align: left;'>$Username</td>";
                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                        echo "<td style='text-align: left;'>$LastResponse</td>";
                        echo "<td style='text-align: left;' $CurrentAgentsCellStyle>$CurrentAgents</td>";
                        echo '</tr>';
                    }
                }
            }
            //rrr
            else if ($_REQUEST['a'] == 'sbt') {
                $CurrentStaffID = $thisstaff->getId();
                if (isset($_GET["sort"])) {
                    //Sort By Number    
                    if ($_GET["sort"] == "Type") {
                        if ($_GET["order"] == "DESC") {
                            $OrderBy = "    `ost_ticket`.`ticket_type`  DESC";
                        } elseif ($_GET["order"] == "ASC") {
                            $OrderBy = "   `ost_ticket`.`ticket_type`  ASC";
                        }
                    }
                } else {
                    $OrderBy = "   `ost_ticket`.`ticket_type`   ASC";
                }



                $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `ost_ticket`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` ,
    CASE  `ost_ticket`.`ticket_type`
          WHEN 'D' THEN 'Driver'
          WHEN 'R' THEN 'Resturant'
          END as 'Type'
    
                        FROM `ost_ticket` 
                        
                        INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                        INNER JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                        LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                        LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_ticket`.`ticket_id`
                       
                        WHERE  `ost_ticket`.`closed` IS NULL
                        and `ost_ticket`.`topic_id` IN (161,160,159)   
                       
    ORDER BY  " . $OrderBy;
                // echo $GetUsersStaffTicketsQ;
                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed, $t, $Type) = db_fetch_row($GetUsersStaffTickets_Res)) {
                        $StaffTeam = 'Not Found!';

                        if ($Staff != '') {
                            $StaffTeam = $Staff;
                        } else {
                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                $Res = db_fetch_row($GetTeam_Res);
                                $StaffTeam = $Res[0];
                            }
                        }

                        echo '<tr>';

                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                        <?php }
                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                        $Body = "Not available!";
                        $LastResponse = "";

                        if ($LastThreadEntry != null) {
                            $Body = $LastThreadEntry[0];
                            $Body = strip_tags($Body, '<br /><br/><br>');
                            $Body = substr($Body, 0, 200);
                            $LastResponse = $LastThreadEntry[1];
                            $poster = $LastThreadEntry[2];
                        }

                        $CurrentAgents = "None";
                        $HelpTopicID = $Topic_id;

                        if ($TicketCurrentStep > 0) {
                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                $CurrentAgentsID = $Res_staff[0];
                            }
                            // echo $CurrentAgentsID;
                            if ($CurrentAgentsID != 0) {
                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            } else {
                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            }
                        } else if ($TicketCurrentStep == -1) {
                            if ($Closed !== '') {
                                $CurrentAgents = "Ticket Closed";
                            } else {
                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                    $CurrentAgents = "Ticket Closed";
                                else
                                    $CurrentAgents = "Ticket To Be Closed";
                            }
                        }

                        if ($CurrentAgents == '') {
                            $CurrentAgents = 'Contact IT!';
                        }
                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                            $teamId = $Res11[0];
                        }
                        $CurrentAgentColor = "#fd7f7f";

                        if ($thisstaff->getId() == 6) {
                            $CurrentAgentColor = "#c456ce";
                        }

                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                        }
                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                        //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                        // }
                        else {
                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                            } else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                    // echo $teamId;
                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                } else {
                                    $CurrentAgentsCellStyle = 'style=""';
                                }
                            }
                        }
                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                        echo "<td style='text-align: left;'>$Username</td>";
                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                        echo "<td style='text-align: left;'>$LastResponse</td>";
                        echo "<td style='text-align: left;' $CurrentAgentsCellStyle>$CurrentAgents</td>";
                        echo "<td style='text-align: left;'>$Type</td>";
                        echo '</tr>';
                    }
                }
            }

            ///rrr

            //rrr
            else if ($_REQUEST['a'] == 'sbc') {
                $CurrentStaffID = $thisstaff->getId();
                if (isset($_GET["sort"])) {
                    //Sort By Number    
                    if ($_GET["sort"] == "City") {
                        if ($_GET["order"] == "DESC") {
                            $OrderBy = "    `ost_ticket`.`city`   DESC";
                        } elseif ($_GET["order"] == "ASC") {
                            $OrderBy = "   `ost_ticket`.`city`  ASC";
                        }
                    }
                } else {
                    $OrderBy = "   `ost_ticket`.`city`    ASC";
                }



                $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `ost_ticket`.`team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` ,
    `ost_ticket`.`city`
        
  
                      FROM `ost_ticket` 
                      
                      INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                      INNER JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                      LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                      LEFT JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_ticket`.`ticket_id`
                     
                      WHERE  `ost_ticket`.`closed` IS NULL
                      and `ost_ticket`.`topic_id` = 161  
                     
  ORDER BY   " . $OrderBy;
                // echo $GetUsersStaffTicketsQ;
                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed, $t, $Type) = db_fetch_row($GetUsersStaffTickets_Res)) {
                        $StaffTeam = 'Not Found!';

                        if ($Staff != '') {
                            $StaffTeam = $Staff;
                        } else {
                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                $Res = db_fetch_row($GetTeam_Res);
                                $StaffTeam = $Res[0];
                            }
                        }

                        echo '<tr>';

                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                        <?php }
                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                        $Body = "Not available!";
                        $LastResponse = "";

                        if ($LastThreadEntry != null) {
                            $Body = $LastThreadEntry[0];
                            $Body = strip_tags($Body, '<br /><br/><br>');
                            $Body = substr($Body, 0, 200);
                            $LastResponse = $LastThreadEntry[1];
                            $poster = $LastThreadEntry[2];
                        }

                        $CurrentAgents = "None";
                        $HelpTopicID = $Topic_id;

                        if ($TicketCurrentStep > 0) {
                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                $CurrentAgentsID = $Res_staff[0];
                            }
                            // echo $CurrentAgentsID;
                            if ($CurrentAgentsID != 0) {
                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            } else {
                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            }
                        } else if ($TicketCurrentStep == -1) {
                            if ($Closed !== '') {
                                $CurrentAgents = "Ticket Closed";
                            } else {
                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                    $CurrentAgents = "Ticket Closed";
                                else
                                    $CurrentAgents = "Ticket To Be Closed";
                            }
                        }

                        if ($CurrentAgents == '') {
                            $CurrentAgents = 'Contact IT!';
                        }
                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                            $teamId = $Res11[0];
                        }
                        $CurrentAgentColor = "#fd7f7f";

                        if ($thisstaff->getId() == 6) {
                            $CurrentAgentColor = "#c456ce";
                        }

                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                        }
                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                        //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                        // }
                        else {
                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                            } else {
                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                    // echo $teamId;
                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                } else {
                                    $CurrentAgentsCellStyle = 'style=""';
                                }
                            }
                        }
                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                        echo "<td style='text-align: left;'>$Username</td>";
                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                        echo "<td style='text-align: left;'>$LastResponse</td>";
                        echo "<td style='text-align: left;' $CurrentAgentsCellStyle>$CurrentAgents</td>";
                        echo "<td style='text-align: left;'>$Type</td>";
                        echo '</tr>';
                    }
                }
            }

            ///rrr
            else if ($_REQUEST['a'] == 'cbs') {
                $CurrentStaffID = $thisstaff->getId();
                $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `ost_ticket__cdata`.`subject`, CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_ticket`.`topic_id` ,`ost_ticket`.`current_step` ,`ost_ticket`.`closed` 
                FROM `ost_ticket` INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id`
                 LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                 INNER JOIN `ost_department` ON `ost_ticket`.`dept_id` = `ost_department`.`id` 
                 INNER JOIN `ost_thread` ON `ost_thread`.`object_id` = `ost_ticket`.`ticket_id` 
                 INNER JOIN `ost_thread_event` ON `ost_thread_event`.`thread_id` = `ost_thread`.`id`
                INNER JOIN `ost_help_topic` ON `ost_help_topic`.`topic_id`= `ost_ticket`.`topic_id`
                
                
                WHERE `ost_ticket`.`topic_id`  IN (SELECT `ost_ticket`.`topic_id` FROM `ost_ticket`
                WHERE `staff_id` IN (SELECT `staff_id` FROM `ost_staff` INNER JOIN `ost_department` ON `ost_staff`.`dept_id` = `ost_department`.`id` WHERE `ost_department`.`manager_id`=" . $CurrentStaffID . " AND `staff_id` <> 0) AND `staff_id` IN (SELECT `staff_id` FROM `ost_team_member` WHERE `team_id` IN ( SELECT `team_id` FROM `ost_team` WHERE `lead_id`=" . $CurrentStaffID . " AND `staff_id` <> 0 ) )) AND DATEDIFF( SUBSTRING_INDEX(CURRENT_TIMESTAMP, ' ', 1),SUBSTRING_INDEX(`ost_thread_event`.`timestamp`, ' ', 1)) <= 7 AND `ost_ticket`.`closed` IS NOT NULL AND `ost_thread_event`.`username` = 'SYSTEM' AND `object_type` LIKE 'T' AND `ost_thread_event`.`event_id`=2";
                // echo $GetUsersStaffTicketsQ ;
                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed) = db_fetch_row($GetUsersStaffTickets_Res)) {
                        $StaffTeam = 'Not Found!';

                        if ($Staff != '') {
                            $StaffTeam = $Staff;
                        } else {
                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                $Res = db_fetch_row($GetTeam_Res);
                                $StaffTeam = $Res[0];
                            }
                        }

                        echo '<tr>';

                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                    <?php }
                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                        $Body = "Not available!";
                        $LastResponse = "";

                        if ($LastThreadEntry != null) {
                            $Body = $LastThreadEntry[0];
                            $Body = strip_tags($Body, '<br /><br/><br>');
                            $Body = substr($Body, 0, 200);
                            $LastResponse = $LastThreadEntry[1];
                            $poster = $LastThreadEntry[2];
                        }




                        $CurrentAgents = "None";
                        $HelpTopicID = $Topic_id;

                        if ($TicketCurrentStep > 0) {
                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                $CurrentAgentsID = $Res_staff[0];
                            }
                            // echo $CurrentAgentsID;
                            if ($CurrentAgentsID != 0) {
                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            } else {
                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                    $CurrentAgents = $Res[0];
                                }
                            }
                        } else if ($TicketCurrentStep == -1) {
                            if ($Closed !== '') {
                                $CurrentAgents = "Ticket Closed";
                            } else {
                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                    $CurrentAgents = "Ticket Closed";
                                else
                                    $CurrentAgents = "Ticket To Be Closed";
                            }
                        }

                        if ($CurrentAgents == '') {
                            $CurrentAgents = 'Contact IT!';
                        }
                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                            $teamId = $Res11[0];
                        }
                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                        // echo "<td style='text-align: right;'>$Username</td>";
                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                        echo "<td style='text-align: left;'>$LastResponse</td>";
                        // echo "<td>$CurrentAgents</td>";
                        echo '</tr>';
                    }
                }
            } else if ($_REQUEST['a'] == 'Msearch') {
                if (empty($_POST["searchC"])) {
                    ?> <div id="msg_error"><?php echo "Please enter data to search for"; ?></div><?php
                                                                                                    die();
                                                                                                } else {
                                                                                                    // // echo $_POST["searchC"];
                                                                                                    // if(isset($_POST['gets'])){
                                                                                                    //     echo $_POST['gets'];
                                                                                                    // }
                                                                                                    // else{
                                                                                                    //    echo  $_POST['gets'];
                                                                                                    // }


                                                                                                }
                                                                                                $CurrentStaffID = $thisstaff->getId();
                                                                                                if ($_POST['gets'] == 0) {

                                                                                                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                FROM `ost_ticket` 
                LEFT JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                LEFT JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                WHERE `ost_ticket`.`ticket_id`=" . $_POST["searchC"];
                                                                                                } elseif ($_POST['gets'] == 1) {
                                                                                                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                FROM `ost_ticket` 
                LEFT JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                LEFT JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                WHERE `ost_ticket`.`number` =" . $_POST["searchC"];
                                                                                                } elseif ($_POST['gets'] == 2) {
                                                                                                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                FROM `ost_ticket` 
                LEFT JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                LEFT JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                WHERE `ost_ticket__cdata`.`subject` LIKE  '%" . $_POST["searchC"] . "%'   ";
                                                                                                } elseif ($_POST['gets'] == 3) {
                                                                                                    $GetUsersStaffTicketsQ = "SELECT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
                FROM `ost_ticket` 
                INNER JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
                INNER JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
                INNER JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
                LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
                INNER JOIN `ost_thread` ON `ost_thread`.`object_id`=`ost_ticket`.`ticket_id`
                INNER JOIN `ost_thread_entry` ON `ost_thread_entry`.`thread_id`=`ost_thread`.`id`
                WHERE `ost_thread_entry`.`body` LIKE '%" . $_POST["searchC"] . "%'  AND `ost_thread`.`object_type`='T' AND `ost_thread_entry`.`type`='M'   ";
                                                                                                }

                                                                                                // echo $GetUsersStaffTicketsQ;
                                                                                                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                                                                                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed) = db_fetch_row($GetUsersStaffTickets_Res)) {
                                                                                                        $StaffTeam = 'Not Found!';

                                                                                                        if ($Staff != '') {
                                                                                                            $StaffTeam = $Staff;
                                                                                                        } else {
                                                                                                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                                                                                                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                                                                                                $Res = db_fetch_row($GetTeam_Res);
                                                                                                                $StaffTeam = $Res[0];
                                                                                                            }
                                                                                                        }

                                                                                                        echo '<tr>';

                                                                                                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
                        <?php }
                                                                                                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                                                                                                        $Body = "Not available!";
                                                                                                        $LastResponse = "";

                                                                                                        if ($LastThreadEntry != null) {
                                                                                                            $Body = $LastThreadEntry[0];
                                                                                                            $Body = strip_tags($Body, '<br /><br/><br>');
                                                                                                            $Body = substr($Body, 0, 200);
                                                                                                            $LastResponse = $LastThreadEntry[1];
                                                                                                            $poster = $LastThreadEntry[2];
                                                                                                        }

                                                                                                        $CurrentAgents = "None";
                                                                                                        $HelpTopicID = $Topic_id;

                                                                                                        if ($TicketCurrentStep > 0) {
                                                                                                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                                                                                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                                                                                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                                                                                                $CurrentAgentsID = $Res_staff[0];
                                                                                                            }
                                                                                                            // echo $CurrentAgentsID;
                                                                                                            if ($CurrentAgentsID != 0) {
                                                                                                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                                                                                                    $CurrentAgents = $Res[0];
                                                                                                                }
                                                                                                            } else {
                                                                                                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                                                                                                    $CurrentAgents = $Res[0];
                                                                                                                }
                                                                                                            }
                                                                                                        } else if ($TicketCurrentStep == -1) {
                                                                                                            if ($Closed !== '') {
                                                                                                                $CurrentAgents = "Ticket Closed";
                                                                                                            } else {
                                                                                                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                                                                                                    $CurrentAgents = "Ticket Closed";
                                                                                                                else
                                                                                                                    $CurrentAgents = "Ticket To Be Closed";
                                                                                                            }
                                                                                                        }

                                                                                                        if ($CurrentAgents == '') {
                                                                                                            $CurrentAgents = 'Contact IT!';
                                                                                                        }
                                                                                                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                                                                                            $teamId = $Res11[0];
                                                                                                        }
                                                                                                        $CurrentAgentColor = "#fd7f7f";

                                                                                                        if ($thisstaff->getId() == 6) {
                                                                                                            $CurrentAgentColor = "#c456ce";
                                                                                                        }

                                                                                                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                                                                                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                                                                                        }
                                                                                                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                                                                                                        //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                                                                                                        // }
                                                                                                        else {
                                                                                                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                                                                                                $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                                                                                            } else {
                                                                                                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                                                                                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                                                                                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                                                                                                    // echo $teamId;
                                                                                                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                                                                                                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                                                                                                        echo "<td style='text-align: left;'>$Username</td>";
                                                                                                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                                                                                                        echo "<td style='text-align: left;'>$LastResponse</td>";
                                                                                                        echo "<td style='text-align: left;' $CurrentAgentsCellStyle>$CurrentAgents</td>";
                                                                                                        echo '</tr>';
                                                                                                    }
                                                                                                }
                                                                                            }

                                                                                            //BookMark
                                                                                            else if ($_REQUEST['a'] == 'BookMark') {
                                                                                                $CurrentStaffID = $thisstaff->getId();
                                                                                                $GetUsersStaffTicketsQ = "SELECT DISTINCT `ost_ticket`.`ticket_id`, `number`, `subject`, `name`, CONCAT(`firstname`, ' ', `lastname`), `team_id` , `topic_id` ,`current_step`   ,`closed`  ,`ost_ticket`.`updated` 
        FROM `ost_ticket` 
        LEFT JOIN `ost_agent_users_tickets` ON `ost_ticket`.`user_id` = `ost_agent_users_tickets`.`user_id` 
        LEFT JOIN `ost_ticket__cdata` ON `ost_ticket__cdata`.`ticket_id` = `ost_ticket`.`ticket_id` 
        LEFT JOIN `ost_user` ON `ost_user`.`id` = `ost_ticket`.`user_id` 
        LEFT JOIN `ost_staff` ON `ost_ticket`.`staff_id` = `ost_staff`.`staff_id` 
        WHERE `ost_ticket`.`ticket_id` IN (SELECT `ticket_id` FROM `ost_ticket_bookmark` WHERE `staff_id`=" . $CurrentStaffID . ")   ";


                                                                                                // echo $GetUsersStaffTicketsQ;
                                                                                                if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                                                                                                    while (list($ID, $Number, $Subject, $Username, $Staff, $TeamID, $Topic_id, $TicketCurrentStep, $Closed) = db_fetch_row($GetUsersStaffTickets_Res)) {
                                                                                                        $StaffTeam = 'Not Found!';

                                                                                                        if ($Staff != '') {
                                                                                                            $StaffTeam = $Staff;
                                                                                                        } else {
                                                                                                            $GetTeam_Q = 'SELECT `name` FROM `ost_team` WHERE `team_id` = ' . $TeamID;

                                                                                                            if (($GetTeam_Res = db_query($GetTeam_Q)) && db_num_rows($GetTeam_Res)) {
                                                                                                                $Res = db_fetch_row($GetTeam_Res);
                                                                                                                $StaffTeam = $Res[0];
                                                                                                            }
                                                                                                        }

                                                                                                        echo '<tr>';

                                                                                                        if ($canManageTickets) { ?>
                            <td><input type="checkbox" class="ckb" name="tids[]" value="<?php echo $ID; ?>" /></td>
            <?php }
                                                                                                        $LastThreadEntry = Thread::getLastThreadEntry($ID);
                                                                                                        $Body = "Not available!";
                                                                                                        $LastResponse = "";

                                                                                                        if ($LastThreadEntry != null) {
                                                                                                            $Body = $LastThreadEntry[0];
                                                                                                            $Body = strip_tags($Body, '<br /><br/><br>');
                                                                                                            $Body = substr($Body, 0, 200);
                                                                                                            $LastResponse = $LastThreadEntry[1];
                                                                                                            $poster = $LastThreadEntry[2];
                                                                                                        }

                                                                                                        $CurrentAgents = "None";
                                                                                                        $HelpTopicID = $Topic_id;

                                                                                                        if ($TicketCurrentStep > 0) {
                                                                                                            $checkIfstaff = "SELECT `staff_id` FROM `ost_help_topic_flow` WHERE `help_topic_id`=" . $HelpTopicID . " AND `step_number`=" . $TicketCurrentStep;
                                                                                                            if (($checkIfstaff_Res = db_query($checkIfstaff)) && db_num_rows($checkIfstaff_Res)) {
                                                                                                                $Res_staff = db_fetch_row($checkIfstaff_Res);
                                                                                                                $CurrentAgentsID = $Res_staff[0];
                                                                                                            }
                                                                                                            // echo $CurrentAgentsID;
                                                                                                            if ($CurrentAgentsID != 0) {
                                                                                                                $GetCurrentAgents_Q = "SELECT GROUP_CONCAT(DISTINCT CONCAT(UCASE(LEFT(`firstname`, 1)), SUBSTRING(`firstname`, 2)) SEPARATOR ', ') FROM `ost_help_topic_flow` INNER JOIN `ost_staff` ON `ost_help_topic_flow`.`staff_id` = `ost_staff`.`staff_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                                                                                                    $CurrentAgents = $Res[0];
                                                                                                                }
                                                                                                            } else {
                                                                                                                $GetCurrentAgents_Q = "SELECT `ost_team`.`name` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                                if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                                    $Res = db_fetch_row($GetCurrentAgents_Res);
                                                                                                                    $CurrentAgents = $Res[0];
                                                                                                                }
                                                                                                            }
                                                                                                        } else if ($TicketCurrentStep == -1) {
                                                                                                            if ($Closed !== '') {
                                                                                                                $CurrentAgents = "Ticket Closed";
                                                                                                            } else {
                                                                                                                if (Ticket::GetClosedDate($ID) && Ticket::GetClosedDate($ID) !== '')
                                                                                                                    $CurrentAgents = "Ticket Closed";
                                                                                                                else
                                                                                                                    $CurrentAgents = "Ticket To Be Closed";
                                                                                                            }
                                                                                                        }

                                                                                                        if ($CurrentAgents == '') {
                                                                                                            $CurrentAgents = 'Contact IT!';
                                                                                                        }
                                                                                                        $GetCurrentAgents_Q1 = "SELECT `ost_team`.`team_id` FROM `ost_help_topic_flow` INNER JOIN `ost_team` ON `ost_help_topic_flow`.`team_id` = `ost_team`.`team_id` WHERE `help_topic_id` = $HelpTopicID AND `step_number` = $TicketCurrentStep";

                                                                                                        if (($GetCurrentAgents_Res = db_query($GetCurrentAgents_Q1)) && db_num_rows($GetCurrentAgents_Res)) {
                                                                                                            $Res11 = db_fetch_row($GetCurrentAgents_Res);
                                                                                                            $teamId = $Res11[0];
                                                                                                        }
                                                                                                        $CurrentAgentColor = "#fd7f7f";

                                                                                                        if ($thisstaff->getId() == 6) {
                                                                                                            $CurrentAgentColor = "#c456ce";
                                                                                                        }

                                                                                                        if (strpos($CurrentAgents, ucfirst($thisstaff->getFirstName())) !== false) {
                                                                                                            $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                                                                                        }
                                                                                                        // elseif(in_array($teamId,$thisstaff->getTeams())){
                                                                                                        //     $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';


                                                                                                        // }
                                                                                                        else {
                                                                                                            if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                                                                                                $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                                                                                            } else {
                                                                                                                if (in_array(strtolower($CurrentAgents), $getstaffofagentT) || in_array(explode(",", strtolower($CurrentAgents))[0], $getstaffofagentT)) {
                                                                                                                    $CurrentAgentsCellStyle = 'style="background-color:#F08080; color:#FFF"';
                                                                                                                } elseif (in_array($teamId, $thisstaff->getTeams())) {
                                                                                                                    // echo $teamId;
                                                                                                                    $CurrentAgentsCellStyle = 'style="background-color:' . $CurrentAgentColor . ';color:#FFF"';
                                                                                                                } else {
                                                                                                                    $CurrentAgentsCellStyle = 'style=""';
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                        echo "<td style='font-weight:bold'><a style='display: inline' class='preview' data-preview='#tickets/$ID/preview' href='/task/scp/tickets.php?id=$ID'>$Number</a></td>";
                                                                                                        echo "<td style='font-weight:bold;text-align: right;'><a style='display: inline' href='/task/scp/tickets.php?id=$ID'>$Subject</a></td>";
                                                                                                        echo "<td style='text-align: left;'>$Username</td>";
                                                                                                        echo "<td style='text-align: left;'><strong style='color:blue;'>" . $poster . "</strong>\r\n" . substr($Body, 0, 100) . "</td>";
                                                                                                        echo "<td style='text-align: left;'>$LastResponse</td>";
                                                                                                        echo "<td style='text-align: left;' $CurrentAgentsCellStyle>$CurrentAgents</td>";
                                                                                                        echo '</tr>';
                                                                                                    }
                                                                                                }
                                                                                            }




            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="<?php echo count($columns) + 4; ?>">
                    <?php if ($count && $canManageTickets) {
                        echo __('Select'); ?>:&nbsp;
                    <a id="selectAll" href="#ckb"><?php echo __('All'); ?></a>&nbsp;&nbsp;
                    <a id="selectNone" href="#ckb"><?php echo __('None'); ?></a>&nbsp;&nbsp;
                    <a id="selectToggle" href="#ckb"><?php echo __('Toggle'); ?></a>&nbsp;&nbsp;
                <?php } else {
                        echo '<i>';
                        echo $ferror ? Format::htmlchars($ferror) : __('Query returned 0 results.');
                        echo '</i>';
                    } ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php
    if ($count > 0) { //if we actually had any tickets returned.
    ?> <div>
            <span class="faded pull-right"><?php echo $pageNav->showing(); ?></span>
            <?php
            echo __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
            $_SESSION["ticket_page_number"] = $page;
            ?>
            <a href="#tickets/export/<?php echo $queue->getId(); ?>" id="queue-export" class="no-pjax"><?php echo __('Export'); ?></a>
            <i class="help-tip icon-question-sign" href="#export"></i>
        </div>
    <?php
    } ?>

    <?php
    if ($count > 0) { //if we actually had any tickets returned.

        $GetGeneralForms = "SELECT `content` , `duration` , `created_at` FROM `ost_general_informs` WHERE date(`created_at`)=DATE(NOW()) AND  `Flag_agent`=1";
        // echo $GetGeneralForms;
        if (($GetGeneralForms_Res = db_query($GetGeneralForms)) && db_num_rows($GetGeneralForms_Res)) {
            while (list($CC1_, $Du_, $Ca_) = db_fetch_row($GetGeneralForms_Res)) {
                $CC1 = $CC1_;
                $Du =  $Du_;
                $Ca =  $Ca_;
            }
        }
        // echo $CC1;
        // $CreatedDate = strtotime($Ca->format('Y-m-d H:i:s'));
        $new_time =  date('Y-m-d H:i:s', strtotime('+' . $Du . ' hour ', strtotime($Ca)));
        $NowDateD = new DateTime("Asia/Damascus");
        $NowDate = date('Y-m-d H:i:s', strtotime('+2 hour ', strtotime(date('Y-m-d H:i:s'))));
        $DateDiff = $NowDate - $new_time;
        // echo $NowDate ;
        // echo "<br>";
        // echo $new_time;
        if ($NowDate  <  $new_time) {

            echo ' <div style="position:  position: relative;top: 250px;width: 88%;">';
        } else {
            echo ' <div style="position: inherit;top: 235px;width: 88%;">';
        }


    ?>
        <span class="faded pull-right"><?php echo $pageNav->showing(); ?></span>
        <?php
        echo __('Page') . ':' . $pageNav->getPageLinks() . '&nbsp;';
        $_SESSION["ticket_page_number"] = $page;
        ?>
        <a href="#tickets/export/<?php echo $queue->getId(); ?>" id="queue-export" class="no-pjax"><?php echo __('Export'); ?></a>
        <i class="help-tip icon-question-sign" href="#export"></i>
        </div>
    <?php
    } ?>
</form>
<script type="text/javascript">
    $(function() {
        $(document).on('click', 'a#queue-export', function(e) {
            e.preventDefault();
            var url = 'ajax.php/' + $(this).attr('href').substr(1)
            $.dialog(url, 201, function(xhr) {
                window.location.href = '?a=export&queue=<?php echo $queue->getId(); ?>';
                return false;
            });
            return false;
        });
    });

    function getParameterByName(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
</script>