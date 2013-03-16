{include file='helpers/messages.tpl'}
<form class="form-horizontal well json" action='{$form_action}' id='content_form' name='editform' method='post'
	  enctype='multipart/form-data'>

	<div class="tabbed">
		{if $recMenu->module neq 'person'}
			<ul class="nav nav-tabs tabs">
				<li data-tab="tabs-1" class="active"><a>{t}General{/t}</a></li>
				<li data-tab="tabs-2"><a>{t}Settings{/t}</a></li>

			{if $arrExtraTabs}
				{foreach from=$arrExtraTabs key=key item=item}
					<li data-tab="tabs-{$key}"><a>{t}{$item.title}{/t}</a></li>
				{/foreach}
			{/if}
			</ul>
		{/if}

		<div id="tabs-1">
		{include file='controller_page/edit.main_tab.tpl'}
			<div class="clear"></div>
		</div>

		<div id="tabs-2">
			<div class="control-group float">
				<label class="control-label" for="date_added">{t}Date added{/t}</label>

				<div class="controls">
					<input class="datetime" id="date_added" type="text" name="date_added"
						   value="{$recMenu->date_added_formatted}"/>
				</div>
			</div>

		{if count($arrLanguages)>1}
			<div class="control-group float">
				<label class="control-label" for="langID">{t}Language{/t}</label>

				<div class="controls">
					<select name="langID" id="langID">
						{foreach from=$arrLanguages item=item}
						<option value="{$item->ID}"
								{if $item->ID eq $recMenu->langID}selected="selected"{/if}>{$item->native}
{/foreach}
					</select>
				</div>
			</div>

			{elseif count($arrLanguages) eq 1}
			<input type="hidden" name="langID" value="{$arrLanguages[0]->ID}"/>
		{/if}



		<h2 class="clear">{t}Rights and user groups{/t}</h2>
		<table class="table table-condensed table-bordered">
				<tr>
					<th></th>
				{foreach from=$userGroups item=group}
					<th>
						{$group->title}
					</th>
				{/foreach}
				</tr>
			{foreach from=$sysRights item=right}
				<tr {*if $group->ID eq 2}class='hidden'{/if*}>
					<td>{t}{$right->title}{/t}</td>
					{foreach from=$userGroups item=group}
						<td class="center">
							{assign var='rightID' value=$right->ID}
							<input type="checkbox" name="user_rights[{$group->ID}][]"
								   value="{$right->ID}"
								   {if !$recMenu->ID && in_array($rightID,(array)$defaultRights) ||
								   in_array($group->ID,(array)$recMenu->rights[$rightID])
								   }checked='checked'{/if} />
						</td>
					{/foreach}
				</tr>
			{/foreach}

		</table>


		{if !$bHideContainer}
			<h2>{t}Template{/t}</h2>

			<div class="value container_template">
				{foreach from=$arrContainerTemplates item=item}
					<div class="float {if !$recMenu->ID && $item->file eq 'front.page.tpl' || $item->file eq $recMenu->container_template}active{/if}">
						<img src="{$smarty.const.sys_url}vendor/Gratheon/CMS/assets/img/tpl_containers/{$item->file}.png"><br/>
						<input type="radio" name="container_template" value="{$item->file}"
							   {if !$recMenu->ID && $item->file eq 'front.page.tpl' || $item->file eq $recMenu->container_template}checked="checked"{/if}> {$item->title}
					</div>
				{/foreach}
			</div>
		{/if}

			<div class="clear"></div>
		</div>

	{if $arrExtraTabs}
		{foreach from=$arrExtraTabs key=key item=item}
			<div id="tabs-{$key}">
			{include file=$item.template}
				<div class="clear"></div>
			</div>
		{/foreach}
	{/if}
	</div>

	<div class="form-actions">
		<button name="save_standart" class="btn btn-primary" id='savebutton'>{t}Save{/t}</button>
		{if $recMenu->ID && $recMenu->parentID neq 1}
			<button class="btn btn-danger" onclick="if (confirm('{t}Are you sure?{/t}')) Content.DeleteElement({$recMenu->ID});" class="delete_link">{t}Delete{/t}</button>
		{/if}
	</div>

</form>

{include file='helpers/js_css.tpl'}
