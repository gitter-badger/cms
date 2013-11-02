/**
 * Content panels and management object
 */
var Content = {
	Routers: [],
	Views  : [],
	Models : [],

	contextMenuView: null,


	openMenu       : 'dashboard',
	strUrl         : '',
	autosaveEnabled: false,
	inactivity     : 0,

	ID        : 1,
	parentID  : 1,
	langID    : 'rus',
	copyBuffer: [],

	Dialog: function (id) {
		$('#dialog').html($('#' + id).html());
		$('#dialog_wrapper').fadeIn();
	},

	Upload: function (parentID) {

	},

	resizeEditor: function () {

		var topSide = ($('#tabs-1').outerHeight() - $('#article_value').outerHeight());

		$('#article_value iframe').height(
				$('#content').height() - 214 - topSide
		);
		$('#article_value textarea').height(
				$('#content').height() - 214 - topSide
		);
	},

	EditElement: function (ID) {
		$('#context_menu').hide();
		//Content.notePanel.set('load', t('Opening page for editing'));
		Content.page.ID = ID;
		Content.parentID = 1;
		Content.page.load(sys_url + 'content/content/edit/?ID=' + ID);
	},

	AddElement: function (parentID, module) {
		//Content.Frame('content');
		//MenuTree.Select(parentID);
		if (typeof(module) == 'undefined') {
			module = 'article';
		}

		Content.page.ID = 0;
		Content.parentID = parentID;

		var src = sys_url + 'content/content/edit/?parentID=' + parentID + '&langID=' + Content.langID + '&module=' + module;

		$.get(src, '', function (request) {
			Content.page.attachPageEvents(request);
		});

	},

	CopyElement: function (ID) {
		Content.copyBuffer.push(ID);
	},

	PasteElement: function (ID) {
		$.getJSON(sys_url + '/content/paste/?parentID=' + ID + '&ids=' + Content.copyBuffer.join(','), '', function (json) {
			MenuTree.Load(json.ID, true);
		});
		Content.copyBuffer = [];
	},

	EmbedElement: function (id) {
		$.getJSON(link_embed_info + '?id=' + id, function (responce) {
			if (responce.module == 'image') {
				Content.page.editors[0].execCommand('inserthtml', "<img rel='" + id + "' src='" + responce.html + "' />");
			}
			else {
				Content.page.editors[0].execCommand('inserthtml', " <span rel='" + id + "' class='embed embed_" + responce.module + "'>" + responce.html + "</span> ");
			}
		});
	},

	DeleteElement: function (ID) {
		$.getJSON(sys_url + 'content/content/delete/?ID=' + ID + '', function (json) {
			MenuTree.Load(json.ID, true);
		});
	}
};

Content.Routers.Main = Backbone.Router.extend({

	routes: {
		""                          : "dashboard",
		"dashboard"                 : "dashboard",
		"search/:q"                 : "search",
		"page/:id"                  : "page",
		"tag/*path"                 : "tag",
		"settings/*path"            : "settings",
		"install/updates/"            : "install",
		"translation/*path"         : "translation",
		"add/:module/:parent/:lang/": "add",
		"*path"                     : "other"
	},

	dashboard: function () {
		Content.page.load(sys_url + 'content/dashboard/');

		//Open content tab by default
		$('#content_menu').show();
	},

	page: function (id) {
		$('#content_menu').show();
		MenuTree.editDeepChild(id);
		Content.page.ID = id;
	},

	add: function (module, parent, lang) {
		Content.page.load(sys_url + 'content/content/edit/?parentID=' + parent + '&lang=' + lang + '&module=' + module);
	},

	install   : function () {
		Content.page.load(sys_url + 'content/install/updates');
	},
	settings   : function (path) {
		Content.page.load(sys_url + 'content/settings/' + path);
	},
	translation: function (path) {
		Content.page.load(sys_url + 'content/translation/' + path);
	},
	tag        : function (path) {
		Content.page.load(sys_url + 'content/tag/' + path);
	},

	other: function (path) {
		var anchor = path.split('/');

		Content.openMenu = anchor[0];

		if (anchor[1]) {

			Content.menuPanel.selectByRel(anchor[0] + '/' + anchor[1]);
		}
		Content.page.load(sys_url + 'content/call/' + path);

	},

	search: function (q) {
		$('#search_q').val(q);
		Content.searchView.loadSearch();
	}
});

Content.activeSession = {
	init: function () {
		window.setTimeout(function () {
			Content.activeSession.check(1);
		}, 1000);

		window.setTimeout(function () {
			Content.activeSession.extend();
		}, autologout_ping * 1000);
	},

	update: function () {
		if (Content.inactivity > autologout_passive) {
			Content.notePanel.set('done', t('Welcome back'), 1);
			Content.inactivity = 0;
			Content.activeSession.extend();
		}
	},

	extend: function () {
		$.getJSON(sys_url + 'content/profile/ping/?sid=' + session_id, function (responce) {
			if (!responce.user_id) {
				Content.notePanel.set(t('Logging out'), 'error');
				window.location = sys_url + 'content/profile/logout/';
			}
		});
	},

	check: function (seconds) {

		var secFullLeft = 1 * autologout_active + 1 * autologout_passive - Content.inactivity;
		var minLeft = Math.floor(secFullLeft / 60);
		var secLeft = secFullLeft - 60 * minLeft;
		var strMsg = t('Inactivity logout in');

		if (minLeft > 0) {
			strMsg = strMsg + ' ' + minLeft + ' ' + t('minutes');
		}
		if (minLeft > 0 && secLeft > 0) {
			strMsg = ' ' + strMsg + ' ' + t('and');
		}
		if (secLeft > 0) {
			strMsg = strMsg + ' ' + secLeft + ' ' + t('seconds');
		}

		if (Content.inactivity > (1 * autologout_passive + 1 * autologout_active)) {
			Content.notePanel.set(t('Logging out'), 'error');
			window.location = sys_url + 'content/profile/logout/';
		}
		else if (Content.inactivity > 1 * autologout_passive) {
			Content.notePanel.set(strMsg, 'error');
		}

		Content.inactivity = Content.inactivity + seconds;
	}
};

Content.Views.Note = Backbone.View.extend({
	el: '#notification',

	events: {
		//'click':'requestDesktopPermissions'
	},

	blocked: false,

	initialize: function () {
		$('#note_panel').fadeIn('slow');
	},

	set        : function (message, message_class, fadeout_sec) {

		if (!message && message_class == 'error') {
			message = t('Error') + '!';
		}

		if (!message && message_class == 'load') {
			message = t('Loading') + '...';
			//jQuery('#load_icon').fadeIn();
		}
		//else jQuery('#load_icon').fadeOut();

		if (typeof(message) == 'undefined' && message_class == 'done') {
			message = t('Ready') + '...';
		}

		$(this.el).attr('className', '');
		$(this.el).html(message);
		$(this.el).addClass('alert');

		if (message_class != '') {
			$(this.el).addClass('alert-' + message_class);
		}
		$(this.el).show();

		setTimeout("Content.notePanel.dismissNote();", 5000);
	},
	/*
	 requestDesktopPermissions:function() {
	 if (window.webkitNotifications && window.webkitNotifications.checkPermission() != 0) {
	 window.webkitNotifications.requestPermission();
	 }
	 },
	 */
	dismissNote: function () {
		$(this.el).html('');
		$(this.el).hide();
	}
});

Content.Views.BodyView = Backbone.View.extend({
	el    : 'body',
	events: {
		'click'  : 'clickBody',
		'keydown': 'keyboardInput',
		'resize' : 'resize'
	},

	resize: function () {
		Content.resizeEditor();
	},

	clickBody: function () {
		Content.contextMenuView.hide();
		Content.activeSession.update();
	},

	keyboardInput: function () {
		Content.activeSession.update();
		if (window.event) {
			event = window.event;
		}
	}
});

Content.Views.Menu = Backbone.View.extend({
	id: '#content_menu',

	initialize: function () {
	},
	resize    : function () {
	}
});

Content.Views.ContextMenu = Backbone.View.extend({
	el: '#context_menu',

	hide: function () {
		$(this.el).hide();
	}
});

Content.Views.ContentPage = Backbone.View.extend({

	addTabbing: function () {
		var me = this;

		$('ul.nav-tabs li', me.el).live('click', function () {
			$('.tabbed > div', me.el).hide();
			$('#' + $(this).data('tab'), me.el).show();
			$('ul.nav-tabs li', me.el).removeClass('active');
			$(this).addClass('active');
		});
		$('.tabbed > div', me.el).hide();
		$('.tabbed > div:first', me.el).show();
		$('ul.nav-tabs li:first', me.el).click();

	},

	addEditor: function () {
		var me = this;
		if (navigator.userAgent.match(/iPad/i) == null) {
			$('.wysiwyg', me.el).each(function (i, o) {
				Content.page.editors[i] = $(this).redactor({
					focus              : true,
					css                : sys_url + 'vendor/Gratheon/CMS/assets/css/modules/article/edit.wysiwyg.css',
					cleanUp            : false,
					autoformat         : false,
					convertDivs        : false,
					removeClasses      : false,
					removeStyles       : false,
					imageUpload        : file_upload_url,
					fileUpload         : file_upload_url,
					resize             : true,
					imageUploadFunction: function () {
					}
				});
			});
		}
	},

	addDynamicForms: function () {
		var me = this;

		$('form.json').ajaxForm({
			type    : 'post',
			dataType: 'json',
			success : function (json) {
				if (json.msg) {
					Content.notePanel.set(json.msg, json['class'], 10);
				}
				else if (json.ID) {
					MenuTree.Load(json.ID, true);
					me.hide();
				}
				else if (json.url) {
					Content.page.load(json.url);
				}
			}
		});


		$('form.ajax').ajaxForm({
			dataType: 'html',
			success : function (responce) {
				me.attachPageEvents(responce);
			},
			error   : function (response) {
				$(me.el).html(response);
			}
		});

		$.datepicker.setDefaults({
			dateFormat: 'dd.mm.yy'
		});

		$('input.date', me.el).datepicker();
		$("input.date", me.el).mask("99.99.9999");
		$("input.time", me.el).mask("99:99");
		$("input.datetime", me.el).mask("99.99.9999 99:99");
	},

	attachPageEvents: function (responce) {
		var me = this;

		Content.activeSession.update();
		if (!responce.ID) {
//			this.$el.hide();
			this.$el.html(responce);
			this.$el.show();
		}

		$('#savebutton', this.el).click(function () {
			Content.notePanel.set(t('Saving page'), '', 1);
		});

		$('.container_template div', this.el).click(function () {
			$('.container_template div', me.el).removeClass('active');
			$(this).addClass('active');
			$('.container_template div.active input', me.el).attr("checked", "checked");
		});

		$('.advanced_settings_switch', me.el).click(function () {
			$('.advanced_settings', me.el).slideToggle();
			if ($('.advanced_settings_switch img', me.el).hasClass('advanced_settings_switch_closed')) {
				$('.advanced_settings_switch img', me.el).removeClass('advanced_settings_switch_closed').addClass('advanced_settings_switch_open');
			}
			else {
				$('.advanced_settings_switch img', me.el).removeClass('advanced_settings_switch_open').addClass('advanced_settings_switch_closed');
			}
		});

		this.addTabbing();

		this.addEditor();

		this.addDynamicForms();

		Content.resizeEditor();

		$('img.modal_trigger').click(function () {
			Content.modalPage.addPage(Content.page.ID, $(this).data('module'));
		});

		$('.module_select', me.el).change(function () {
			if (Content.page.ID) {
				Content.page.load('/content/edit/?ID=' + Content.page.ID + '&module=' + $(this).val() + '&langID=' + Content.langID);
			}
			else {
				Content.page.load('/content/edit/?parentID=' + Content.parentID + '&module=' + $(this).val() + '&langID=' + Content.langID);
			}
		});
	}
});

Content.Views.ModalPage = Content.Views.ContentPage.extend({
	el    : '#pageModal',
	events: {
		'.close': 'hide'
	},

	hide: function () {
		$(this.el).hide();
		$(this.el).html('');
	},

	addPage: function (parentID, module) {
		if (typeof(module) == 'undefined') {
			module = 'article';
		}

		Content.page.ID = 0;
		Content.parentID = parentID;

		var src = sys_url + 'content/content/edit/?parentID=' + parentID + '&langID=' + Content.langID + '&module=' + module;

		var me = this;
		$.get(src + '&modal=1', function (r) {
			$(me.el).html(r);
			$(me.el).fadeIn();
			$('.close', me.el).live('click', function () {
				me.hide();
			});
			me.attachPageEvents(r);
		});
	}
});

Content.Views.Page = Content.Views.ContentPage.extend({

	editors        : [],
	autosaveEnabled: false,

	initialize: function () {
		$(this.el).fadeIn('slow');

		//autosave drafts
		window.setTimeout(function () {
			Content.page.saveDraft();
		}, 15000);
	},

	hide: function () {
		$(this.el).hide();
	},

	saveDraft: function () {
		if (!this.autosaveEnabled) {
			return false;
		}

		var article_content = Content.page.editors[0].getCode();

		//save to local storage
		if (window.localStorage !== null) {
			window.localStorage.setItem('article_draft_content', article_content);
			window.localStorage.setItem('article_draft_timestamp', (new Date()).getTime());
		}

		var aSerializedData = $('#content_form').formSerialize();
		aSerializedData.article_content = article_content;

		//save to backend
		$.post(sys_url + 'content/call/article/save_draft/?id=' + Content.page.ID, aSerializedData,
				function () {
					Content.notePanel.set(t('Draft saved successfully'), 'info', 1);
				}
		);
	},

	load: function (url, id, callback) {
		this.autosaveEnabled = false;
		if (typeof(url) != 'undefined' && url != '' && url != sys_url + 'content/') {
			//Content.notePanel.set('load');

			if (url.substring(0, 4) != 'http') {
				url = sys_url + url;
			}
			url = url.replace('&static=1', '');

			var square = new Sonic({

				width  : 100,
				height : 50,
				padding: 10,

				stepsPerFrame: 10,
				trailLength  : 1,
				pointDistance: .03,

				strokeColor: '#FF7B24',

				step: 'fader',

				multiplier: 2,

				setup: function () {
					this._.lineWidth = 5;
				},

				path: [

					['arc', 10, 10, 10, -270, -90],
					['bezier', 10, 0, 40, 20, 20, 0, 30, 20],
					['arc', 40, 10, 10, 90, -90],
					['bezier', 40, 0, 10, 20, 30, 0, 20, 20]
				]
			});

			square.play();

			$('#content_area').html(square.canvas);

			$('#content_area .sonic').css('margin', '200px auto');
			$('#content_area .sonic').css('display', 'block');
			$('#content_area').css('cursor', 'progress');

			var me = this;
			$.get(url, function (responce) {
				me.attachPageEvents(responce);


				if (typeof(callback) == 'function') {
					callback();
				}

				$('#content_area').css('cursor', 'default');
				$('#content_area .sonic').remove();
			});
		}
	}
});

Content.Views.NavBar = Backbone.View.extend({
	'el': '#navbar',

	events: {
		'submit form': 'loadSearch'
	},

	initialize: function () {
		$('.dropdown-toggle', this.el).dropdown();
	},

	loadSearch: function () {
		var query = $('#search_q').val();
		Content.page.load(sys_url + 'content/content/search/?q=' + query);
		Content.router.navigate('search/' + query, false);
		return false;
	}
});

Content.Views.MenuPanel = Backbone.View.extend({
	'el': '#panel_list',

	events: {
		'click li a': 'selectMenuItem'
	},

	selectMenuItem: function (e) {
		$("li", this.el).removeClass('active');
		$(e.target).parent().addClass('active');
	},

	selectByRel: function (rel) {
		$("li", this.el).removeClass('active');
		$("a[rel='" + rel + "']", this.el).parents('li').addClass('active');
	}
});

Content.Views.SideTagList = Backbone.View.extend({
	'el': '#side_tag_list',

//	events: {
//		'click li a': 'selectMenuItem'
//	},

	initialize: function () {
		this.updateTagList();
	},

	updateTagList: function () {
		var self = this;

		self.$el.html('');
		$.getJSON(sys_url + 'content/tag/listPopTags', function (results) {
			_.each(results, function (v) {
				self.$el.append('<span class="label tag">' + v.title + '</span>');
				if (!_.isNull(v.color)) {
					self.$el.find('span:last-child').css('background-color', v.color);
				}
			});
		});
	}
});