<?php
if (!defined('OSTCLIENTINC')) die('Access Denied!');
$info = array();
if ($thisclient && $thisclient->isValid()) {
    $info = array(
        'name' => $thisclient->getName(),
        'email' => $thisclient->getEmail(),
        'phone' => $thisclient->getPhoneNumber()
    );
}

$info = ($_POST && $errors) ? Format::htmlchars($_POST) : $info;

$EndOfYearInventoryCheckup = false; // 'true' to enter the end of the year inventory checkup mode

$form = null;
if (!$info['topicId']) {
    if (array_key_exists('topicId', $_GET) && preg_match('/^\d+$/', $_GET['topicId']) && Topic::lookup($_GET['topicId']))
        $info['topicId'] = intval($_GET['topicId']);
    else
        $info['topicId'] = $cfg->getDefaultTopicId();
}

$forms = array();
if ($info['topicId'] && ($topic = Topic::lookup($info['topicId']))) {
    foreach ($topic->getForms() as $F) {
        if (!$F->hasAnyVisibleFields())
            continue;
        if ($_POST) {
            $F = $F->instanciate();
            $F->isValidForClient();
        }
        $forms[] = $F->getForm();
    }
}

?>
<h1><?php echo __('Open a New Ticket'); ?></h1>
<p><?php echo __('Please fill in the form below to open a new ticket.'); ?></p>
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
    <?php csrf_token(); ?>
    <input type="hidden" name="a" value="open">
    <table width="100%" cellpadding="1" cellspacing="0" border="0">
        <tbody>
            <?php
            if (!$thisclient) {
                $uform = UserForm::getUserForm()->getForm($_POST);
                if ($_POST) $uform->isValid();
                $uform->render(array('staff' => false, 'mode' => 'create'));
            } else { ?>
                <tr>
                    <td colspan="2">
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td><?php echo __('Email'); ?>:</td>
                    <td><?php
                        echo $thisclient->getEmail(); ?></td>
                </tr>
                <tr>
                    <td><?php echo __('Client'); ?>:</td>
                    <td><?php
                        echo Format::htmlchars($thisclient->getName()); ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tbody>
            <tr>
                <td colspan="2">
                    <hr />
                    <div class="form-header" style="margin-bottom:0.5em">
                        <b><?php echo __('User(s)'); ?></b>
                    </div>
                </td>
            </tr>
            <!-- Yaseen -->
            <tr>
                <td colspan="2">
                        <?php
                      
                        //set available_time in ost_user_account  value = 1 to activate secound user 
                        $checked_Q = 'SELECT `available_time` FROM `ost_user` WHERE id = ' . $thisclient->getId() . '';
                        if (($checked_Res = db_query($checked_Q)) && db_num_rows($checked_Res)) {
                            $Res = db_fetch_row($checked_Res);
                            if ($Res[0] && $Res[0] > 0) {
                                $checked = $Res[0];
                            }
                        }
                        if ($checked == 1) {
                            echo " <label id='lbl_add_user' style='display: block; font-weight: bold;'>add secound user";
                            echo "<input type='checkbox' id='add_user' name='add_user' ></label>";
                        } ?>
                        <script>
                            $("#add_user").on("click", function() {
                                if ($(this).is(':checked')) {
                                    document.getElementById("sec_users-select").style.display = "block";
                                } else {
                                    document.getElementById("sec_users-select").style.display = "none";
                                }
                            });
                        </script>
                </td>
            </tr>
            <tr>
                <td>
                    <a id="add-user-cc-but" class="inline button" style="overflow:inherit;" href="#" onclick="$('#users-selects').show();$('#teams-selects').show();$(this).parent().hide()">
                        <i class="icon-plus"></i> Add User & CC
                    </a>
                </td>
            </tr>
            <tr>
                <td id="users-selects" style="display: none;">
                    <h5>To User : </h5>
                    <div id="users-select-1" class="users-select" style="display: inline-block;margin-top: .3em;margin-left: 1em;">
                        <select class="to-user" id="select_first_user" name="select_first_user" style="margin-bottom: 1em" onchange="UserSelected(this)">
                            <option value="" selected="selected">&mdash; <?php echo __('Select a User'); ?> &mdash;</option>
                            <?php
                            $users = User::objects();

                            foreach ($users as $key => $value) {
                                if ($value->ht['id'] != $thisclient->getId()) {
                                    echo sprintf('<option value="%d">%s</option>', $value->ht['id'], $value->ht['name']);
                                }
                            }
                            ?>
                        </select>
                        <input class="test" type="hidden" name="to-user[]" value="">
                    </div>
                     <!-- Yaseen -->
                     <div id="sec_users-select" class="users-select" style="display: inline-block;margin-top: .3em;margin-left: 1em;display: none;">
                        <select id="select_sec_user" name="select_sec_user" class="to-user" style="margin-bottom: 1em" onchange="SecUserSelected(this)">
                            <option value="" selected="selected">&mdash; <?php echo __('Select a User'); ?> &mdash;</option>
                            <?php
                            $users = User::objects();

                            foreach ($users as $key => $value) {
                                if ($value->ht['id'] != $thisclient->getId()) {
                                    echo sprintf('<option value="%d">%s</option>', $value->ht['id'], $value->ht['name']);
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <br /><br />
                    <h5 id="CC-label" style="display: none">CC : </h5>
                </td>
            </tr>
            <tr>
                <td>
                    <a id="add-user-select" class="inline button" style="overflow:inherit;display: none;" href="#" onclick="AddNewUserSelect()">
                        <i class="icon-plus"></i> Add CC
                    </a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr />
                    <div class="form-header" style="margin-bottom:0.5em">
                        <b><?php echo __('Help Topic'); ?></b>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <select id="topicId" name="topicId" onchange="PullChildrenHelpTopics(this.value)">
                        <option value="" selected="selected" disabled>&mdash; <?php echo __('Select a Parent Help Topic'); ?> &mdash;</option>

                        <!-- End of the year inventory checkup -->
                        <?php if ($EndOfYearInventoryCheckup) : ?>
                            <script>
                                var data = $(':input[name]', '#dynamic-form').serialize();
                                $.ajax('ajax.php/form/help-topic/84', {
                                    data: data,
                                    dataType: 'json',
                                    success: function(json) {
                                        $('#dynamic-form').empty().append(json.html);
                                        $(document.head).append(json.media);
                                    }
                                });
                            </script>
                        <?php endif; ?>
                        <!-- End of the year inventory checkup -->

                        <?php
                        if ($topics = Topic::getParentHelpTopics()) {
                            if ($EndOfYearInventoryCheckup) {
                                // <!-- End of the year inventory checkup -->
                                echo '<option value="84" selected="selected">جرد نهاية 2019</option>';
                                // <!-- End of the year inventory checkup -->
                            } else {
                                foreach ($topics as $id => $name) {
                                    echo sprintf('<option value="%d">%s</option>', $id, $name);
                                }
                            }
                        } else { ?>
                            <option value="0"><?php echo __('General Inquiry'); ?></option>
                        <?php
                        } ?>
                    </select>
                    <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
                </td>
            </tr>
            <?php if (!$EndOfYearInventoryCheckup) { ?>
                <tr id="children_section" style="display:none;">
                    <td colspan="2">
                        <select id="children_topicId" name="topicId" onchange="javascript:
                    var data = $(':input[name]', '#dynamic-form').serialize();
                    $.ajax(
                        'ajax.php/form/help-topic/' + this.value, {
                            data: data,
                            dataType: 'json',
                            success: function(json) {
                                $('#dynamic-form').empty().append(json.html);
                                $(document.head).append(json.media);
                                $('#dynamic-form tr:nth-child(2)').find('input').val($('#children_topicId').find(':selected').html());
                                $('#dynamic-form tr:nth-child(2)').find('input').attr('readonly', true);
                                $('#dynamic-form tr:nth-child(2)').find('input').css('direction', 'rtl');
                                $('#dynamic-form tr:nth-child(2)').find('input').css('border', 'none');
                                $('#dynamic-form tr:nth-child(2)').find('input').css('font-weight', 'bold');
                                $('#dynamic-form tr:nth-child(2)').find('input').css('text-align', 'left');
                                $('#dynamic-form tr:nth-child(2)').find('input').css('margin-top', '1em');
                            }
                    });">
                            <option value="" selected="selected" disabled>&mdash; <?php echo __('Select a Help Topic'); ?> &mdash;</option>
                        </select>
                        <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tbody id="dynamic-form">
            <?php
            $options = array('mode' => 'create');

            foreach ($forms as $form) {
                include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
            } ?>
        </tbody>
        <tbody>
            <?php
            if ($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
                if ($_POST && $errors && !$errors['captcha'])
                    $errors['captcha'] = __('Please re-enter the text again');
            ?>
                <tr class="captchaRow">
                    <td class="required"><?php echo __('CAPTCHA Text'); ?>:</td>
                    <td>
                        <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
                        &nbsp;&nbsp;
                        <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
                        <em><?php echo __('Enter the text shown on the image.'); ?></em>
                        <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
                    </td>
                </tr>
            <?php
            } ?>
            <tr>
                <td colspan=2>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <input type="checkbox" name="external_approval" <?php if (isset($gender) && $gender == "no") echo "checked"; ?> value="no"> تمت الموافقة الخارجية 

    <hr />
    <p class="buttons" style="text-align:center;">
        <input type="submit" value="<?php echo __('Create Ticket'); ?>">
        <input type="reset" name="reset" value="<?php echo __('Reset'); ?>">
        <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>" onclick="javascript:
            $('.richtext').each(function() {
                var redactor = $(this).data('redactor');
                if (redactor && redactor.opts.draftDelete)
                    redactor.draft.deleteDraft();
            });
            window.location.href='index.php';">
    </p>
</form>

<style type="text/css">
    .remove-user-icon {
        margin-left: .5em;
        color: #bd0909;
        vertical-align: middle;
        cursor: pointer;
    }
</style>