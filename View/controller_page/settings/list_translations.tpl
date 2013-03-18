<div class="ajaxSettingsWrapper">
<h1>{$title}</h1>
<form class="form-inline well ajax" method='POST' action="{$link_filter}">
	<select name="application" id="application">
		<option value="">{t}Application{/t}</option>
		<option value="content" {if $application eq 'content'}selected=selected{/if}>{t}Adminpanel{/t}</option>
		<option value="front" {if $application eq 'front'}selected=selected{/if}>{t}Public{/t}</option>
		<option value="calendar" {if $application eq 'calendar'}selected=selected{/if}>{t}Calendar{/t}</option>
	</select>

	<input name="keyword" type="text" id="keyword" value="{$keyword}" placeholder="{t}Keyword{/t}" />
	<button class="btn" name="save_standart" id='savebutton'>{t}Filter{/t}</button>

	<a class="btn btn-primary" href="#settings/edit_translation/">{t}Add{/t}</a>
	<a class="btn" href="#settings/import_translations">{t}Import{/t} &uarr;</a>
	<a class="btn" href="#settings/export_translations">{t}Export{/t} &darr;</a>
</form>

{if $arrData}
	<div class="list_wrap">
		<table class="table table-striped table-bordered table-condensed">
			<tr>
				<th>{t}Code{/t}</th>
				{foreach from=$arrAvailableLanguages item=item}
					<th>{$item->english}</th>
				{/foreach}
				<th></th>
			</tr>
			{foreach from=$arrData item=item name=plans}
				<tr class="{cycle values='odd,even'}">
					<td>{$item->code}</td>
					{foreach from=$arrAvailableLanguages item=lang}
						{assign var=templang value=$lang->ID}
						<td>{$item->$templang}</td>
					{/foreach}
					<td class="actions">
						<a class="btn btn-mini ajax" href="{$item->link_edit}" rel="{$item->link_edit_ajax}">{t}Edit{/t}</a>
						<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
					</td>
				</tr>
			{/foreach}
		</table>
		{include file="helpers/paginator.tpl"}
	</div>
{else}
	<div class="alert alert-info">{t}No translations found{/t}</div>
{/if}

</div>