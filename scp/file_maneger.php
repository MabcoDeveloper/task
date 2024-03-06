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

session_start();

$page = '';

//Navigation
$nav->setTabActive('My Files');
$open_name = _P(
    'queue-name',
    /* This is the name of the open tasks queue */
    'My Files'
);
// echo $queue_name;

$nav->addSubMenu(array(
    'desc' => $open_name,
    'title' => __('My Files'),
    'href' => '?files',
    'iconclass' => 'Ticket'
));

$nav->addSubMenu(array(
    'desc' => "Upload File In Root",
    'title' => __('Upload File'),
    'href' => '?upload=upload',
    'iconclass' => 'newTicket'
));

$nav->addSubMenu(array(
    'desc' => "Create Folder In Root",
    'title' => __('Create Folder'),
    'href' => '?createfolder=createfolder',
    'iconclass' => 'newfolder'
));

if($thisstaff->isManager()){
    $nav->addSubMenu(array(
        'desc' => "My Staff Files",
        'title' => __('My Staff Files'),
        'href' => '?maneger=maneger',
        'iconclass' => 'departments'
    ));
}

$result = array();
$sql="SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=".$thisstaff->getId();
           
            if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
                    array_push($result, $RecurringTaskID);
                   
                }
            }
            if(count($result)>0){
                $nav->addSubMenu(array(
                    'desc' => "My Showrooms Files",
                    'title' => __('My Showrooms Files'),
                    'href' => '?MyShowrooms=MyShowrooms',
                    'iconclass' => 'helpTopics'
                ));
            }
            $isAdmin = array();
            
            $sql="SELECT `isadmin` FROM `ost_staff` WHERE `staff_id`=".$thisstaff->getId();
                       
                        if (($sql_Res = db_query($sql)) && db_num_rows($sql_Res)) {
                            while (list($isAdminres) = db_fetch_row($sql_Res)) {
                                array_push($isAdmin, $isAdminres);
                               
                            }
                        }
                        if($isAdmin[0] == 1 ){
                            $nav->addSubMenu(array(
                                'desc' => "Recycle Bin",
                                'title' => __('Recycle Bin'),
                                'href' => '?Recycle_Bin=Recycle_Bin',
                                'iconclass' => 'banList'
                            ));
                        }
                      

require_once(STAFFINC_DIR . 'header.inc.php');
//view all files 
$orderWays = array('DESC' => '-', 'ASC' => '');
if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $negorder = $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $negorder = $order = 'ASC';
}
if (!isset($_GET["addtofolder"]) && !isset($_GET["upload"]) && !isset($_GET["delete"]) && !isset($_GET["deletefolder"]) && !isset($_GET["maneger"])  && !isset($_GET["staff"]) && !isset($_GET["Showrooms"])  && !isset($_GET["MyShowrooms"])) {
    $negorder = $order == '-' ? 'ASC' : 'DESC';//Negate the sorting
    if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
        $order = $orderWays[strtoupper($_REQUEST['order'])];
    } else {
        $order = 'ASC';
    }

    
?>
<style>
.dropbtn {
  /* background-color: #444444; */
  color: #444444;
 
  border: none;
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
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

.dropdown-content a:hover {background-color: #ddd;}

.dropdown:hover .dropdown-content {display: block;}

/* .dropdown:hover .dropbtn {background-color: #3e8e41;} */
</style>
 <div class="pull-right states">

 <div class="dropdown">
  <button class="dropbtn">Sort</button>



  <div class="dropdown-content">
   
  <a  href="file_maneger.php?files&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" >
    <?php echo _('Sort By Name');
    ?>
</a>
<a  href="file_maneger.php?files&sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" >
    <?php echo _('Sort By Date');
    ?>
</a>
  </div>
</div>
<?php 
    $sqlgetmaxnumber = "select count(*) from ost_agent_users_tickets where staff_id =". $thisstaff->getId();
    if (($sqlgetmaxnumber_Res = db_query($sqlgetmaxnumber)) && db_num_rows($sqlgetmaxnumber_Res)) {

        $newnumber = db_fetch_row($sqlgetmaxnumber_Res);
    }
    if($newnumber[0] >0 ){
    ?>
<button onclick="restartWebMail()">Restart Web Mail</button><?php }?>
  <script >
  function  restartWebMail()
    {
      alert("لطفا انتظار انتهاء العملية");
 setTimeout(function(){
  window.location = "http://74.208.125.168/RestartWebsites.aspx?website=WebMail";
 },300);
}
</script>
</div>
<?php
      if(!isset($_GET["Recycle_Bin"])){
    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $aid=array();
            $FileId=array();
            $CreateDate=array();
            if (isset($_GET["sort"])){
                if($_GET["sort"]=="ID"){
                    if($_GET["order"]=="DESC"){
                        $OrderBy = "  `ost_file`.`name` DESC";
                    }
                elseif ($_GET["order"]=="ASC") {
                    $OrderBy = "   `ost_file`.`name` ASC";
                }
                }

                if($_GET["sort"]=="date"){
                    if($_GET["order"]=="DESC"){
                        $OrderBy = " `ost_file`.`created`  DESC";
                    }
                    elseif ($_GET["order"]=="ASC") {
                        $OrderBy= "  `ost_file`.`created`  ASC";
                    }
                  }
            }
            else{
                $OrderBy="  `ost_file`.`created`  DESC";
            }
          
            $GetUsersStaffTicketsQ = "SELECT `ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id`,`ost_file`.`created`,`ost_file_manegment`.`id` FROM `ost_file_manegment` 
            INNER JOIN `ost_file` ON `ost_file`.`id`=`ost_file_manegment`.`file_id`
            INNER JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
            WHERE `staff_id`=" . $thisstaff->getId()." AND `ost_file_manegment`.`folder_id` IS Null and `ost_file_manegment`.`deleted`='N'  GROUP by `ost_file_manegment`.`id`  ORDER BY ". $OrderBy;;
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Name,$aid_,$CreateDate_,$FileId_) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
                    array_push($aid, $aid_);
                    array_push($CreateDate, $CreateDate_);
                    array_push($FileId, $FileId_);
           
                }
            }
            $xfolder=array();
            $yfolder=array();
            $sql_folder="SELECT `id`,`name`  FROM  `ost_folder` WHERE `parent_id` = 0 AND `staff_id`=".$thisstaff->getId();
            // echo $sql_folder;
            if (($sql_folder_Res = db_query($sql_folder)) && db_num_rows($sql_folder_Res)) {
                while (list($ID, $Name) = db_fetch_row($sql_folder_Res)) {
                    
                    array_push($xfolder, $Name);
                    array_push($yfolder, $ID);
                }
            }
            //show folder
            if (count($xfolder)  > 0) {
                echo "<div>".__("Your Folders.")."</div>
                        <ul id='kb'>";
                foreach ($xfolder as $index => $item) {
                    ?>
                        <li>
                            <h4><a class="no-pjax truncate filename" style="max-width:600px"  href="file_maneger.php?addtofolder=<?php echo $yfolder[$index]; ?>"  ><?php echo  $item; ?> </a> - <span></span></h4>
                           
        
                        <?php
                            echo '
                            <a href="file_maneger.php?deletefolder='.$yfolder[$index].'" ><i class="fa icon-trash"></i></a>';
                        
                    echo "</li>";
                }
                echo "</ul>";
            }
            else {
                echo __("NO Folders found");
            } 

echo "<hr>";
            //show files
            if (count($x)  > 0) {
                echo "<div>".__("Click on the File to Download .")."</div>
                        <ul>";
                foreach ($x as $index => $item) {
                    $f = AttachmentFile::lookup((int) $y[$index]);
                    // echo sprintf('
                    ?>
                        <li style="display: inline;">
                        
                        <i  class="icon-folder-open-alt" style="font-size:30px;display: block;float: left;margin-right: 10px;color:#184e81"></i>
                            <h4><a class="no-pjax truncate filename" style="max-width:600px ;margin-top: 9px;"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>
                        <?php
                            echo "
                            <a style='float: right;margin-bottom: 100px;' href='file_maneger.php?delete=".$FileId[$index]."' ><i class='fa icon-trash'></i></a>";
                        }
                        
                    echo "</li>";
                    
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>

           
        </tbody>
    </table>
<?php  }
}

//upload file
 if (isset($_GET["upload"])) {
     ?>
     <style>
        .splitscreen {
            display: flex;
        }

        .splitscreen .left {
            flex: 1;
        }

        .splitscreen .right {
            flex: 1;
        }
        /* #cc2{
            display:none;
        }
        #cc3{
            display:none;
        } */
    </style>
    <div id="task-form">
     <div style="width: auto;height: auto; padding-top: 50px;border: 1px solid  blue;">
     <form   method="post" class="org" action="" id="formID"  enctype="multipart/form-data">
<?php csrf_token(); ?>
<label for="file">Choose File:</label>
<br>
<br>
         <?php
   $reply_attachments_form = new SimpleForm(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:reply',
        'configuration' => array('extensions'=>'')))
));

print $reply_attachments_form->getField('attachments')->render();
echo $reply_attachments_form->getMedia();
    
    
?>
<input type="submit" name="submit" value="Submit"  onclick="this.style.visibility = 'hidden'" />
</form> 
</div>
    </div>
    
<?php


if(isset($_POST["replace"])){
    foreach($_POST['check_list'] as $check) {
    foreach($_SESSION["attachments"]  as  $att) {
        if(array_values($att)[0] == $check ){
    // print_r( array_values($reply_attachments_form->getField('attachments')->getFiles()[0]));
    $DeleteFromstaff='DELETE FROM `ost_file_manegment` WHERE `id` IN (SELECT  `ost_file_manegment`.`id` FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` 
    INNER JOIN (SELECT  `ost_file_manegment`.`id` AS ID FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE
     `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId() .' AND `ost_file`.`created`= (SELECT MAX(`created`) FROM `ost_file` WHERE `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().' ) ORDER BY  `ost_file_manegment`.`id` DESC LIMIT 1) AS notin ON  notin.ID <>  `ost_file_manegment`.`id`
    
    WHERE
    `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().') ' ;
        // echo $DeleteFromstaff;
        //      db_query($Delete);
    db_query($DeleteFromstaff);
    // array_diff( $IDS, array_values($att)[0] );
        }
        }
}
 }

 $IDS=array();
 $attachments = $reply_attachments_form->getField('attachments')->getFiles();
 $_SESSION["attachments"] =$attachments;
 foreach($attachments as $att){
     array_push($IDS, '"'.array_values($att)[1].'"');
 } 
   
        $z = array(); 
        $nn = array(); 
        // $_SESSION["attachments"] =array_values($att)[1];



$ifExist='SELECT `ost_file`.`id` ,`ost_file`.`name` FROM `ost_file` 
INNER JOIN `ost_file_manegment` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE `ost_file`.`name` IN ('.implode(",", $IDS).') AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().' AND DATE(`ost_file`.`created`) NOT IN (SELECT MAX(`created`) FROM `ost_file` )  GROUP BY `ost_file`.`id`';
// echo $ifExist;
if (($sql_Res = db_query($ifExist)) && db_num_rows($sql_Res)) {
    while (list($RecurringTaskID,$Name) = db_fetch_row($sql_Res)) {
        array_push($z, $RecurringTaskID);
        array_push($nn, $Name);
       
    }
}

    ?>
<!-- <form action="" method="post"> -->


<?php
if(!empty($z)){
    ?>
    <form action="" method="post">
    <?php csrf_token(); ?>
    <p style="color: red;">This Files are Already Exist !! Select the Files you Want to Replace ??!</p>
    <?php
    foreach($z as $index => $zz){

       ?>
        <!-- <p style="color: red;"><?php //echo $nn[$index].", "; ?>  </p> -->
        <input  type="checkbox" id="vehicle1" name="check_list[]" value="<?php echo $zz; ?>">
        <label style="color: red;" for="vehicle1"> <?php echo $nn[$index].", "; ?></label><br>    
        <br>
        <?php }?>
<br>
        <input type="submit" name="replace" value="replace" >
        <input type="submit" name="confirm" value="Don't replace">
        
            
        </form>
        
       
        
        
        
        
        <?php
    }

    
    

  
    

     
    $attachments = $reply_attachments_form->getField("attachments")->getFiles();
    foreach($attachments as $att){
        
 $sql = "INSERT INTO `ost_file_manegment` (`id`, `user_id`, `staff_id`, `dep_id`,`file_id`) 
    VALUES (NULL, NULL," .$thisstaff->getId(). "," . $thisstaff->getDeptId() .  "," . array_values($att)[0]   . ")";
    //   echo $sql;
      if (db_query($sql)) {
          $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($att)[0].", NULL, '0', NULL)";
          db_query($sqll);
        //   unset($_GET["upload"]);
          
       } 
    
}
 
GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);

    
 }

if(isset($_GET["maneger"])){

    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $GetUsersStaffTicketsQ = "SELECT CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`),`ost_file_manegment`.`staff_id` FROM `ost_file_manegment` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_file_manegment`.`staff_id` INNER JOIN `ost_department` ON `ost_department`.`id`=`ost_file_manegment`.`dep_id` WHERE `ost_file_manegment`.`staff_id` IN (SELECT `staff_id` FROM `ost_staff` WHERE `dept_id` IN (SELECT `id` FROM `ost_department` WHERE `manager_id`=".$thisstaff->getId()."))  AND `ost_file_manegment`.`staff_id` <> ".$thisstaff->getId()." GROUP BY `ost_file_manegment`.`staff_id`";
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($Name,$ID ) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
           
                }
            }
            if (count($x)  > 0) {
                echo "<div>".__("Click on the Staff Name to see his Files .")."</div>
                        <ul id='kb'>";
                foreach ($x as $index => $item) {
                    echo sprintf("
                        <li>
                            <h4><a class='truncate' style='max-width:600px' href='?staff=%s'>%s</a> - <span></span></h4>
                            ",$y[$index],
                            $item
                        
                        );
                        
                            echo "..";
                        }
                    echo "</li>";
                
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>
        </tbody>
    </table>
<?php  }







//Myshowroom names 
if(isset($_GET["MyShowrooms"])){

    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $GetUsersStaffTicketsQ = "SELECT `ost_user`.`name`,`ost_file_manegment`.`user_id` FROM `ost_file_manegment` INNER JOIN `ost_user` ON `ost_user`.`id`=`ost_file_manegment`.`user_id`  WHERE  `ost_file_manegment`.`user_id` IN (SELECT `user_id` FROM `ost_agent_users_tickets` WHERE `staff_id`=".$thisstaff->getId().") and `ost_file_manegment`.`deleted`='N'  GROUP BY `ost_file_manegment`.`user_id`";
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($Name,$ID ) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
           
                }
            }
            if (count($x)  > 0) {
                echo "<div>".__("Click on the Staff Name to see his Files .")."</div>
                        <ul id='kb'>";
                foreach ($x as $index => $item) {
                    echo sprintf("
                        <li>
                            <h4><a class='truncate' style='max-width:600px' href='?Showrooms=%s'>%s</a> - <span></span></h4>
                            ",$y[$index],
                            $item
                        
                        );
                        
                            echo "..";
                        }
                    echo "</li>";
                
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>
        </tbody>
    </table>
<?php  }


if(isset($_GET["Recycle_Bin"])){

    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $aid=array();
            $FileId=array();
            $GetUsersStaffTicketsQ = "SELECT `ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id`,`ost_file`.`created`,`ost_file_manegment`.`id` FROM `ost_file_manegment` 
            INNER JOIN `ost_file` ON `ost_file`.`id`=`ost_file_manegment`.`file_id`
            INNER JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
            WHERE  `ost_file_manegment`.`deleted`='Y'";
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Name,$aid_,$CreateDate_,$FileId_) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
                    array_push($aid, $aid_);
                    array_push($CreateDate, $CreateDate_);
                    array_push($FileId, $FileId_);
           
                }
            }
            if (count($x)  > 0) {
                echo "<div>".__("Click on the File to Download .")."</div>
                        <ul >";
                        foreach ($x as $index => $item) 
                {
                    $f = AttachmentFile::lookup((int) $y[$index]);
                    // echo sprintf('
                    ?>
                        <li >
                        
                        <i  class="icon-file-text" style="font-size:30px;display: block;float: left;margin-right: 10px;color:#184e81"></i>
                            <h4><a  class="no-pjax truncate filename" style="max-width:600px ;margin-top: 9px;"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>     
                        <?php
                            echo "
                            <a  style='float: right;margin-bottom: 100px;' href='file_maneger.php?deletefromrecycle=".$FileId[$index]."&Recycle_Bin=Recycle_Bin' ><i class='fa icon-trash'></i></a>";
                           
                        }
                        
                    echo "</li>";
                    
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>
        </tbody>
    </table>

<?php  }





if(isset($_GET["staff"])){
    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $GetUsersStaffTicketsQ = "SELECT `ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id` FROM `ost_file_manegment` 
            INNER JOIN `ost_file` ON `ost_file`.`id`=`ost_file_manegment`.`file_id`
            INNER JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
            WHERE `staff_id`=" . $_GET["staff"] ."and `ost_file_manegment`.`deleted`='N'";
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Name,$aid_) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
                    array_push($aid, $aid_);
           
                }
            }
            if (count($x)  > 0) {
                echo "<div>".__("Click on the File to Download .")."</div>
                        <ul id='kb'>";
                        foreach ($x as $index => $item) {
                            $f = AttachmentFile::lookup((int) $y[$index]);
                            // echo sprintf('
                            ?>
                                <li>
                                    <h4><a class="no-pjax truncate filename" style="max-width:600px"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>
                                   
                
                                <?php
                                    echo "..";
                                }
        
                            echo "</li>";
                        
                
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>
        </tbody>
    </table>
<?php
}
    
?>
    
    <?php


?>
<?php
//delete file                  
if(isset($_GET["delete"])){
    $id=$_GET["delete"];
    if(isset($_POST["yes"])){
        $deletefile="UPDATE `ost_file_manegment` SET deleted='Y' WHERE `id`=".$id;
        // echo $deletefile;
        if(db_query($deletefile)){
            unset($_GET["delete"]);
            ?> <script>window.location = "file_maneger.php";</script>  <?php
        }
    }
    if(isset($_POST["no"])){
        unset($_GET["delete"]);
        ?> <script>window.location = "file_maneger.php";</script>  <?php
    }
    if(!isset($_POST["yes"]) || !isset($_POST["no"]) ){
    ?>
<form action="" method="post">
<?php csrf_token(); 
$ssql122="SELECT `name` FROM `ost_file` 
INNER JOIN `ost_file_manegment` on  `ost_file_manegment`.`file_id`=`ost_file`.`id`
WHERE `ost_file_manegment`.`id`=".$id;

if (($ssql122_Res = db_query($ssql122)) && db_num_rows($ssql122_Res)) {
    $Name= db_fetch_row($ssql122_Res);
        
      
}

?>

are you sure you want to delete <?php echo $Name[0]; ?> file ??!
    <input type="submit" name="yes" value="Yes" >
    <input type="submit" name="no" value="No">

    
</form>



<?php
    }
}     
        




if(isset($_GET["Showrooms"])){
    ?>
    <h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    

    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $GetUsersStaffTicketsQ = "SELECT `ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id` FROM `ost_file_manegment` 
            INNER JOIN `ost_file` ON `ost_file`.`id`=`ost_file_manegment`.`file_id`
            INNER JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
            WHERE `user_id` =".$_GET["Showrooms"] ." and `ost_file_manegment`.`deleted`='N'  GROUP by `ost_file_manegment`.`id` ";
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Name,$aid_) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
                    array_push($aid, $aid_);
           
                }
            }
            if (count($x)  > 0) {
                echo "<div>".__("Click on the File to Downloadd .")."</div>
                        <ul id='kb'>";
                        foreach ($x as $index => $item) {
                            $f = AttachmentFile::lookup((int) $y[$index]);
                            // echo sprintf('
                            ?>
                                <li>
                                    <h4><a class="no-pjax truncate filename" style="max-width:600px"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>
                                   
                
                                <?php
                                    echo "..";
                                }
        
                            echo "</li>";
                        
                
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>
        </tbody>
    </table>
<?php
}

//create folder

if(isset($_GET["createfolder"])){
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

    <label ><b>Enter folder name and press create</b></label>
    <input style="padding: 16px 20px;border: none;width: 85%;" type="text" placeholder="Enter Folder Name" name="foldername" required>



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
  window.location = "file_maneger.php";
}
</script>
    <?php
    if (isset($_POST["str1"])) {
        if (isset($_POST['foldername'])) {
            $sql_createnewfolder="INSERT INTO `ost_folder`( `staff_id`, `name`) VALUES (".$thisstaff->getId().",'".$_POST['foldername']."')";
            // echo $sql_createnewfolder; 
            if(db_query($sql_createnewfolder)){
            echo"
            <script> window.location = 'file_maneger.php';</script>
            ";
            }
        }
}
}




//delete folder

if(isset($_GET["deletefolder"])){
    $id=$_GET["deletefolder"];
    if(isset($_POST["yes"])){
        $deletefile="DELETE FROM `ost_folder`  WHERE `id`=".$id;
        // echo $deletefile;
        if(db_query($deletefile)){
            $deletefolder="UPDATE `ost_file_manegment` SET deleted='Y' WHERE `id`=".$id;
            if(db_query($deletefolder)){
            unset($_GET["deletefolder"]);
            ?> <script>window.location = "file_maneger.php";</script>  <?php
        }
    }
    }
    if(isset($_POST["no"])){
        unset($_GET["deletefolder"]);
        ?> <script>window.location = "file_maneger.php";</script>  <?php
    }
    if(!isset($_POST["yes"]) || !isset($_POST["no"]) ){
        $ssql122="SELECT `name` FROM `ost_folder` WHERE `id`=".$_GET["deletefolder"];

if (($ssql122_Res = db_query($ssql122)) && db_num_rows($ssql122_Res)) {
    $Name= db_fetch_row($ssql122_Res);
}
$isEmptyStr="select count(*) from ost_file_manegment where deleted='N' and folder_id=".$_GET["deletefolder"];
if (($isEmptyStr_Res = db_query($isEmptyStr)) && db_num_rows($isEmptyStr_Res)) {
    $isEmpty= db_fetch_row($isEmptyStr_Res);
}
if ($isEmpty[0] > 0){
    ?>
    <form action="" method="post">
    
    <?php csrf_token(); ?>
    
    please make sure that  <?php echo $Name[0];?> folder is empty !!
       
        <input type="submit" name="no" value="Go">
    </form>
    <?php
}
elseif ($isEmpty[0] == 0){
    ?>
<form action="" method="post">
<?php csrf_token(); ?>
are you sure you want to delete <?php echo $Name[0];?> folder ??!
    <input type="submit" name="yes" value="Yes" >
    <input type="submit" name="no" value="No">

    
</form>



<?php
}
    }
}  


if(isset($_GET['addtofolder'])){
?>
 <style>
        .splitscreen {
            display: flex;
        }

        .splitscreen .left {
            flex: 1;
        }

        .splitscreen .right {
            flex: 1;
        }
        /* #cc2{
            display:none;
        }
        #cc3{
            display:none;
        } */
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
  width: 80%;
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

<?php
$delete_from_temp="DELETE FROM `temp` WHERE 1";
if(db_query($delete_from_temp)){
    $getfolderinfo="SELECT `id`,`parent_id`,`name`  FROM `ost_folder` WHERE `id` = ".$_GET['addtofolder'];
    if (($getfolderinfo_Res = db_query($getfolderinfo)) && db_num_rows($getfolderinfo_Res)) {
        while (list($ID,$ParentID,$Name) = db_fetch_row($getfolderinfo_Res)) {
            $getfolder_path="CALL GetFolderPathTPREo(".$ID.",".$ParentID.",'".$Name."',@out_value);";
            // echo $getfolder_path;
            if(db_query($getfolder_path)){
$getRealPath="SELECT `name` FROM `ost_folder` WHERE `id` IN ( SELECT `folderId` FROM `temp`) ORDER BY `ost_folder`.`id` ASC ";

if (($getRealPath_Res = db_query($getRealPath)) && db_num_rows($getRealPath_Res)) {
    echo "<span style='font-size: 16px; color:red;font-weight: bold;'>";
    while (list($Path) = db_fetch_row($getRealPath_Res)) {
       echo $Path."/ " ;
    }
    echo "</span><br/>";
}
            }
        }}

}
?>
<br>
<div >

<div  style="padding-top: 10px;"  class="pull-right flush-right">
<button class="green button" onclick=" document.getElementById('myForm').style.display = 'block';">
           
                    <i class="icon-plus-sign"></i>
                    <?php echo __( 'Add New Folder');?>
                    </button>
</div>
<div class="form-popup" id="myForm" >
  <form  class="form-container"    method="post"  action="" id="formID"  enctype="multipart/form-data">
  <?php csrf_token(); ?>
    <h1>Create Folder</h1>

    <label ><b>Enter folder name and press create</b></label>
    <input style="padding: 16px 20px;border: none;width: 85%;" type="text" placeholder="Enter Folder Name" name="foldername" required>



    <!-- <button type="submit" class="btn" name="str1" >Create</button> -->
    <input type="submit" class="btn" name="str2" value="Create" />
    <button type="button" class=" cancel" onclick="document.getElementById('myForm').style.display = 'none';">Cancel</button>
  </form>
</div>


     <div style="width: auto;height: auto; padding-top: 60px;padding-left: 5px;border: 1px solid  blue;">
     <form   method="post" class="org" action=""   enctype="multipart/form-data">
<?php csrf_token(); ?>
<label for="file">Add items to this folder:</label>
<br>
<br>
         <?php
   $reply_attachments_form = new SimpleForm(array(
    'attachments' => new FileUploadField(array('id'=>'attach',
        'name'=>'attach:reply',
        'configuration' => array('extensions'=>'')))
));

print $reply_attachments_form->getField('attachments')->render();
echo $reply_attachments_form->getMedia();
    
    
?>
<input type="submit" name="submit" value="Submit"  onclick="this.style.visibility = 'hidden'" />
</form> 
</div>
    </div>


<?php
if(isset($_POST["replace"])){
    foreach($_POST['check_list'] as $check) {
    foreach($_SESSION["attachments"]  as  $att) {
        if(array_values($att)[0] == $check ){
    // print_r( array_values($reply_attachments_form->getField('attachments')->getFiles()[0]));
    $DeleteFromstaff='DELETE FROM `ost_file_manegment` WHERE `id` IN (SELECT  `ost_file_manegment`.`id` FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` 
    INNER JOIN (SELECT  `ost_file_manegment`.`id` AS ID FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE
     `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId() .' AND `ost_file`.`created`= (SELECT MAX(`created`) FROM `ost_file` WHERE `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().' ) ORDER BY  `ost_file_manegment`.`id` DESC LIMIT 1) AS notin ON  notin.ID <>  `ost_file_manegment`.`id`
    
    WHERE
    `ost_file`.`name`="'.array_values($att)[1].'" AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().')';
        // echo $DeleteFromstaff;
        //      db_query($Delete);
    db_query($DeleteFromstaff);
    // array_diff( $IDS, array_values($att)[0] );
        }
        }
}
 }
 $IDS=array();
 $attachments = $reply_attachments_form->getField('attachments')->getFiles();
 $_SESSION["attachments"] =$attachments;
 foreach($attachments as $att){
     array_push($IDS, '"'.array_values($att)[1].'"');
 } 
   
        $z = array(); 
        $nn = array(); 
        // $_SESSION["attachments"] =array_values($att)[1];



        $ifExist='SELECT `ost_file`.`id`,`ost_file`.`name` FROM `ost_file` 
        INNER JOIN `ost_file_manegment` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE `ost_file`.`name`IN ('.implode(",", $IDS).')  AND `ost_file_manegment`.`staff_id`='.$thisstaff->getId().' AND  `ost_file_manegment`.`folder_id`='.$_GET["addtofolder"].' AND DATE(`ost_file`.`created`) NOT IN (SELECT MAX(`created`) FROM `ost_file` ) and `ost_file_manegment`.`deleted`="N"  GROUP by `ost_file`.`id`';
        // echo $ifExist;




if (($sql_Res = db_query($ifExist)) && db_num_rows($sql_Res)) {
    while (list($RecurringTaskID,$Name) = db_fetch_row($sql_Res)) {
        array_push($z, $RecurringTaskID);
        array_push($nn, $Name);
       
    }
}
if(!empty($z)){
    ?>
    <form action="" method="post">
    <?php csrf_token(); ?>
    <p style="color: red;">This Files are Already Exist !! Select the Files you Want to Replace ??!</p>
    <?php
    foreach($z as $index => $zz){

       ?>
        <!-- <p style="color: red;"><?php //echo $nn[$index].", "; ?>  </p> -->
        <input  type="checkbox" id="vehicle1" name="check_list[]" value="<?php echo $zz; ?>">
        <label style="color: red;" for="vehicle1"> <?php echo $nn[$index].", "; ?></label><br>    
        <br>
        <?php }?>
<br>
        <input type="submit" name="replace" value="replace" >
        <input type="submit" name="confirm" value="Don't replace">
        
            
        </form>
        
       
        
        
        
        
        <?php
    }
    $attachments = $reply_attachments_form->getField('attachments')->getFiles();
    foreach($attachments as $att){
    $sql = "INSERT INTO `ost_file_manegment` (`id`, `user_id`, `staff_id`, `dep_id`,`file_id`,`folder_id`,`deleted`) 
    VALUES (NULL, NULL," .$thisstaff->getId(). "," . $thisstaff->getDeptId() .  "," . array_values($att)[0]   . ",".$_GET["addtofolder"].",'N')";
//   echo $sql;
  if (db_query($sql)) {
      $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($att)[0].", NULL, '0', NULL)";
      db_query($sqll);
    //   unset($_GET["addtofolder"]);
    //   echo '<script>window.location = "file_maneger.php?addtofolder='.$_GET['addtofolder'].'";</script>';
      
   } 
}
// GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);
?>
<h1 style="margin:10px 0">
        <a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"><i class="refresh icon-refresh"></i>
            <?php echo __('Files'); ?>
        </a>
    </h1>
    <table style="margin-top:2em" class="list queue tickets" border="0" cellspacing="1" cellpadding="2" width="100%">
        <caption><?php echo  $results; ?></caption>
       <hr>
        <tbody>
            <?php
            $x=array();
            $y=array();
            $aid=array();
            $FileId=array();
            $CreateDate=array();
            if (isset($_GET["sort"])){
                if($_GET["sort"]=="ID"){
                    if($_GET["order"]=="DESC"){
                        $OrderBy = "  `ost_file`.`name` DESC";
                    }
                elseif ($_GET["order"]=="ASC") {
                    $OrderBy = "   `ost_file`.`name` ASC";
                }
                }

                if($_GET["sort"]=="date"){
                    if($_GET["order"]=="DESC"){
                        $OrderBy = " `ost_file`.`created`  DESC";
                    }
                    elseif ($_GET["order"]=="ASC") {
                        $OrderBy= "  `ost_file`.`created`  ASC";
                    }
                  }
            }
            else{
                $OrderBy="  `ost_file`.`created`  DESC";
            }
            $GetUsersStaffTicketsQ = "SELECT `ost_file`.`id`,`ost_file`.`name`,`ost_attachment`.`id`,`ost_file`.`created`,`ost_file_manegment`.`id` FROM `ost_file_manegment` 
            INNER JOIN `ost_file` ON `ost_file`.`id`=`ost_file_manegment`.`file_id`
            INNER JOIN `ost_attachment` ON `ost_attachment`.`file_id`=`ost_file`.`id`
            WHERE `staff_id`=" . $thisstaff->getId()." AND `ost_file_manegment`.`folder_id`=".$_GET["addtofolder"]." and `ost_file_manegment`.`deleted`='N' GROUP by `ost_file_manegment`.`id`   ORDER BY ". $OrderBy;;
            // echo $GetUsersStaffTicketsQ;
            if (($GetUsersStaffTickets_Res = db_query($GetUsersStaffTicketsQ)) && db_num_rows($GetUsersStaffTickets_Res)) {
                while (list($ID, $Name,$aid_,$CreateDate_,$FileId_) = db_fetch_row($GetUsersStaffTickets_Res)) {
                    
                    array_push($x, $Name);
                    array_push($y, $ID);
                    array_push($aid, $aid_);
                    array_push($CreateDate, $CreateDate_);
                    array_push($FileId, $FileId_);
           
                }
            }
            $xfolder=array();
            $yfolder=array();
            $sql_folder="SELECT `id`,`name`  FROM  `ost_folder` WHERE `parent_id` =".$_GET["addtofolder"];
            // echo $sql_folder;
            if (($sql_folder_Res = db_query($sql_folder)) && db_num_rows($sql_folder_Res)) {
                while (list($ID, $Name) = db_fetch_row($sql_folder_Res)) {
                    
                    array_push($xfolder, $Name);
                    array_push($yfolder, $ID);
                }
            }
            //show folder
            if (count($xfolder)  > 0) {
                echo "<div>".__("Your Folders.")."</div>
                        <ul id='kb'>";
                foreach ($xfolder as $index => $item) {
                    ?>
                        <li>
                            <h4><a class="no-pjax truncate filename" style="max-width:600px"  href="file_maneger.php?addtofolder=<?php echo $yfolder[$index]; ?>"  ><?php echo  $item; ?> </a> - <span></span></h4>
                           
        
                        <?php
                            echo '
                            <a  href="file_maneger.php?deletefolder='.$yfolder[$index].'" ><i class="fa icon-trash"></i></a>';
                    echo "</li>";
                }
                echo "</ul>";
            } 
         else {
            echo __("NO Folders found");
        }

echo "<hr>";
            //show files
            if (count($x)  > 0) {
                echo  "<div>".__("Click on the File to Download  .")."</div>
                        <ul >";
                foreach ($x as $index => $item) 
                {
                    $f = AttachmentFile::lookup((int) $y[$index]);
                    // echo sprintf('
                    ?>
                        <li style="display: inline;">
                        
                        <i  class="icon-folder-open-alt" style="font-size:30px;display: block;float: left;margin-right: 10px;color:#184e81"></i>
                            <h4><a  class="no-pjax truncate filename" style="max-width:600px ;margin-top: 9px;"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>     
                        <?php
                            echo "
                            <a  style='float: right;margin-bottom: 100px;' href='file_maneger.php?delete=".$FileId[$index]."' ><i class='fa icon-trash'></i></a>";
                           
                        }
                        
                    echo "</li>";
                    
                echo "</ul>";
            } else {
                echo __("NO Files found");
            }
            ?>

           
        </tbody>
    </table>
    
<?php
if (isset($_POST["str2"])) {
    if (isset($_POST['foldername'])) {
        $sql_createnewfolder="INSERT INTO `ost_folder`( `staff_id`, `name`,`parent_id`) VALUES (".$thisstaff->getId().",'".$_POST['foldername']."',".$_GET["addtofolder"].")";
        // echo $sql_createnewfolder; 
        if(db_query($sql_createnewfolder)){
        echo"
        <script> 
        window.location.reload();
        </script>
        ";
       
        }
    }
}
}


if(isset($_GET["deletefilefolder"])){
    $id=$_GET["deletefilefolder"];
    if(isset($_POST["yes"])){
        $deletefile="UPDATE `ost_file_manegment` SET deleted='Y' WHERE `id`=".$id;
        // echo $deletefile;
        if(db_query($deletefile)){
            unset($_GET["deletefilefolder"]);
            ?> <script>window.location = "file_maneger.php";</script>  <?php
        }
    }
    if(isset($_POST["no"])){
        unset($_GET["deletefilefolder"]);
        ?> <script>window.location = "file_maneger.php";</script>  <?php
    }
    if(!isset($_POST["yes"]) || !isset($_POST["no"]) ){
    ?>
<form action="" method="post">
<?php csrf_token(); ?>
are you sure you want to delete this folder ??!
    <input type="submit" name="yes" value="Yes" >
    <input type="submit" name="no" value="No">

    
</form>



<?php
    }
}
    if(isset($_GET["deletefromrecycle"])){
        $id=$_GET["deletefromrecycle"];
        if(isset($_POST["yes"])){
            $deletefile="DELETE FROM `ost_file_manegment` WHERE `id`=".$id;
            // echo $deletefile;
            if(db_query($deletefile)){
                unset($_GET["deletefromrecycle"]);
                ?> <script>window.location = "file_maneger.php?Recycle_Bin=Recycle_Bin";</script>  <?php
            }
        }
        if(isset($_POST["no"])){
            unset($_GET["deletefromrecycle"]);
            ?> <script>window.location = "file_maneger.php?Recycle_Bin=Recycle_Bin";</script>  <?php
        }
        if(!isset($_POST["yes"]) || !isset($_POST["no"]) ){
        ?>
    <form action="" method="post">
    <?php csrf_token(); 
    $ssql122="SELECT `name` FROM `ost_file` 
    INNER JOIN `ost_file_manegment` on  `ost_file_manegment`.`file_id`=`ost_file`.`id`
    WHERE `ost_file_manegment`.`id`=".$id;
    
    if (($ssql122_Res = db_query($ssql122)) && db_num_rows($ssql122_Res)) {
        $Name= db_fetch_row($ssql122_Res);
    }
    ?>
    are you sure you want to delete <?php echo $Name[0]; ?> file ??!
        <input type="submit" name="yes" value="Yes" >
        <input type="submit" name="no" value="No">
    </form>
    <?php
        }
}
        // require_once(STAFFINC_DIR.$inc);
        require_once(STAFFINC_DIR . 'footer.inc.php');
?>