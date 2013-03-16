//$('#tags').tags();
/*
if(menu_id){
    $('#new_connection').autocomplete(sys_url+"content/find_connection/?ID="+menu_id, {
        'onItemSelect':function(a){
            target = $('span',a).attr('rel');
            
            $.get(sys_url+"content/add_connection/?ID="+menu_id+"&target="+target, function(){
                $("<li><span rel='"+target+"'>"+$('span',a).html()+"</span><img src='img/cms/icons/delete.png' rel='"+target+"' /></span>").appendTo('.page_connections');
                $('#new_connection').focus();
            });

            $('#new_connection').val('');
        },
        'formatItem':function(row){
            return "<span rel='"+row[1]+"'>"+row[0]+"</span>";
        }
    });

    $('.page_connections img').live('click',function(){
        $.get(sys_url+"content/delete_connection/?ID="+menu_id+"&target="+$(this).attr('rel'));
        $(this).parent().remove();
    });
}
*/