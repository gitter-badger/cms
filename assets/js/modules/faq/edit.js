function add_faq_value(){
	$('#faq_values').append(
	'<tr><td><textarea class="expanding_height" name="questions[]">'+$('#question').val()+'</textarea></td>'+
	'<td><textarea class="expanding_height" name="answers[]">'+$('#answer').val()+'</textarea></td>'+
	'<td class="actions">'+
	'<span class="a move_up">'+t('Up')+'</span> '+
	'<span class="a move_down" >'+t('Down')+'</span> '+
	'<span class="a red remove" >'+t('Remove')+'</span></td></tr>');
	
	$('#question').val('');
	$('#answer').val('');
	$('#question').focus();
	
	rebind_events();
	return false;
}

function rebind_events(){
	$('textarea.expanding_height').unbind().focus(function(){
		$(this).height(60);
	}).blur(function(){
		$(this).height(20);
	});	
	
	$('.actions .red').unbind().click(function(){
		$(this).parent().parent().remove();
	});
	
	$('.move_up').click(function(){
		if($(this).parent().parent().prevAll().length>1){
			$(this).parent().parent().prev().before('<tr>'+$(this).parent().parent().html()+'</tr>');
			$(this).parent().parent().remove();
			rebind_events();
		}
	});
	$('.move_down').click(function(){
		if($(this).parent().parent().nextAll().length>0){
			$(this).parent().parent().next().after('<tr>'+$(this).parent().parent().html()+'</tr>');
			$(this).parent().parent().remove();
			rebind_events();
		}
	});
}

$(document).ready(function(){
	$('#question').focus();
	rebind_events();
});