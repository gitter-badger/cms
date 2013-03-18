<div class="ajaxSettingsWrapper">
<form class="form-inline well ajax" method='POST' action="{$link_filter}">
	<select name="group" id="group">
		<option value="">{t}Group{/t}
		{foreach from=$groups item=group}
			<option value="{$group->ID}" {if $group->ID eq $form_filter.group}selected="selected"{/if}>{$group->title}
		{/foreach}
	</select>

	<input name="keyword" type="text" id="keyword" value="{$form_filter.keyword}" placeholder="{t}Keyword{/t}"/>

	<input name="date_from" type="text" id="date_from" value="{$form_filter.date_from}" class="date" placeholder="{t}Date added{/t}">
	<input name="date_to" type="text" id="date_to" value="{$form_filter.date_to}" class="date" placeholder="{t}Date added{/t}">


	<button class="btn" name="save_standart" id='savebutton'>{t}Filter{/t}</button>
	<a class="btn btn-primary" href="{$link_add}">{t}Add{/t}</a>
</form>

{if $arrData}
	<div class="list_wrap">
		<table class="table table-striped table-bordered table-condensed">
			<tr>
				<th>{t}Username{/t}</th>
				<th>{t}Fullname{/t}</th>
				<th>{t}Email{/t}</th>
				<th></th>
			</tr>
			{foreach from=$arrData item=item name=plans}
				<tr class="{cycle values='odd,even'}">
					<td>{$item->login}</td>
					<td>{$item->firstname} {$item->lastname}</td>
					<td>{$item->email}</td>
					<td class="actions">
						<a class="btn btn-mini ajax" rel="{$item->link_edit_ajax}"
						   href="{$item->link_edit}">{t}Edit{/t}</a>
						<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
					</td>
				</tr>
			{/foreach}
		</table>
	{include file="helpers/paginator.tpl"}
</div>
{else}
	<div class="alert alert-info">{t}List is empty{/t}</div>
{/if}
</div>