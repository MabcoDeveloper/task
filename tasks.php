<?php
/*************************************************************************
    tasks.php

    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/



require('secure.inc.php');
if(!is_object($thisclient) || !$thisclient->isValid()) die('Access denied'); //Double check again.

date_default_timezone_set("Asia/Damascus");
require_once(INCLUDE_DIR . 'class.task.php');
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
$task = $task=Task::lookup(encrypt_decrypt('decrypt',$_REQUEST['id'],$secret_key));
// echo encrypt_decrypt('decrypt',$_REQUEST['id'],$secret_key);
?>
<style type="text/css">
@page {
    header: html_def;
    footer: html_def;
    margin: 15mm;
    margin-top: 30mm;
    margin-bottom: 22mm;
}
#task_thread .message,
#task_thread .response,
#task_thread .note {
    margin-top:10px;
    /* border:1px solid #aaa; */
    /* border-bottom:2px solid #aaa; */
}
#task_thread .header {
    text-align:left;
    /* border-bottom:1px solid #aaa; */
    padding:3px;
    width: 100%;
    table-layout: fixed;
}
#task_thread .message .header {
    background:#C3D9FF;
}
#task_thread .response .header {
    background:#FFE0B3;
}
#task_thread .note .header {
    background:#FFE;
}
#task_thread .info {
    padding:5px;
    background: snow;
    border-top: 0.3mm solid #ccc;
}
/* 
table.meta-data {
    width: 50%;
}
table.custom-data {
    margin-top: 10px;
}
table.custom-data th {
  width: 25%;
}
table.custom-data th,
table.meta-data th {
    text-align: left;
    background-color: #ddd;
    padding: 3px 8px;
}
table.meta-data td {
    padding: 3px 8px;
    text-align: left;
} */
.faded {
    color:#666;
}
.pull-left {
    float: left;
}
.pull-right {
    float: left;
}
.flush-right {
    text-align: lef;
}
.flush-left {
    text-align: left;
}
.ltr {
    direction: ltr;
    unicode-bidi: embed;
}
.headline {
    border-bottom: 2px solid black;
    font-weight: bold;
}
div.hr {
    border-top: 0.2mm solid #bbb;
    margin: 0.5mm 0;
    font-size: 0.0001em;
}
.thread-entry, .thread-body {
    page-break-inside: avoid;
}
.tabs a {
    text-decoration: none;
}
ul.tabs {
    padding:4px 0 0 20px;
    margin:0;
    text-align:left;
    height:29px;
    border-bottom:1px solid #aaa;
    background:#eef3f8;
    position: relative;
    box-shadow: inset 0 -5px 10px -9px rgba(0,0,0,0.3);
}
#response_options ul.tabs {
}

ul.tabs li {
    margin:0;
    padding:0;
    display:inline-block;
    list-style:none;
    text-align:center;
    min-width:130px;
    font-weight:bold;
    height:28px;
    line-height:20px;
    color:#444;
    display:inline-block;
    outline:none;
    position:relative;
    bottom:1px;
    background:#fbfbfb;
    background-color: rgba(251, 251, 251, 0.5);
    border:1px solid #ccc;
    border:1px solid rgba(204, 204, 204, 0.5);
    border-bottom:none;
    position: relative;
    bottom: 1px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    font-size: 95%;
}
ul.tabs li.active {
    color:#184E81;
    background-color:#f9f9f9;
    border:1px solid #aaa;
    border-bottom:none;
    text-align: center;
    border-top:2px solid #81a9d7;
    bottom: 0;
    box-shadow: 4px -1px 6px -3px rgba(0,0,0,0.2);
}
ul.tabs li:not(.active) {
    box-shadow: inset 0 -5px 10px -9px rgba(0,0,0,0.2);
    bottom: 2px;
}
ul.tabs.clean li.active {
    background-color: white;
}

ul.tabs li a {
    font-weight: 400;
    line-height: 20px;
    color: #444;
    color: rgba(0,0,0,0.6);
    display: block;
    outline: none;
    padding: 5px 10px;
}
ul.tabs li a:hover {
    text-decoration: none;
}

ul.tabs li.active a {
    font-weight: bold;
    color: #222;
    color: rgba(0,0,0,0.8);
}

ul.tabs li.empty {
    padding: 5px;
    border: none !important;
}

ul.tabs.vertical {
    display: inline-block;
    height: auto;
    border-bottom: initial;
    border-right: 1px solid #aaa;
    padding-left: 0;
    padding-bottom: 40px;
    padding-top: 10px;
    background: transparent;
    box-shadow: inset -5px 0 10px -9px rgba(0,0,0,0.3);
}
ul.tabs.vertical.left {
    float: left;
    margin-right: 9px;
}

ul.tabs.vertical li {
    border:1px solid #ccc;
    border:1px solid rgba(204, 204, 204, 0.5);
    border-right: none;
    min-width: 0;
    display: block;
    border-top-right-radius: 0;
    border-bottom-left-radius: 5px;
    right: 0;
    height: auto;
}
ul.tabs.vertical li:not(.active) {
    box-shadow: inset -5px 0 10px -9px rgba(0,0,0,0.3);
}

ul.tabs.vertical li + li {
    margin-top: 5px;
}

ul.tabs.vertical li.active {
    border: 1px solid #aaa;
    border-left: 2px solid #81a9d7;
    border-right: none;
    right: -1px;
    box-shadow: -1px 4px 6px -3px rgba(0,0,0,0.3);
}

ul.tabs.vertical.left li {
    text-align: right;
}

ul.tabs.vertical li a {
    padding: 5px;
}

ul.tabs.alt {
  height: auto;
  background-color:initial;
  border-bottom:2px solid #ccc;
  border-bottom-color: rgba(0,0,0,0.1);
  box-shadow:none;
}

ul.tabs.alt li {
  width:auto;
  border:none;
  min-width:0;
  box-shadow:none;
  bottom: 1px;
  height: auto;
}

ul.tabs.alt li.active {
  border:none;
  box-shadow:none;
  background-color: transparent;
  border-bottom:2px solid #81a9d7;
}
.dialog ul.tabs, .dialog ul.tabs * {
    box-sizing: content-box;
    -moz-box-sizing: content-box;
    -webkit-box-sizing: content-box;
}
.tab_content {
    position: relative;
}
.tab_content:not(.left) {
    padding: 12px 0;
}
.left-tabs {
    margin-left: 48px;
}
<?php include ROOT_DIR . 'css/thread.css'; ?>
<?php //include ROOT_DIR . 'scp/css/scp.css'; ?>
    </style>
<?php
include(CLIENTINC_DIR.'header.inc.php');
?>
<strong>
<h2>
                <a  id="reload-task"
                    href="tasks.php?id=<?php echo $task->getId(); ?>"><i
                    class="icon-refresh"></i>&nbsp;<?php
                    echo sprintf(__('Task #%s'), $task->getNumber()); ?></a>
                </h2>
</strong>
<br>

    <div class="clear tixTitle has_bottom_border">
    <h1 style="color:black;">
    <?php
        echo ($task->getTitle());
    ?>
    </h1>
</div>
<br>
    <hr>



<!-- Task metadata -->

<table id="ticketTable" cellspacing="0" cellpadding="0" width="100%" border="0" >
<tbody>
<tr >
    <th><?php echo __('Status'); ?></th>
    <td><?php echo $task->getStatus(); ?></td>
    <th><?php echo __('Department'); ?></th>
    <td><?php echo $task->getDept(); ?></td>
</tr>
<tr>
    <th><?php echo __('Create Date'); ?></th>
    <td><?php echo Format::datetime($task->getCreateDate()); ?></td>
    <?php
    if ($task->isOpen()) { ?>
    <th><?php echo __('Assigned To'); ?></th>
    <td><?php echo $task->getAssigned(); ?></td>
    <?php
    } else { ?>
    <th><?php echo __('Closed By');?>:</th>
    <td>
        <?php
        if (($staff = $task->getStaff()))
            echo Format::htmlchars($staff->getName());
        else
            echo '<span class="faded">&mdash; '.__('Unknown').' &mdash;</span>';
    ?>
    </td>
    <?php
    } ?>
</tr>
<tr>
    <?php
    if ($task->isOpen()) {?>
    <th><?php echo __('Due Date'); ?></th>
    <td><?php echo Format::datetime($task->getDueDate()); ?></td>
    <?php
    } else { ?>
    <th><?php echo __('Close Date'); ?></th>
    <td><?php echo Format::datetime($task->getCloseDate()); ?></td>
    <?php
    } ?>
    <th><?php //echo __('Close Date'); ?></th>
    <td><?php //echo Format::datetime($task->getCloseDate()); ?></td>
</tr>
</tbody>
</table>
<!-- Custom Data -->
<?php
foreach (DynamicFormEntry::forObject($task->getId(),
            ObjectModel::OBJECT_TYPE_TASK) as $form) {
    // Skip core fields shown earlier on the view
    $answers = $form->getAnswers()->exclude(Q::any(array(
        'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
        'field__name__in' => array('title')
    )));
    if (count($answers) == 0)
        continue;
    ?>
        <table class="custom-data" cellspacing="0" cellpadding="4" width="100%" border="0">
        <tr><td colspan="2" class="headline flush-left"><?php echo $form->getTitle(); ?></th></tr>
        <?php foreach($answers as $a) {
            if (!($v = $a->display())) continue; ?>
            <tr>
                <th><?php
    echo $a->getField()->get('label');
                ?>:</th>
                <td><?php
    echo $v;
                ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php
    $idx++;
} ?>

<!-- Task Thread -->

<div id="task_thread">
<?php
$types = array('M', 'R', 'N');
if ($entries = $task->getThreadEntries($types)) {
    $entryTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    foreach ($entries as $entry) { ?>
        <div class="thread-entry <?php echo $entryTypes[$entry->type]; ?>">
            <table class="header" style="width:100%">
            <tr>
            <td align="left" class=" faded " style="white-space:no-wrap">
                    <?php
                        echo Format::htmlchars($entry->getName()); ?></span>
                </td>
                <td>
                    <span><?php
                        echo Format::datetime($entry->created);?></span>
                        <span style="padding:0 1em" class="faded "><?php
                        echo Format::truncate($entry->title, 100); ?></span>
                    
                </td>
               
            </tr></table>
            <div class="thread-body">
                <div><?php echo $entry->getBody()->display('pdf'); ?></div>
            <?php
            if ($entry->has_attachments
                    && ($files = $entry->attachments)) { ?>
                <div class="info">
<?php           foreach ($files as $A) { ?>
                    <div>
                    <span class="attachment-info">
        <i class="icon-paperclip icon-flip-horizontal"></i>
        <a  class="no-pjax truncate filename"
            href="<?php echo $A->file->getDownloadUrl(['id' => $A->getId()]);
            ?>" download="<?php echo Format::htmlchars($A->getFilename()); ?>"
            target="_blank"><?php echo Format::htmlchars($A->getFilename());
        ?></a><?php echo $size;?>
        </span>
                    </div>
<?php           } ?>
                </div>
<?php       } ?>
            </div>
        </div>
<?php }
} ?>
</div>

<?php $info=($_POST && $errors)?Format::input($_POST):array();?>
<div id="task_response_options" class="<?php echo $task ? 'ticket_task_actions' : ''; ?> sticky bar stop actions">
    <ul class="tabs">
        <?php
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
        name="task_reply" method="post" enctype="multipart/form-data" action="">
        <?php csrf_token(); ?>
        
                    
                    <textarea name="response" id="task-response" cols="50"
                        data-signature-field="signature" 
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="<?php echo __( 'Start writing your update here.'); ?>"
                        rows="9" wrap="soft"
                        class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                            ?>" ></textarea>
                <!-- <div id="task_response_form_attachments" class="attachments">
                <?php
                    // if ($reply_attachments_form)
                        // print $reply_attachments_form->getField('attachments')->render();
                ?>
                </div> -->
                <?php
   $reply_attachments_form = new SimpleForm(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:reply',
        'configuration' => array('extensions'=>'')))
));

print $reply_attachments_form->getField('attachments')->render();
echo $reply_attachments_form->getMedia();
    


    
?>
               
        
       <p  style="text-align:center;">
           <input class="save pending" type="submit" name="Post" value="<?php echo __('Post Update');?>">
           <input type="reset" value="<?php echo __('Reset');?>">
       </p>
    </form>
    <?php
    if(isset($_POST["response"])){
        echo "notempty";
    $Posttesx=$_POST["response"];
    $sqlgetthreadId = "SELECT `id`  FROM `ost_thread` WHERE `object_id` = ".encrypt_decrypt('decrypt',$_REQUEST['id'],$secret_key)." AND `object_type`='A'";
        if (($sqlgetthreadId_Res = db_query($sqlgetthreadId)) && db_num_rows($sqlgetthreadId_Res)) {
    
            $threadId = db_fetch_row($sqlgetthreadId_Res);
        }
    
        $GetThreadID_Q = "INSERT INTO `ost_thread_entry` (`pid`, `thread_id`, `staff_id`, `user_id`, `type`, `flags`, `poster`, `source`, `title`, `body`, `format`, `ip_address`, `recipients`, `created`) VALUES ('0', '".$threadId[0]."', '101', '0', 'R', '64', '".$thisclient->getName()."', '', NULL, '".$Posttesx."', 'html', '::1', NULL, NOW());";
    
        if(db_query($GetThreadID_Q)){
            
            $sqlobjectID = "SELECT MAX(`ost_thread_entry`.`id`) FROM `ost_thread_entry` 
            INNER JOIN `ost_thread` ON `ost_thread`.`id`=`ost_thread_entry`.`thread_id`
        WHERE `ost_thread`.`id`=".$threadId[0]." AND `ost_thread`.`object_type`='A'";
               
            //    echo $sqlobjectID;
               if (($sqlobjectID_Res = db_query($sqlobjectID)) && db_num_rows($sqlobjectID_Res)) {
            
                    $replyId = db_fetch_row($sqlobjectID_Res);
                }
                //rahaf
                foreach($reply_attachments_form->getField('attachments')->getFiles() as $att){
             $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, ".$replyId[0].", 'H', ".array_values($att)[0] .", '".array_values($att)[1]."' , '0', NULL)";
             echo $sqll;
             db_query($sqll);
                }
        
    }
  unset($_POST["Post"]);
        echo "<script>
        if ( window.history.replaceState ) {
          window.history.replaceState( null, null, window.location.href );
          
        }
        window.location.reload();
        </script>";

}
    //} ?>
    
</div>

 
<?php
if (isset($_POST["Post"])) {

// echo $GetThreadID_Q;

}

// echo $threadId[0];
    // include(CLIENTINC_DIR.$inc);
    // print $tform->getMedia();
include(CLIENTINC_DIR.'footer.inc.php');
?>
<script>
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>