$(document).ready(function(){
	//$('#'+$('input[name="mode"]').val()).addClass('selected');
	$('#content label.switch').click(function(){
		$('label.switch').removeClass('selected');
		$(this).addClass('selected');
		//$('input',$(this)).attr('checked','checked');
		
		if($('#content input[name="mode"]:checked').val()=='url'){
			$('.toggle_service').hide();
			$('.toggle_url').show();
		}
		else{
			$('.toggle_service').show();
			$('.toggle_url').hide();
		}
	});
});