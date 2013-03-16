$(document).ready(function(){
	//$('#'+$('input[name="mode"]').val()).addClass('selected');
	$('#content label.switch').click(function(){
		$('label.switch').removeClass('selected');
		$(this).addClass('selected');
		//$('input',$(this)).attr('checked','checked');
		
		$('.toggler').hide();
		$('.toggle_'+$('#content input[name="mode"]:checked').val()).show();
	});
});