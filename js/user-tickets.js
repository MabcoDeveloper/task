var CurrentSelectID = 1;

function AddNewUserSelect() {
	var $UsersSelect = $('#users-select-1').clone();
	$UsersSelect.find('i').remove();
	$UsersSelect.attr('id', 'users-select-' + (CurrentSelectID + 1));
	$UsersSelect.append('<font class="error">&nbsp;*</font><i class="icon-minus remove-user-icon" id="remove-user-icon-' + (CurrentSelectID + 1) + '" onclick="RemoveUserSelect(\'' + $UsersSelect.attr('id') + '\')"></i>');
  	$('#users-selects').append($UsersSelect);
	$('#add-user-select').hide();
	$('#remove-user-icon-' + CurrentSelectID).hide();
	$('#users-select-' + CurrentSelectID  + ' select').attr('disabled', true);
	$('#users-select-' + (CurrentSelectID + 1)  + ' select').attr('required', true);

	RemoveLastId = false;
	
	for (var i = 0; i < LastSelectedUserId.length; i++) {
	    $('#users-select-' + (CurrentSelectID + 1)  + ' select option[value="' + LastSelectedUserId[i] + '"]').hide();
	}
	
	$('#users-select-' + (CurrentSelectID + 1) + ' select').attr('disabled', false);
	CurrentSelectID += 1;
}

function RemoveUserSelect($ID) {
	if ($ID === 'users-select-1') {
		$('#users-select-1 select').children("option:selected").prop('selected', '');
		$("#add-user-select").hide();
		$('#CC-label').hide();
		$('#users-selects').hide();
		$('#add-user-cc-but').parent().show();
	} else {
		var IsRemoveId = false;

		if ($('#users-select-' + CurrentSelectID + ' select').children("option:selected").val() != "") {
			IsRemoveId = true;
		}

		if ($('#' + $ID).remove()) {
			if (IsRemoveId) {
				LastSelectedUserId.pop();
			}

			$('#add-user-select').show();
			$('#remove-user-icon-' + (CurrentSelectID - 1)).show();
			$('#users-select-' + (CurrentSelectID - 1)  + ' select').attr('disabled', false);
			$('#users-select-' + (CurrentSelectID + 1)  + ' select option').show();

			CurrentSelectID -= 1;

			RemoveLastId = true;
		}
	}
}

var LastSelectedUserId = [],
	RemoveLastId = false;
//Yaseen
function SecUserSelected($Select) {
		
   
	if ($Select.value != "") {
		$("#add-user-select").show();
		$($Select).parent().children('input[type="hidden"]').attr('value', $Select.value);

		if (RemoveLastId) {
			LastSelectedUserId.pop();
			RemoveLastId = false;
		}

		LastSelectedUserId.push($Select.value);
		RemoveLastId = true;
	} else {
		$("#add-user-select").hide();
		// $('#CC-label').hide();
	}
}
function UserSelected($Select) {
	$('#CC-label').show();

	if ($Select.value != "") {
		$("#add-user-select").show();
    	$($Select).parent().children('input[type="hidden"]').attr('value', $Select.value);

    	if (RemoveLastId) {
			LastSelectedUserId.pop();
			RemoveLastId = false;
    	}

		LastSelectedUserId.push($Select.value);
		RemoveLastId = true;
	} else {
		$("#add-user-select").hide();
		// $('#CC-label').hide();
	}
}

function PullChildrenHelpTopics($PID) {
	$.ajax(
		'ajax.php/form/help-topics/' + $PID, {
			dataType: 'json',
			success: function(json) {
				if (json != '') {
					$('#children_section').css('display', 'table-row');
					$('#children_topicId').html('');
					$('#children_topicId').append($("<option />").val("").text("— Select a Help Topic —"));

					$.each(json, function(val, text) {
						$('#children_topicId').append($("<option />").val(val).text(text));
					});
				} else {
					alert('لا يوجد ابناء لهذا العنوان, الرجاء التواصل مع قسم الاتمتة!');
				}
			}
		}
	);
}