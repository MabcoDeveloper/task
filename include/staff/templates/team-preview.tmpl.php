<?php
$error=$msg=$warn=null;

echo sprintf(
        '<div style="white-space: nowrap; display: inline-block; width:auto; height: auto; padding: 2px 2px 0 5px;">
         <h2>'.__('Team #%s').': </h2>',
         $task->getAssigned()
         
         );

if($error)
    echo sprintf('<div id="msg_error">%s</div>',$error);
elseif($msg)
    echo sprintf('<div id="msg_notice">%s</div>',$msg);
elseif($warn)
    echo sprintf('<div id="msg_warning">%s</div>',$warn);


echo '<div id="task-preview_container">';

?>
<?php
//TODO: add link to view if the user has permission
?>


<!-- hidden div for team -->
<div class=" tab_content" id="team">
    <table border="0" cellspacing="" cellpadding="1">
        <colgroup><col style="min-width: 250px;"></col></colgroup>
        <?php
        if (($team=$task->getTeamId())) {
            $username=array();
            $sql_getuserteam="SELECT CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`) 
            FROM `ost_team`
             INNER JOIN `ost_team_member` ON `ost_team_member`.`team_id`=`ost_team`.`team_id` 
             INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`= `ost_team_member`.`staff_id`
             WHERE `ost_team`.`team_id`=".$task->getTeamId();
            // echo $sql_getuserteam; 
            if (($sql_getuserteam_Res = db_query($sql_getuserteam)) && db_num_rows($sql_getuserteam_Res)) {
                while (list($Name, $Address) = db_fetch_row($sql_getuserteam_Res)) {
                    array_push($username, $Name);
                }
            }
            
            ?>
        <?php
            
            foreach($username as $index => $item) {
                echo sprintf('<tr><td %s>%s%s <em class="faded">&lt;%s&gt;</em></td></tr>',
                ('class="faded"'),
                sprintf('<i class="icon-%s"></i>',
                ( 'user' )),
                $item,
                $item);
            }
        }  else {
            echo __("Team doesn't have any member.");
        }?>
    </table>
    <br>
   
</div>
</div>

