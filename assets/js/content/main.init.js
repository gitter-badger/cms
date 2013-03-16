//var $j = $.noConflict();
var MenuTree;
//anchor navigation
$(document).ready(function () {
	var body = new Content.Views.BodyView();
	Content.menu = new Content.Views.Menu();
	Content.contextMenuView = new Content.Views.ContextMenu();
	Content.notePanel = new Content.Views.Note();
	Content.page = new Content.Views.Page();
	Content.searchView = new Content.Views.NavBar();
	Content.menuPanel = new Content.Views.MenuPanel();
	Content.modalPage = new Content.Views.ModalPage();

	Content.strUrl = sys_url;
	Content.inactivity = 0;
	Content.activeSession.init();

	if (1 * $.cookie("panel_resize_left") > 0) {
		var pos = 1 * $.cookie("panel_resize_left");

		$('#panel_resize').css({
			left: pos
		});

		if(pos>50 && pos <1600){
			$('#panel').width(pos);
			$('#top').css('left', pos + 'px');
			$('#content').css('left', (pos + 26) + 'px');
		}
	}

	$('#panel_resize').draggable({
		axis: 'x',
		iframeFix: true,
		drag: function (e, ui) {
		},
		stop: function (e, ui) {
			$.cookie("panel_resize_left", ui.position.left);
			pos = ui.position.left
			$('#panel').width(pos);
			$('#top').css('left', pos + 'px');
			$('#content').css('left', (pos + 26) + 'px');
		}
	});

	$(window).resize(function () {
		body.resize();
	});


	$('#dialog_wrapper').click(function (e) {
		if (!e) {
			var e = window.event;
		}

		e.cancelBubble = true;
		if (e.stopPropagation) {
			e.stopPropagation();
		}

		$('#dialog_wrapper').hide();
	});


	$("a.ajax").live('click', function () {
		var strRel = $(this).attr('rel');
		var strHref = $(this).attr('href');
		var strID = $(this).attr('id');

		if (strRel) {
			window.location = sys_url + 'content/#' + strRel;
			//Content.openMenu=strID;
		}

		Content.page.load(strHref);

		return false;
	});

	$(".page").live('click', function () {
		Content.router.navigate('page/' + $(this).attr('data-id'), true);
		return false;
	});

	$('a#dashboard').click(function () {
		window.location = sys_url + 'content/#dashboard';
		return false;
	});

	//Initialize Menu
	MenuTree = $('#content_menu').simpleTree({
		autoclose: true,
		afterClick: function (node) {
			//alert("text-"+$('span:first',node).text());
			//Content.notePanel.set('load', t('Opening') + ' ' + $('span:first', node).text());
			Content.EditElement($(node).attr('id').replace('node', ''));
		},
		afterDblClick: function (node) {
			//alert("text-"+$('span:first',node).text());
		},
		afterMove: function (destination, source, pos) {
			//alert("destination-"+destination.attr('id')+" source-"+source.attr('id')+" pos-"+pos);
			Content.notePanel.set(t('Moving..'), '');
			$.get(sys_url + 'content/content/menu_precise_move/?ID=' + source.attr('id').replace('node', '') + '&parentID=' + destination.attr('id').replace('node', '') + '&pos=' + pos, function (responce) {
				Content.notePanel.set(t('Move completed'), 'success', 3);
			});

		},
		afterAjax: function () {
			//Content.notePanel.set('done', t('Done'), 1);
		},
		afterContextMenu: function (e, node) {
			ID = $(node).attr('id').replace('node', '');

			e = e ? e : window.event;

			if (typeof(e.clientX) == 'undefined') {
				var mouse_position = { 'x': 250, 'y': 10 };
			}
			else
				var mouse_position = { 'x': e.clientX, 'y': e.clientY };


			var scroll_position = {'x': 0, 'y': 0};
			if (typeof( window.pageYOffset ) == 'number') {
				scroll_position = {'x': window.pageXOffset, 'y': window.pageYOffset};
			} else if (document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop )) {
				scroll_position = {'x': document.documentElement.scrollLeft, 'y': document.documentElement.scrollTop};
			} else if (document.body && ( document.body.scrollLeft || document.body.scrollTop )) {
				scroll_position = {'x': document.body.scrollLeft, 'y': document.body.scrollTop};
			}

			$('#context_menu').html($('#context_menu_prototype').html().replace(/\[ID\]/g, ID));
			//MenuTree.Select(ID);
			$('#context_menu').css('left', mouse_position.x + scroll_position.x + 'px');
			$('#context_menu').css('top', mouse_position.y + scroll_position.y + 'px');
			$('#context_menu').removeClass('hidden').show();

			$("#swfuploader").css('width', 100);
			$("#swfuploader").css('height', 15);
			$('#swfuploader').css('left', mouse_position.x + scroll_position.x);
			$('#swfuploader').css('top', mouse_position.y + scroll_position.y + $('#context_menu').height() - $('#swfuploader').height());
			$('#swfuploader').show();


			$("#swfuploader").hover(function () {
				$('#context_menu li.menu_upload').addClass('selected');
			}, function () {
				$('#context_menu li.menu_upload').removeClass('selected');
			});

			Content.page.ID = ID;

		},
		animate: true,
		urlReadNodes: sys_url + 'content/content/menu_preload/?', //+MenuTree.langID+'&ID='+ID+''
		urlAnchorBase: sys_url + 'content/'
	});

	Content.router = new Content.Routers.Main();
	Backbone.history.start();
/*
	$("#swfuploader").click(function () {
		$('#context_menu').hide();
		$("#swfuploader").css('left', 0);
		$("#swfuploader").css('top', 0);
		$("#swfuploader").css('width', 1);
		$("#swfuploader").css('height', 1);
		$("#swfuploader").blur();
	});

	//Context menu file upload
	Content.flash_upload_handler = new SWFUpload({
		upload_url: file_upload_url,
		flash_url: swfu_flash_url,
		file_size_limit: "5 MB",
		post_params: {
			"parentID": Content.parentID,
			"PHPSESSID": session_id//,
			//"langID":Content.langID//$('#lang_content option:selected').val()
		},
		file_types_description: "Files",
		file_types: "*.*",
		file_post_name: "file",

		button_placeholder_id: "swfupload_prototype",
		button_width: 100,
		button_height: 18,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.DEFAULT,

		file_dialog_complete_handler: function () {
			this.startUpload();
		},

		upload_start_handler: function (file) {
			this.addFileParam(file.id, "parentID", Content.page.ID);
			//this.addFileParam(file.id, "langID"	  , Content.langID);

			Content.notePanel.set(t('Starting upload') + ': ' + file.name, '');
		},

		upload_success_handler: function (file) {
			Content.notePanel.set(t('Upload finished') + ': ' + file.name, '');
		},
		upload_progress_handler: function (file, bytesLoaded, bytesTotal) {
			var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
			//var progress = new FileProgress(file, this.customSettings.progressTarget);
			Content.notePanel.set(t('Uploading ') + ': ' + file.name + ' (' + percent + '%)', '');

		},
		upload_error_handler: function (file) {
			Content.notePanel.set(t('Upload failed') + ': ' + file.name, 'error');
		},
		queue_complete_handler: function () {
			$('#swfuploader').hide();
			MenuTree.Load(Content.page.ID);
		}
	});
*/
	$('#content_menu').upload5({
		beforeLoad: function () {
			this.gate = file_upload_url + '?parentID=' + Content.page.ID;
			this.can_proceed = true;
			/*
			 if(Content.openMenu=='dashboard'){
			 this.can_proceed=true;
			 }
			 else this.can_proceed=false;
			 */

		},

		onProgress: function (event) {
			if (event.lengthComputable) {
				var percentage = Math.round((event.loaded * 100) / event.total);
				if (percentage < 100) {
					Content.notePanel.set(t('Uploading file..') + percentage + '%', '');
				}
			}
		},

		onComplete: function (event, txt) {
			/* If we got an error display it. */


			Content.notePanel.set(t('Upload complete'), 'success', 10);

			MenuTree.Load(MenuTree.getParentID(Content.page.ID));
		}
	});

	$('#panel_resize img').click(function(){
		if($(this).hasClass('modal_trigger')){
			Content.modalPage.addPage(Content.page.ID, $(this).data('module'));
		}
		else{
			Content.AddElement(Content.page.ID, $(this).data('module'));
		}
	});
	//}
});
