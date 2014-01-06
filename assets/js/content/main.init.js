//var $j = $.noConflict();
var MenuTree;
//anchor navigation
$(document).ready(function () {
	var body = new Content.Views.BodyView();
	Content.menu = new Content.Views.Menu();
	Content.contextMenuView = new Content.Views.ContextMenu();
	Content.notePanel = new Content.Views.Note();
	Content.page = new Content.Views.Page({
		el: $('#content_area')
	});
	Content.searchView = new Content.Views.NavBar();
	Content.menuPanel = new Content.Views.MenuPanel();
	Content.modalPage = new Content.Views.ModalPage();
	Content.sideTagList = new Content.Views.SideTagList();

	Content.strUrl = sys_url;
	Content.inactivity = 0;
	Content.activeSession.init();


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
			$.get(sys_url + 'content/menu/menu_precise_move/?ID=' + source.attr('id').replace('node', '') + '&parentID=' + destination.attr('id').replace('node', '') + '&pos=' + pos, function (responce) {
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

			Content.page.ID = ID;

		},
		animate: true,
		urlReadNodes: sys_url + 'content/menu/menu_preload/?', //+MenuTree.langID+'&ID='+ID+''
		urlAnchorBase: sys_url + 'content/'
	});

	Content.router = new Content.Routers.Main();
	Backbone.history.start();

	$('#upload_area').upload5({
		beforeLoad: function () {
			if (Content.page.ID >= 0) {
				this.gate = file_upload_url;// + '?parentID=' + Content.page.ID;
				this.gate += '?parentID=' + Content.page.ID;
			}
			else {
				this.gate = file_upload_moduleonly_url;
			}
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


//		if($(this).hasClass('modal_trigger')){
//		}
//		else{
//			Content.AddElement(Content.page.ID, $(this).data('module'));
//		}
//	});

	if (1 * $.cookie("panel_resize_left") > 0) {
		var pos = 1 * $.cookie("panel_resize_left");

		$('#panel_resize').css({
			left: pos
		});

		if (pos > 50 && pos < 1600) {
			$('#panel').width(pos);
			$('#top').css('left', pos + 'px');
			$('#content').css('left', (pos + 26) + 'px');
		}
	}

	$('#panel_resize').draggable({
		axis: 'x',
		iframeFix: true,

		drag: function (e, ui) {
			var pos = ui.position.left
			$('#panel').width(pos);
			$('#top').css('left', pos + 'px');
			$('#content').css('left', (pos + 5) + 'px');
		},

		stop: function (e, ui) {
			$.cookie("panel_resize_left", ui.position.left);
			var pos = ui.position.left
			$('#panel').width(pos);
			$('#top').css('left', pos + 'px');
			$('#content').css('left', (pos + 5) + 'px');
		}
	});
	//}
});
