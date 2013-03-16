
//Dynamic tabs

$('.dyn_tabs li').click(function(){
	var panel=$(this).attr('id').replace('dyn_link_','');
	$('.dyn_content').hide();
	$('#dyn_tab_'+panel).show();
	$('.dyn_tabs li').removeClass('selected');
	$(this).addClass('selected');
});

window.setTimeout(function(){
    $('#dashboard_boxes').masonry({
        itemSelector: '.dashboard_box',
        isAnimated:true
    });
}, 600);

$(window).resize();
