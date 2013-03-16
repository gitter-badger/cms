/**
 * @author Artjom Kurapov
 * @since 10.10.11 23:11
 */
$('.dashboard_comments .delete').click(function() {
    var row = $(this).parents('tr:first');
    $.get($(this).attr('href'), function() {
        row.fadeOut();
    });
    return false;
});

$('.dashboard_comments tr').mouseover(function(){
    $('.delete',this).show();
}).mouseout(function(){
    $('.delete',this).hide();
});

$('.dashboard_comments tr').mouseout();