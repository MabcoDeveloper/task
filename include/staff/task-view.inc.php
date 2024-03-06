<div id="task_content">
<?php if(isset($_SESSION["task_page_number"])){
?>
<button type="button"><a href="tasks.php?sort=&order=&p=<?php echo $_SESSION["task_page_number"]; ?>">Back</a></button>
<?php
}
else{
  ?>
  <button type="button"><a href="tasks.php">Back</a></button>
  <?php
}?>
<br>
<br>
<?php
require STAFFINC_DIR.'templates/task-view.tmpl.php';
?>
</div>
<?php if(isset($_SESSION["task_page_number"])){
?>
<button type="button"><a href="tasks.php?sort=&order=&p=<?php echo $_SESSION["task_page_number"]; ?>">Back</a></button>
<?php
}
else{
  ?>
  <button type="button"><a href="tasks.php">Back</a></button>
  <?php
}