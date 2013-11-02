{include file='helpers/header.tpl'}

<style>
	{foreach from=$arrModules key=key item=item}
	{if $item.icon}
	.icon_{$item.ID} {literal} {
	{/literal} background: transparent url('{$item.icon}') no-repeat right;
	{literal}
	}

	{/literal}
	{/if}
	{/foreach}
</style>
</head>

<body>

<div id="navbar" class="navbar navbar-inverse" style="margin-bottom: 0;">
	<div class="navbar-inner">
		<div class="container">

			<ul class="nav">
				<li><a href="{$sys_url}"><i class="icon-white icon-home"></i></a></li>
				<li><a href="{$sys_url}content/dashboard/" rel="dashboard" class="ajax">{t code="overview"}{/t}</a></li>


				<li><a href="#settings/list_users/">{t}Users{/t}</a></li>
				<li><a href="#tag/list_tags/">{t}Tags{/t}</a></li>

			</ul>

			<form class="navbar-search pull-left" id="search" method='post' action="#">
				<input type="text" class="search-query span2" name="q" id="search_q" placeholder="{t}Search{/t}">
			</form>

			<ul class="nav pull-right">
				<li><a href="#translation/list_translations/">{t}Translations{/t}</a></li>
				<li><a href="#settings/view_settings/">{t}Configuration{/t}</a></li>
				<li><a href="#install/updates/">{t}Update{/t}</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{t}Settings{/t}<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="nav-header">{$user.firstname} {$user.lastname}</li>
						<li><a href="#settings/list_groups/">{t}User groups{/t}</a></li>
						<li><a href="#settings/list_connections/">{t}Page connections{/t}</a></li>
						<li><a href="#settings/list_emails/">{t}Email templates{/t}</a></li>
						<li><a href="#settings/list_sync_accounts/">{t}Sync accounts{/t}</a></li>
						<li><a href="#settings/view_diagnostics/">{t}Diagnostics{/t}</a></li>

					</ul>
				</li>


				<li><a href="{$sys_url}content/profile/logout/" class="logout">{t}Logout{/t}</a></li>
			</ul>


		</div>
	</div>
</div>

<div class="modal" id="pageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

<div class="dialog hidden" id="dialog_wrapper">
	<div id="dialog"></div>
</div>


<div id='content'>
	<div id="notification"></div>

	<div id='content_area'></div>
</div>

<div id='panel' class="noselect">
	{*<div class="navbar navbar-inverse">*}
		{*<div class="navbar-inner">*}
			{*<span style="padding-top:6px;display: inline-block;">{t code='structure'}{/t}</span>*}

			{*<div class="btn-group" style="float:right;">*}
				{*<a class="btn btn-mini btn-primary" href="#add/article/1/rus/"><i class="icon-page icon-white"></i> {t}Add{/t}</a>*}
				{*<a class="btn btn-mini btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>*}
				{*<ul class="dropdown-menu">*}
					{*{foreach from=$arrModules key=key item=item}*}
						{*<li>*}
							{*<a href="#add/{$item.ID}/1/rus/">*}
								{*<img src="{$item.icon}" alt="{t code="module_`$item.ID`"}{/t}" data-module="{$item.ID}"/>*}
								{*{$item.ID}*}
							{*</a>*}
						{*</li>*}
					{*{/foreach}*}
				{*</ul>*}
			{*</div>*}
		{*</div>*}
	{*</div>*}

	<div style="direction:ltr;padding:8px 0;" class="well">

		<ul class="nav nav-list" id="panel_list">

			<li class="nav-header">{t code='structure'}{/t}</li>
			<li id="content_menu">
				<ul id="mainmenu">
					<li id="node1" class="root">
						<ul class="ajax"></ul>
					</li>
				</ul>
			</li>


			<li>
				<div class="btn-group" style="margin: 5px auto 0;width: 104px;display: block;
				}">
					<a class="btn btn-mini btn-primary" href="#add/article/1/rus/"><i class="icon-page icon-white"></i> {t}Add{/t}</a>
					<a class="btn btn-mini btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
					<ul class="dropdown-menu">
						{foreach from=$arrModules key=key item=item}
							<li>
								<a href="#add/{$item.ID}/1/rus/">
									<img src="{$item.icon}" alt="{t code="module_`$item.ID`"}{/t}" data-module="{$item.ID}"/>
									{$item.ID}
								</a>
							</li>
						{/foreach}
					</ul>
				</div>
			</li>

			<li class="nav-header">{t}Content by type{/t}</li>
			<li><a rel="news/list_news" href="{$sys_url}content/call/news/list_news/&amp;static=1" class="ajax">{t}News{/t}</a></li>

			<li><a rel="article/list_articles" href="{$sys_url}content/call/article/list_articles/&amp;static=1" class="ajax">{t}Articles{/t}</a></li>

			<li><a rel="image/list_images" href="{$sys_url}content/call/image/list_images/&amp;static=1" class="ajax">{t}Images{/t}</a></li>

			<li><a rel="file/list_files" href="{$sys_url}content/call/file/list_files/" class="ajax">{t}Files{/t}</a></li>


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

			<li class="nav-header">File upload drop area</li>
			<li id="upload_area" style="height: 80px;background-color: #dbdd73;border: 1px dashed gray;"></li>
			<li class="nav-header">{t code='tags'}{/t}</li>
			<li id="side_tag_list"></li>

		</ul>
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

	{*
	<li class="menu_upload">
		<div>{t}Upload{/t}</div>
	</li>
	*}
</ul>

<ul id='context_menu' class="hidden"></ul>

<div id="panel_resize"></div>

</body>
</html>