<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');
if(isset($_GET["link"] ) ){
    $z = array();
    $x = array();
    $y=array();
    $GetAllStaff = "SELECT `ost_general_informs`.`content` , CONCAT(`ost_staff`.`firstname`, ' ', `ost_staff`.`lastname`), `ost_general_informs`.`created_at` FROM `ost_general_informs` INNER JOIN `ost_staff` ON `ost_staff`.`staff_id`=`ost_general_informs`.`staff_id`  WHERE `ost_general_informs`.`Flag_user`=1";
    if (($GetRecurringTasks_Res = db_query($GetAllStaff)) && db_num_rows($GetRecurringTasks_Res)) {
        while (list($RecurringTaskID, $RecurringTaskTitle,$c_date) = db_fetch_row($GetRecurringTasks_Res)) {
            array_push($z, $RecurringTaskTitle);
            array_push($x, $RecurringTaskID);
            array_push($y, $c_date);
        }}

?>
    <div class="has_bottom_border" style="margin-bottom:5px; padding-top:5px;">
        <div class="pull-left">
            <h2><?php echo __('General Informs');?></h2>
        </div>
        <div class="clear"></div>
    </div>

    <?php
    echo '<div>'.__('').'</div>
    <ul id="kb">';
    foreach ($x as $index => $item) {
echo sprintf('
    <li>
        <h4><a class="truncate" style="max-width:600px" >%s (%d)</a> - <span>%s</span></h4>
        %s '
    );
    
        echo '<p/><div>';
        
            echo sprintf('<div><i class="icon-folder-open-alt"></i>  '.$item.'
                    -   <span>%s</span></div>',
                    $z[$index]." - ".$y[$index] 
                    );
       
        echo '</div>';
        echo '<p/><div>';
        
      
    
    echo '</div>';
echo '</li>';
echo '<hr>';
}
echo '</ul>';
        // foreach ($x as $index => $item) {
        //     echo sprintf('<div><i class="icon-folder-open-alt"></i>
        //             <a href="kb.php?cid=%d">%s (%d)</a> - <span>%s</span></div>',
        //             $item
        //             );
        // }
}
else{
?>
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['topicId']) { //Search
    $faqs = FAQ::allPublic()
        ->annotate(array(
            'attachment_count'=>SqlAggregate::COUNT('attachments'),
            'topic_count'=>SqlAggregate::COUNT('topics')
        ))
        ->order_by('question');

    if ($_REQUEST['cid'])
        $faqs->filter(array('category_id'=>$_REQUEST['cid']));

    if ($_REQUEST['topicId'])
        $faqs->filter(array('topics__topic_id'=>$_REQUEST['topicId']));

    if ($_REQUEST['q'])
        $faqs->filter(Q::all(array(
            Q::ANY(array(
                'question__contains'=>$_REQUEST['q'],
                'answer__contains'=>$_REQUEST['q'],
                'keywords__contains'=>$_REQUEST['q'],
                'category__name__contains'=>$_REQUEST['q'],
                'category__description__contains'=>$_REQUEST['q'],
            ))
        )));

    include CLIENTINC_DIR . 'kb-search.inc.php';

} else { //Category Listing.
    include CLIENTINC_DIR . 'kb-categories.inc.php';
}
}?>
