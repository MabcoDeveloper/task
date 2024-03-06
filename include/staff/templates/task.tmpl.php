<?php

if (!$info['title'])
    $info['title'] = __('New Task');

$namespace = 'task.add';

if ($ticket)
    $namespace = sprintf('ticket.%d.task', $ticket->getId());

?>
<div id="task-form">
    <h3 class="drag-handle"><?php echo $info['title']; ?></h3>
    <b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
    <hr/>
    <?php
        if ($info['error']) {
            echo sprintf('<p id="msg_error">%s</p>', $info['error']);
        } elseif ($info['warning']) {
            echo sprintf('<p id="msg_warning">%s</p>', $info['warning']);
        } elseif ($info['msg']) {
            echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
        }
    ?>
    <div id="new-task-form" style="display:block;">
        <form method="post" class="org" action="<?php echo $info['action'] ?: '#tasks/add'; ?>" id="formID">
        <?php csrf_token(); ?>
           <?php
                $form = $form ?: TaskForm::getInstance();
                echo $form->getForm($vars)->asTable(' ', array('draft-namespace' => $namespace));

                $iform = $iform ?: TaskForm::getInternalForm(null, array('mode' =>  $thisstaff->isManager() ? 'manager' : 'not_manager'));
                echo $iform->asTable(__("Task Visibility & Assignment"));
            ?>

            <script type="text/javascript">
                $('fieldset[data-field-id="6"]').hide();

                // Check if recurring task is checked
                if ($('fieldset[data-field-id="5"] input[type="checkbox"]').prop("checked")) {
                    $('fieldset[data-field-id="6"]').show();
                    $('fieldset[data-field-id="6"] select').prop("required", true);
                    $('fieldset[data-field-id="8"]').show();
                    $('fieldset[data-field-id="8"] input[type="text"]').prop("required", true);
                    $('fieldset[data-field-id="9"]').show();
                    $('fieldset[data-field-id="9"] select').prop("required", true);
                } else {
                    $('fieldset[data-field-id="6"]').hide();
                    $('fieldset[data-field-id="6"] select').prop("required", false);
                    $('fieldset[data-field-id="8"]').hide();
                    $('fieldset[data-field-id="8"] input[type="text"]').prop("required", false);
                    $('fieldset[data-field-id="9"]').hide();
                    $('fieldset[data-field-id="9"] select').prop("required", false);
                }

                // Fix styling issues
                $('fieldset[data-field-id="6"]').css("padding-top", "0");
                $('fieldset[data-field-id="8"]').css("padding-top", "0");
                $('fieldset[data-field-id="9"]').css("padding-top", "0");
                $('fieldset[data-field-id="5"]').css("padding-bottom", "0");
                $('fieldset[data-field-id="4"] input[type="radio"]').css("width", "auto");
                $('fieldset[data-field-id="4"] input[type="radio"]').css("display", "inline-block");
                $('fieldset[data-field-id="4"] input[type="radio"]').attr("required", true);
                $('fieldset[data-field-id="4"] label').css("cursor", "pointer");
                $('fieldset[data-field-id="32"] input[type="text"]').css("direction", "rtl");
                $('#popup').css({'cssText': 'top: 3em; left: 3em; display: block; max-height: 88%; width: 94% !important;'});
                $('fieldset[data-field-id="6"]').parent().parent().after('<tr><td class="cell" colspan="12" rowspan="1" data-field-id="10"><h3 style="margin-bottom:1.5em;"></h3></td></tr>');
                $('fieldset[data-field-id="3"] span').after(" <strong>(Default duedate is 24 hours)</strong>");

                if (typeof IsManager === 'undefined' || IsManager === null ) {
                    $('fieldset[data-field-id="5"]').html("");
                }

                var SD_ID = $('fieldset[data-field-id="1"] select').find(":selected").val();
                
                if (SD_ID != '')
                    SetDeptAgents();

                $(document).on('change', 'fieldset[data-field-id="1"] select', function() {
                    SetDeptAgents();
                });

                function SetDeptAgents() {
                    $('fieldset[data-field-id="2"] select option').prop('selected', false);
                    $('fieldset[data-field-id="2"] select option[value=""]').prop('selected', true);

                    var SelectedDeptId = $('fieldset[data-field-id="1"] select').find(":selected").val();

                    if (AllAgentsIds.constructor != Object) {
                        AllAgentsIds = JSON.parse(AllAgentsIds);
                    }

                    var AgentsIds = 0;

                    $('fieldset[data-field-id="2"] select > optgroup option').show();

                    if (AllAgentsIds[SelectedDeptId]) {
                        AgentsIds = AllAgentsIds[SelectedDeptId].split("_");

                        $('fieldset[data-field-id="2"] select > optgroup option').each(function() {
                            if (!AgentsIds.includes(this.value) && !this.value.includes("t")) {
                                $(this).hide();
                            }
                        });
                    }
                }
                $(document).on('change', 'fieldset[data-field-id="2"] select', function() {
                    var SelectedDeptId = $('fieldset[data-field-id="2"] select').find(":selected").val();
                    if (SelectedDeptId.startsWith("t")) {
                        var res = SelectedDeptId.split("t");
                        alert("team's Members are : "+ AllTeamMember[res[1]]);
                    }
                    
                });

                $(document).on('change', 'fieldset[data-field-id="5"] input[type="checkbox"]', function() {
                    if ($(this).prop("checked")) {
                        $('fieldset[data-field-id="6"]').show();
                        $('fieldset[data-field-id="6"] select').prop("required", true);
                        $('fieldset[data-field-id="6"] select').val('');
                        $('fieldset[data-field-id="8"]').show();
                        $('fieldset[data-field-id="8"] input[type="text"]').prop("required", true);
                        $('fieldset[data-field-id="8"] input[type="text"]').val('');
                        $('fieldset[data-field-id="8"] .faded').hide();
                        $('fieldset[data-field-id="9"]').show();
                        $('fieldset[data-field-id="9"] select').prop("required", true);
                        $('fieldset[data-field-id="9"] select').val('');
                        $('fieldset[data-field-id="3"]').hide();
                        $('fieldset[data-field-id="3"] input').val('');
                    } else {
                        $('fieldset[data-field-id="6"]').hide();
                        $('fieldset[data-field-id="6"] select').prop("required", false);
                        $('fieldset[data-field-id="8"]').hide();
                        $('fieldset[data-field-id="8"] input[type="text"]').prop("required", false);
                        $('fieldset[data-field-id="9"]').hide();
                        $('fieldset[data-field-id="9"] select').prop("required", false);
                        $('fieldset[data-field-id="3"]').show();
                    }
                });

                $(document).on('change', 'fieldset[data-field-id="6"] select', function() {
                    UpdateTime();
                });

                $(document).on('change', 'fieldset[data-field-id="8"] input[type="text"]', function() {
                    UpdateTime();
                });

                function UpdateTime() {
                    var RecurringScheduleText = $('fieldset[data-field-id="8"] input[type="text"]').val();
                    var RecurringScheduleDateTime = new Date(RecurringScheduleText);
                    $('td[data-field-id="10"]').show();

                    if ($('fieldset[data-field-id="6"] select').find(":selected").val() === 'ONETIME') {
                        $('td[data-field-id="10"] h3').html("Task will occur one time at " + RecurringScheduleDateTime.toLocaleString());
                    } else if ($('fieldset[data-field-id="6"] select').find(":selected").val() === 'DAY') {
                        $('td[data-field-id="10"] h3').html("Task will start / recur on every day at " + RecurringScheduleDateTime.toLocaleTimeString());
                    } else if ($('fieldset[data-field-id="6"] select').find(":selected").val() === 'WEEK') {
                        var DaysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                        $('td[data-field-id="10"] h3').html("Task will start / recur on " + DaysOfWeek[RecurringScheduleDateTime.getDay()]);
                    } else if ($('fieldset[data-field-id="6"] select').find(":selected").val() === 'MONTH') {
                        $('td[data-field-id="10"] h3').html("Task will start / recur on the " + RecurringScheduleDateTime.getDate() + "th of the month");
                    } else {
                        $('td[data-field-id="10"]').hide();
                    }
                }

                // CC
                $('td[data-field-id="13"]').parent().after(
                    '<span id="add_cc_but" class="buttons" onclick="AddCC();"><input style="font-size: large;width: 100px;" type="button" value="Add CC" /></span>'
                );

                $('fieldset[data-field-id="11"]').hide();
                $('fieldset[data-field-id="12"]').hide();
                $('fieldset[data-field-id="13"]').hide();

                var LastCC_ID = 10;

                function AddCC() {
                    InitializeSelect(LastCC_ID + 1);
                }

                function InitializeSelect(ID) {
                    if ($('fieldset[data-field-id="' + ID + '"]').is(":hidden")) {
                        var PreviousCC = $('fieldset[data-field-id="' + (ID - 1) + '"] select').find(":selected").val();

                        if (PreviousCC != '') {
                            $('fieldset[data-field-id="' + ID + '"]').show();
                            $('fieldset[data-field-id="' + ID + '"] select').prop("required", true);
                            $('fieldset[data-field-id="' + ID + '"] select > optgroup').eq(1).remove();
                            $('fieldset[data-field-id="' + ID + '"] select').css({'width':'300px', 'display':'inline-table','margin-right':'1em'});
                            $('fieldset[data-field-id="' + ID + '"] select').after('<i class="icon-minus remove-cc-icon" id="remove-cc-icon-' + ID + '" onclick="RemoveSelect()"></i>');
                            $('fieldset[data-field-id="' + ID + '"] select > option').each(function() {
                                if (this.value.includes("t")) {
                                    $(this).remove();
                                }
                            });
                            $('#remove-cc-icon-' + LastCC_ID).remove();

                            LastCC_ID += 1;

                            if (LastCC_ID == 13) {
                                $('#add_cc_but').hide();
                            }
                        } else {
                            alert("Please select CC #" + (LastCC_ID - 10) + " first!");
                        }
                    }
                }

                function RemoveSelect() {
                    if (!$('fieldset[data-field-id="' + LastCC_ID + '"]').is(":hidden")) {
                        $('fieldset[data-field-id="' + LastCC_ID + '"] select').val('');
                        $('fieldset[data-field-id="' + LastCC_ID + '"] select').prop("required", false);
                        $('#remove-cc-icon-' + LastCC_ID).remove();
                        $('fieldset[data-field-id="' + (LastCC_ID - 1) + '"] select').after('<i class="icon-minus remove-cc-icon" id="remove-cc-icon-' + (LastCC_ID - 1) + '" onclick="RemoveSelect()"></i>');
                        $('fieldset[data-field-id="' + LastCC_ID + '"]').hide();
                        $('#add_cc_but').show();
                        LastCC_ID -= 1;
                    }
                }


                

                    
            </script>
<fieldset  >
<br>
<div  style="width: 100%; height: 100%;" class="splitscreen">
<label style="display: block; 
    display: inline-block;
    margin-bottom: 5px"><?php echo "CC Team #1:"; ?></label>
<?php
$z = array();
        $x = array();
        $types = array();
        $typesT = array();
        $GetAllStaff = "SELECT `ost_team`.`team_id`,`ost_team`.`name` FROM `ost_team` WHERE `ost_team`.`team_id` NOT IN (SELECT `team_id` FROM `ost_team_user_member`)";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
            }}
            ?>
             <?php csrf_token(); ?>
                            <select style="width: 100%; height: 100%;"  class="modal-body" id="teamcc" name="teamcc">
                            <option disabled selected value> -- select a team -- </option>
                                <?php foreach ($z as $index => $item) {


                                ?>
                                    <option value="<?php echo $x[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                                
                            </select>
                            <br/>
                            <br/>
                            Only Close By Me:
                            <input type="checkbox" value="only close by me" name="closebyme" id="closebyme"/>
</div>
<br>
</fieldset>
<fieldset>
   

    
        <div >
<h3 class="drag-handle"><?php echo "Add Showroom"; ?></h3>
<hr/><?php
$z = array();
        $x = array();
        $types = array();
        $typesT = array();
        $GetAllStaff = "SELECT `id`,`name` FROM `ost_user`";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
            }}

            $GetAllteam = "SELECT `ost_team_user_member`.`team_id`,`ost_team`.`name` FROM `ost_team_user_member` 
            INNER JOIN `ost_team` ON `ost_team`.`team_id`=`ost_team_user_member`.`team_id`
            GROUP BY `ost_team_user_member`.`team_id`";
            if (($GetAllteam_Res = db_query($GetAllteam)) && db_num_rows($GetAllteam_Res)) {
                while (list($ID, $Title) = db_fetch_row($GetAllteam_Res)) {
                    array_push($types, $ID);
                    array_push($typesT, $Title);
                }}
            ?> <p><?php echo "Choose a User: (optional)"; ?></p>
            <div  style="width: 100%; height: 100%;" class="splitscreen">
            
            
             <!-- <div  class="buttons pull-left"> -->
            <!-- <form   action="" method="post"> -->
                            <?php csrf_token(); ?>
                            <select style="width: 100%; height: 100%;"  class="modal-body" id="ddlViewBy" name="showroomId[]" multiple="multiple"  size="6">
                            <option  selected value> -- select a user -- </option>
                                <?php foreach ($z as $index => $item) {


                                ?>
                                    <option value="<?php echo $x[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                                <option  selected value> -- Teams -- </option>
                                <?php foreach ($typesT as $index => $item) {


                                ?>
                                    <option value="<?php echo $types[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                            </select>
             <!-- </div> -->
                           
                            
                            <!-- <div   class="buttons pull-right"> -->
                            <?php csrf_token();
                            if(count($types) > 0){ ?>
                            <select   style="width: 100%; height: 100%; margin-left: 50px;" class="modal-body" id="ddlViewBy" name="showroomUserId[]" multiple="multiple"  size="6">
                                <option  selected value> -- Teams -- </option>
                                <?php foreach ($typesT as $index => $item) {


                                ?>
                                    <option value="<?php echo $types[$index]; ?>"><?php echo $item; ?></option>
                                <?php } ?>
                            </select>
                                <?php }?>
                            <!-- </div> -->
                          
            </div>
        </div>

  </fieldset>
            <hr>
            <p class="full-width" style="margin-bottom: 4em;">
                <span class="buttons pull-left">
                    <input type="reset" value="<?php echo __('Reset'); ?>">
                    <input type="button" name="cancel" class="close" value="<?php echo __('Cancel'); ?>">
                </span>
                <span class="buttons pull-right">
                    <input type="submit" value="<?php echo __('Create Task'); ?>" name="str1" id='submitbtn'  >
                </span>
             </p>
        </form>
    </div>
    <div class="clear"></div>
</div>

<?php 

 
// echo "<p> Useerr </p>";
if(isset($_POST['showroomId'])){
    
$_SESSION['showroomId']=$_POST['showroomId'];

// echo "<p> ".$_POST['coluser'] ."</p>";
}

if(isset($_POST['showroomUserId'])){
    
    $_SESSION['showroomUserId']=$_POST['showroomUserId'];
    
    // echo "<p> ".$_POST['coluser'] ."</p>";
    }


    if(isset($_POST['teamcc'])){
    
        $_SESSION['teamcc']=$_POST['teamcc'];
        
        // echo "<p> ".$_POST['coluser'] ."</p>";
        }
        
        if(isset($_POST['closebyme'])){
    
            $_SESSION['closebyme']=$_POST['closebyme'];
            
            // echo "<p> ".$_POST['closebyme'] ."</p>";
            }
    
// echo "<script>
// $('form#formID').submit(function(e){
    
//   });
// </script>";

// }

//document.getElementById('submitbtn').disabled = true;
?>