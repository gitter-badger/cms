{include file='helpers/header.tpl'}

<style>
	{foreach from=$arrModules key=key item=item}
		{if $item.icon}
			.icon_{$item.ID}    {literal}{{/literal}background:transparent url('{$item.icon}') no-repeat right;{literal}}{/literal}
		{/if}
	{/foreach}
</style>
</head>

<body>
<div id="navbar" class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">

			<ul class="nav">
				<li><a href="{$sys_url}"><i class="icon-white icon-home"></i> Home</a></li>
				<li><a href="{$sys_url}content/dashboard/" rel="dashboard" class="ajax">{t}Dashboard{/t}</a></li>

				<li><a href="#settings/list_translations/">{t}Translations{/t}</a></li>
				<li><a href="#settings/view_settings/">{t}Configuration{/t}</a></li>
				<li><a href="#settings/list_users/">{t}Users{/t}</a></li>

			</ul>

			<form class="navbar-search pull-left" id="search" method='post' action="#">
				<input type="text" class="search-query span2" name="q" id="search_q" placeholder="{t}Search{/t}">
			</form>

			<ul class="nav pull-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{t}Settings{/t}<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="nav-header">{$user.firstname} {$user.lastname}</li>
						<li><a href="#settings/list_groups/">{t}User groups{/t}</a></li>
						<li><a href="#settings/list_connections/">{t}Page connections{/t}</a></li>
						<li><a href="#settings/list_emails/">{t}Email templates{/t}</a></li>
						<li><a href="#settings/list_sync_accounts/">{t}Sync accounts{/t}</a></li>
						<li><a href="#settings/view_diagnostics/">{t}Diagnostics{/t}</a></li>
						<li><a href="{$sys_url}content/profile/logout/" class="logout">{t}Logout{/t}</a></li>
					</ul>
				</li>
			</ul>

		</div>
	</div>
</div>

<div class="modal" id="pageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

<div class="dialog hidden" id="dialog_wrapper">
    <div id="dialog"></div>
</div>


<div id='content'>
    <div id="notification" class="hidden"></div>
    <div id='content_area'>
	</div>
</div>

<div id='panel' class="noselect">
    <div style="direction:ltr;padding:8px 0;" class="well">


		<ul class="nav nav-list" id="panel_list">
			<li class="nav-header">{t}Pages{/t}</li>
			<li id="content_menu">
				<ul id="mainmenu">
					<li id="node1" class="root">
						<ul class="ajax">
						</ul>
					</li>
				</ul>
			</li>



		{foreach from=$arrModuleMenu item=module}
			<li class="divider"></li>
			<li class="nav-header">{$module.title}</li>
			{if $module.children}
				{foreach from=$module.children item=submodule}
					<li class="{if $submodule.active}active{/if}">
						<a rel="{$submodule.rel}" href='{$submodule.link}&static=1' class="ajax">{$submodule.title}</a>
					</li>
				{/foreach}
			{/if}
		{/foreach}
		</ul>
	</div>
</div>

<div id="panel_resize">
	<div class="btn-group btn-group-vertical">
		{foreach from=$arrModules key=key item=item}
			<button type="button" class="btn"><img {if in_array($item.ID, array('person','map','file','slide', 'image', 'video', 'formula', 'game','movie','code'))}class="modal_trigger"{/if} src="{$item.icon}" alt="{t code="module_`$item.ID`"}{/t}" data-module="{$item.ID}"/></button>
		{/foreach}
	</div>
</div>


<!--context menu-->
<ul id='context_menu_prototype' style="display:none;">
	<li onclick='Content.EditElement([ID]);'>
		<div>{t}Edit{/t}</div>
	</li>
	<li onclick="Content.AddElement([ID]);">
		<div>{t}Add{/t}</div>
	</li>
	<li onclick="if (confirm('{t}Are you sure?{/t}')) Content.DeleteElement([ID]);" class="delete_link">
		<div>{t}Delete{/t}</div>
	</li>
	<li onclick="Content.EmbedElement([ID]);" class="embed_link">
		<div>{t}Embed{/t}</div>
	</li>


	<li onclick="MenuTree.Load([ID]);" class="refresh_link">
		<div>{t}Refresh{/t}</div>
	</li>
	<li onclick="Content.CopyElement([ID]);" class="copy_link">
		<div>{t}Copy{/t}</div>
	</li>
	<li onclick="Content.PasteElement([ID]);" class="paste_link">
		<div>{t}Paste{/t}</div>
	</li>

	<li class="menu_upload">
		<div>{t}Upload{/t}</div>
	</li>
</ul>
<ul id='context_menu' class="hidden"></ul>

</body>
</html>