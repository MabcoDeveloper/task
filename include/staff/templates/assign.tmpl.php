<?php
global $cfg;

$form = $form ?: AssignmentForm::instantiate($info);

if (!$info[':title'])
    $info[':title'] = __('Assign');
?>
<h3 class="drag-handle"><?php echo $info[':title']; ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<div class="clear"></div>
<hr/>
<?php
if ($info['error']) {
    echo sprintf('<p id="msg_error">%s</p>', $info['error']);
} elseif ($info['warn']) {
    echo sprintf('<p id="msg_warning">%s</p>', $info['warn']);
} elseif ($info['msg']) {
    echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
} elseif ($info['notice']) {
   echo sprintf('<p id="msg_info"><i class="icon-info-sign"></i> %s</p>',
           $info['notice']);
}


$action = $info[':action'] ?: ('#');
?>
<div style="display:block; margin:5px;">
<form class="mass-action" method="post"
    name="assign"
    id="<?php echo $form->getFormId(); ?>"
    action="<?php echo $action; ?>">
    <table width="100%">
        <?php
        if ($info[':extra']) {
            ?>
        <tbody>
            <tr><td colspan="2"><strong><?php echo $info[':extra'];
            ?></strong></td> </tr>
        </tbody>
        <?php
        }
       ?>
        <tbody>
            <tr><td colspan=2>
             <?php
             $options = array('template' => 'simple', 'form_id' => 'assign');
             $form->render($options);
             ?>
            </td> </tr>
        </tbody>
    </table>
    <hr>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="reset" value="<?php echo __('Reset'); ?>">
            <input type="button" name="cancel" class="close"
            value="<?php echo __('Cancel'); ?>">
        </span>
        <span class="buttons pull-right">
            <input type="submit" value="<?php
            echo $verb ?: __('Assign'); ?>">
        </span>
     </p>
</form>

<script type="text/javascript">
    if ($('#assign select').eq(1).find('option').eq(1).val().includes("s")) {
        $('#assign select').eq(0).attr('required', true);
        $('#assign select').eq(0).find('option[value=""]').prop('selected', true);
        
        $('#assign select').eq(1).find('optgroup').eq(1).remove();
        $('#assign select').eq(1).parent().parent().hide();

        $('#assign select').eq(0).on('change', function() {
            $('#assign select').eq(1).find('option').prop('selected', false);
            $('#assign select').eq(1).find('option[value=""]').prop('selected', true);

            var SelectedDeptId = $(this).find(":selected").val();

            if (SelectedDeptId != '') 
                $('#assign select').eq(1).parent().parent().show();
            else
                $('#assign select').eq(1).parent().parent().hide();
            
            if (AllAgentsIds.constructor != Object) {
                AllAgentsIds = JSON.parse(AllAgentsIds);
            }

            var AgentsIds = 0;
            $('#assign select').eq(1).find('option').show();

            if (AllAgentsIds[SelectedDeptId]) {
                AgentsIds = AllAgentsIds[SelectedDeptId].split("_");

                $('#assign select').eq(1).find('option').each(function() {
                    if (!AgentsIds.includes(this.value) && !this.value.includes("t")) {
                        $(this).hide();
                    }
                });
            }
        });
    } else {
        $('#assign select').eq(0).parent().parent().hide();
    }
</script>

</div>
<div class="clear"></div>
