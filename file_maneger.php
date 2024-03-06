<?php

/*************************************************************************
    tasks.php

    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
 **********************************************************************/

// require('staff.inc.php');

require_once('client.inc.php');
require('secure.inc.php');
session_start();

$page = '';

//Navigation
$nav->setActiveNav('file');
$open_name = _P(
    'queue-name',
    /* This is the name of the open tasks queue */
    'My Files'
);






require(CLIENTINC_DIR . 'header.inc.php');
//view all files 
$orderWays = array('DESC' => '-', 'ASC' => '');
if ($_REQUEST['order'] && isset($orderWays[strtoupper($_REQUEST['order'])])) {
    $negorder = $order = $orderWays[strtoupper($_REQUEST['order'])];
} else {
    $negorder = $order = 'ASC';
}
if (!isset($_GET["addtofolder"]) && !isset($_GET["upload"]) && !isset($_GET["delete"]) && !isset($_GET["share"])  && !isset($_GET["staff"]) && !isset($_GET["deletefolder"]) ) {
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
  
  border: none;
}

.dropdown {
  position: relative;
  display: inline-block;
  padding: 15px;;
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
 <a style="padding: 10px;"    class="icon-mail-reply" href="?upload=upload">
    <?php echo _('Upload File');
    ?>
</a>


<a style="padding: 10px;"    class="icon-mail-reply" href="?createfolder=createfolder">
    <?php echo _('Create Folder');
    ?>
</a>

<a class="icon-reply" href="?share=share">
    <?php echo _('Files Shared with me');
    ?>
</a>



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








</div>
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
            WHERE `ost_file_manegment`.`flag_share`=1 AND `user_id`=" . $thisclient->getId()." AND `ost_file_manegment`.`folder_id` IS Null  GROUP by `ost_file_manegment`.`id`   ORDER BY ". $OrderBy;;
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
            $sql_folder="SELECT `id`,`name`  FROM  `ost_folder` WHERE `parent_id` = 0 AND `user_id`=".$thisclient->getId();
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
                        <ul>";
                foreach ($xfolder as $index => $item) {
                    ?>
                        <li style=' display: block;
                        margin-right: 6px;
                        margin-bottom: 10px;
                        background: url(./assets/default/images/kb_large_folder.png) top left no-repeat;'>
                            <h4 style="margin-left: 50px;"><a class="no-pjax truncate filename" style="max-width:600px"  href="file_maneger.php?addtofolder=<?php echo $yfolder[$index]; ?>"  ><?php echo  $item; ?> </a> - <span></span></h4>
                           
        
                        <?php
                            echo '
                            <a style="margin-left: 50px;" href="file_maneger.php?deletefolder='.$yfolder[$index].'" ><i class="fa icon-trash"></i></a>';
                        
                    echo "</li>";
                }
                echo "</ul>";
            }
            else {
                echo __("NO Folders found");
            } 

echo "<hr>";
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
                            echo "
                            <a href='file_maneger.php?delete=".$FileId[$index]."' ><i class='fa icon-trash'></i></a>";
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
     <div style="width: auto; ">
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
<hr>
<h3 class="drag-handle"><?php echo "Select a User to share this file with"; ?></h3>
<br>
<?php
$z = array();
        $x = array();
        $types = array();
        $GetAllStaff = "SELECT `id`,`name` FROM `ost_user` WHERE `fcm_token` IS NOT NULL";
        if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
            while (list($RecurringTaskID, $RecurringTaskTitle) = db_fetch_row($GetRecurringTasks_Res)) {
                array_push($z, $RecurringTaskTitle);
                array_push($x, $RecurringTaskID);
            }}
            ?>

              
            <select style="width: 100%;"  class="modal-body" id="subject" name = 'subject[]' multiple="multiple"  size="6"> 
            <option disabled selected value> -- select a user -- </option>  
            <?php foreach ($z as $index => $item) {


?>
    <option value="<?php echo $x[$index]; ?>"><?php echo $item; ?></option>
<?php } ?> 
            </select> 
            <br>
            <br>
<input type="submit" name="submit" value="Submit"  onclick="this.style.visibility = 'hidden'" />
</form> 
</div>
    </div>
<!-- <form action="file_maneger.php?upload=upload" method="post"
enctype="multipart/form-data">
<?php //csrf_token(); ?>
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="submit" value="Submit" />
</form>  -->
    
<?php

// $file = $_FILES['file']['name'];
// $path = pathinfo($file);
// $filename = $path['filename'];
// $ext = $path['extension'];
// $temp_name = $_FILES['file']['tmp_name'];
// $path_filename_ext = $target_dir.$filename.".".$ext;



// $uploadOk = 1;
// $target_dir = "upload/".$thisstaff->getName()."/";
// if (!file_exists("upload/".$thisstaff->getName())) {
//     mkdir("upload/".$thisstaff->getName(), 0777, true);
// }
// $file = $_FILES['file']['name'];
// $path = pathinfo($file);
// $ext = $info['extension']; // get the extension of the file
// $temp_name = $_FILES['file']['tmp_name'];
// $newname = $_FILES['file']['name']; 
// $path_filename_ext = $target_dir.$_FILES['file']['name'];
// // Check if file already exists
// if (file_exists($target_file)) {
//     echo 'Sorry, file already exists.';
//     $uploadOk = 0;
//   }
// // Check file size
// if ($_FILES["fileToUpload"]["size"] > 500000) {
//     echo "Sorry, your file is too large.";
//     $uploadOk = 0;
//   }
// if ($_FILES["file"]["error"] > 0 || $uploadOk == 0)
//   {
    
//         echo count($Myfile);
       
//     // echo '<pre>'; print_r($attachments); echo '</pre>';
               
                        
                
//   echo "Error: " . $_FILES["file"]["error"] . "<br />";
//   }
// else
//   {
//   if(move_uploaded_file($_FILES['file']['tmp_name'],$path_filename_ext)){
//     $sql = 'INSERT INTO `ost_file_manegment` (`id`, `user_id`, `staff_id`, `dep_id`, `type`, `size`, `file_key`, `signature`, `name`, `created`) 
//     VALUES (NULL, NULL,' .$thisstaff->getId(). ',' . $thisstaff->getDeptId() . ',"' . $_FILES["file"]["type"] . '","' . $_FILES["file"]["size"] . '","' . $path_filename_ext . '",' . "NULL" . ',"' . $_FILES["file"]["name"] . '",' . 'Now()'   . ')';
// //   echo $sql;
//   if (db_query($sql)) {
//     unset($_GET["upload"]);
//     }
    
//   echo "<br>";
//   echo "Upload: " . $_FILES["file"]["name"] . "<br />";
//   echo "Type: " . $_FILES["file"]["type"] . "<br />";
//   echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
//   echo "Stored in: " .$path_filename_ext;
//   }
//  }

//  print_r(array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1]);
//  $Delete='DELETE FROM `ost_file` WHERE `name`="'.array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1].'"';
if(isset($_POST["subject"]))  
        { 
            
            // Retrieving each selected option 
            foreach ($_POST['subject'] as $subject){
            $sqlshare = "INSERT INTO `ost_file_manegment` (`id`,`user_id`,`file_id`,`flag_share`) 
            VALUES (NULL, " .$subject. ","  . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ", 2 )";
            echo $sql;
      if (db_query($sqlshare)) {
          $sqllshare="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
          db_query($sqllshare);
          
          
       }   
                // print "You selected $subject<br/>"; 
        }
    }
    else{
        echo ""; 
    } 
if(isset($_POST["replace"])){
    // print_r( array_values($reply_attachments_form->getField('attachments')->getFiles()[0]));
    $DeleteFromstaff='DELETE FROM `ost_file_manegment` WHERE `id` IN (SELECT  `ost_file_manegment`.`id` FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` 
    INNER JOIN (SELECT  `ost_file_manegment`.`id` AS ID FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE  `ost_file_manegment`.`flag_share`=1 AND 
     `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId() .' AND `ost_file`.`created`= (SELECT MAX(`created`) FROM `ost_file` WHERE `ost_file_manegment`.`flag_share`=1 AND  `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().' ) ORDER BY  `ost_file_manegment`.`id` DESC LIMIT 1) AS notin ON  notin.ID <>  `ost_file_manegment`.`id`
    
    WHERE
    `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().')';
// echo $DeleteFromstaff;
//      db_query($Delete);
    db_query($DeleteFromstaff);
}
$_SESSION["attachments"] =array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1];
$z = array(); 
$ifExist='SELECT `ost_file`.`id` FROM `ost_file` 
INNER JOIN `ost_file_manegment` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE `ost_file_manegment`.`flag_share`=1 AND  `ost_file`.`name`="'.array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().' AND DATE(`ost_file`.`created`) NOT IN (SELECT MAX(`created`) FROM `ost_file` )';
// echo $ifExist;
if (($sql_Res = db_query($ifExist)) && db_num_rows($sql_Res)) {
    while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
        array_push($z, $RecurringTaskID);
       
    }
}
if(!empty($z)){

?>
<form action="" method="post">
<?php csrf_token(); ?>
<p style="color: red;">This File is Already Exist Do you Really Want to Replace it ??!</p>
    <input type="submit" name="replace" value="replace" >
    <input type="submit" name="confirm" value="Don't replace">

    
</form>

<?php

    }
    
    

        

 $sql = "INSERT INTO `ost_file_manegment` (`id`,  `user_id`,`file_id`,`flag_share`) 
    VALUES (NULL, " .$thisclient->getId(). ","  . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ", 1 )";
    //   echo $sql;
      if (db_query($sql)) {
          $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
          db_query($sqll);
          unset($_GET["upload"]);
          
       } 
    GenericAttachments::keepOnlyFileIds($reply_attachments_form->getField('attachments')->getFiles(), false);
    

 
 
    
 }

 




            






    
?>
    
    <?php


?>
<?php
//delete file                  
if(isset($_GET["delete"])){
    $id=$_GET["delete"];
    if(isset($_POST["yes"])){
        $deletefile="DELETE FROM `ost_file_manegment` WHERE `id`=".$id;
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
<?php csrf_token(); ?>
are you sure you want to delete this file ??!
    <input type="submit" name="yes" value="Yes" >
    <input type="submit" name="no" value="No">

    
</form>



<?php
    }
}   


//show shared file


if (isset($_GET['share']) ) {
    
    ?>

    <style>
    .dropbtn {
      /* background-color: #444444; */
      
      border: none;
    }
    
    .dropdown {
      position: relative;
      display: inline-block;
      padding: 15px;;
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
      <a  href="file_maneger.php?share&sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" >
        <?php echo _('Sort By Name');
        ?>
    </a>
    <a  href="file_maneger.php?share&sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" >
        <?php echo _('Sort By Date');
        ?>
    </a>
      </div>
    </div>
    
    
    
    
    
    
    
    
    </div>
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
                WHERE `ost_file_manegment`.`flag_share`=2 AND `user_id`=" . $thisclient->getId()."  ORDER BY ". $OrderBy;;
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
                    echo "<div>".__("Click on the File to Downloads .")."</div>
                            <ul id='kb'>";
                    foreach ($x as $index => $item) {
                        $f = AttachmentFile::lookup((int) $y[$index]);
                        // echo sprintf('
                        ?>
                            <li>
                                <h4><a class="no-pjax truncate filename" style="max-width:600px"  href="<?php echo $f->getDownloadUrl(['id' => $aid[$index]]); ?>" download="<?php echo $item; ?>" target="_blank" ><?php echo  $item; ?> </a> - <span></span></h4>
                               
            
                            <?php
                                echo "
                                <a href='file_maneger.php?delete=".$FileId[$index]."' ><i class='fa icon-trash'></i></a>";
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
      width: 100%;
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
                $sql_createnewfolder="INSERT INTO `ost_folder`( `user_id` , `name`) VALUES (".$thisclient->getId().",'".$_POST['foldername']."')";
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
            $deletefolder="DELETE FROM `ost_file_manegment`  WHERE `folder_id`=".$id;
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
    <div >
    
    <div  style="padding-top: 10px;"  class="pull-right flush-right">
    <button style="margin-right: 10px;"  class="green button" onclick=" document.getElementById('myForm').style.display = 'block';">
               
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
    
    
         <div style="width: auto;height: 150px; padding-top: 60px;padding-left: 5px;border: 1px solid  blue;">
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
        // print_r( array_values($reply_attachments_form->getField('attachments')->getFiles()[0]));
        $DeleteFromstaff='DELETE FROM `ost_file_manegment` WHERE `id` IN (SELECT  `ost_file_manegment`.`id` FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` 
        INNER JOIN (SELECT  `ost_file_manegment`.`id` AS ID FROM `ost_file_manegment` INNER JOIN `ost_file` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE
         `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId() .' AND  `ost_file_manegment`.`folder_id`='.$_GET["addtofolder"].'  AND `ost_file`.`created`= (SELECT MAX(`created`) FROM `ost_file` WHERE `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().' AND  `ost_file_manegment`.`folder_id`='.$_GET["addtofolder"].'  ) ORDER BY  `ost_file_manegment`.`id` DESC LIMIT 1) AS notin ON  notin.ID <>  `ost_file_manegment`.`id`
        
        WHERE
        `ost_file`.`name`="'.$_SESSION["attachments"].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().' AND  `ost_file_manegment`.`folder_id`='.$_GET["addtofolder"].')';
    // echo $DeleteFromstaff;
    //      db_query($Delete);
        db_query($DeleteFromstaff);
    }
    $_SESSION["attachments"] =array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1];
    $z = array(); 
    $ifExist='SELECT `ost_file`.`id` FROM `ost_file` 
    INNER JOIN `ost_file_manegment` ON `ost_file_manegment`.`file_id`=`ost_file`.`id` WHERE `ost_file`.`name`="'.array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[1].'" AND `ost_file_manegment`.`user_id`='.$thisclient->getId().' AND  `ost_file_manegment`.`folder_id`='.$_GET["addtofolder"].' AND DATE(`ost_file`.`created`) NOT IN (SELECT MAX(`created`) FROM `ost_file` )';
    // echo $ifExist;
    if (($sql_Res = db_query($ifExist)) && db_num_rows($sql_Res)) {
        while (list($RecurringTaskID) = db_fetch_row($sql_Res)) {
            array_push($z, $RecurringTaskID);
           
        }
    }
    if(!empty($z)){
    
    ?>
    <form action="" method="post">
    <?php csrf_token(); ?>
    <p style="color: red;">This File is Already Exist Do you Really Want to Replace it ??!</p>
        <input type="submit" name="replace" value="replace" >
        <input type="submit" name="confirm" value="Don't replace">
    
        
    </form>
    
    <?php
    
        }
        
        $sql = "INSERT INTO `ost_file_manegment` (`id`, `user_id`,`file_id`,`folder_id`) 
        VALUES (NULL, " .$thisclient->getId().  "," . array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0]   . ",".$_GET["addtofolder"].")";
    //   echo $sql;
      if (db_query($sql)) {
          $sqll="INSERT INTO `ost_attachment` (`id`, `object_id`, `type`, `file_id`, `name`, `inline`, `lang`) VALUES (NULL, 'NULL', 'T', ".array_values($reply_attachments_form->getField('attachments')->getFiles()[0])[0].", NULL, '0', NULL)";
          db_query($sqll);
        //   unset($_GET["addtofolder"]);
        //   echo '<script>window.location = "file_maneger.php?addtofolder='.$_GET['addtofolder'].'";</script>';
          
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
                WHERE `user_id`=" . $thisclient->getId()." AND `ost_file_manegment`.`folder_id`=".$_GET["addtofolder"]." GROUP by `ost_file_manegment`.`id`   ORDER BY ". $OrderBy;;
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
                            <ul >";
                    foreach ($xfolder as $index => $item) {
                        ?>
                            <li style=' display: block;
                        margin-right: 6px;
                        margin-bottom: 10px;
                        background: url(./assets/default/images/kb_large_folder.png) top left no-repeat;'>
                                <h4 style="margin-left: 50px;" ><a class="no-pjax truncate filename" style="max-width:600px"  href="file_maneger.php?addtofolder=<?php echo $yfolder[$index]; ?>"  ><?php echo  $item; ?> </a> - <span></span></h4>
                               
            
                            <?php
                                echo '
                                <a  style="margin-left: 50px;" href="file_maneger.php?deletefolder='.$yfolder[$index].'" ><i class="fa icon-trash"></i></a>';
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
                            <ul >";
                    foreach ($x as $index => $item) {
                        $f = AttachmentFile::lookup((int) $y[$index]);
                        // echo sprintf('
                        ?>
                            <li style="display: inline;">
                            
                            <i  class="icon-folder-open-alt" style="font-size:30px;display: block;color:#184e81"></i>
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
            $sql_createnewfolder="INSERT INTO `ost_folder`( `user_id`, `name`,`parent_id`) VALUES (".$thisclient->getId().",'".$_POST['foldername']."',".$_GET["addtofolder"].")";
            // echo $sql_createnewfolder; 
            if(db_query($sql_createnewfolder)){
            echo"
            <script> 
           
            </script>
            ";
           
            }
        }
    }
    }


    

        
        // require_once(STAFFINC_DIR.$inc);
        require(CLIENTINC_DIR . 'footer.inc.php');
?>