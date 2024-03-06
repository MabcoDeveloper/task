<div>
<table border="0" cellspacing="" cellpadding="1">
<colgroup><col style="min-width: 250px;"></col></colgroup>
<?php
$username=array();
$sql_getuserteam="SELECT `ost_team`.`name` FROM `ost_thread_collaborator`
INNER JOIN `ost_team` ON `ost_team`.`team_id`=`ost_thread_collaborator`.`team_id` 
WHERE `thread_id`=".$thread->getId();
// echo $sql_getuserteam; 
if (($sql_getuserteam_Res = db_query($sql_getuserteam)) && db_num_rows($sql_getuserteam_Res)) {
    while (list($Name) = db_fetch_row($sql_getuserteam_Res)) {
        array_push($username, $Name);
    }
}
if (($users=$thread->getCollaborators())) {?>
<?php
    foreach($users as $user) {
        echo sprintf('<tr><td %s>%s%s <em class="faded">&lt;%s&gt;</em></td></tr>',
                ($user->isActive()? '' : 'class="faded"'),
                (($U = $user->getUser()) && ($A = $U->getAvatar()))
                    ? $A->getImageTag(20) : sprintf('<i class="icon-%s"></i>',
                        ($user->isActive()? 'comments' :  'comment-alt')),
                Format::htmlchars($user->getName()),
                $user->getEmail());
    }
    foreach($username as $user) {
        echo sprintf('<tr><td %s>%s%s <em class="faded">&lt;%s&gt;</em></td></tr>',
                ('class="faded"'),
                sprintf('<i class="icon-%s"></i>',
                ( 'comments' )),
                $user,
                $user);
    }
}  else {
    echo "<strong>".__("Thread doesn't have any collaborators.")."</strong>";
}?>
</table>
<?php
$options = array();

$options[] = sprintf(
        '<a class="collaborators" id="managecollab" href="#thread/%d/collaborators">%s</a>',
        $thread->getId(),
        $thread->getNumCollaborators()
        ? __('Manage Collaborators') : __('Add Collaborator')
        );

if ($options) {
    echo '<ul class="tip_menu">';
    foreach($options as $option)
        echo sprintf('<li>%s</li>', $option);
    echo '</ul>';
}
?>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a#managecollab', function (e) {
        e.preventDefault();
        $('.tip_box').remove();
        return false;
    });
});
</script>
