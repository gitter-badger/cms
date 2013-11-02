<h1>{t}News{/t}</h1>

<form class="ajax form-inline well" method='POST' action="{$link_filter}">
	<input name="date_from" type="text" id="date_from" value="{$form_filter.date_from}" class="date" placeholder="{t}From{/t}">
	<input name="date_to" type="text" id="date_to" value="{$form_filter.date_to}" class="date" placeholder="{t}To{/t}">
	<button name="save_standart" class="btn ajax">{t}Filter{/t}</button>
	<a href="{$link_add}" class="btn btn-primary ajax pull-right">{t}Add{/t}</a>

	{if $link_twitter_sync}
		<a href="{$link_twitter_sync}" class="ajax button"><span>{t}                    {/t}</span></a>
	{/if}
</form>

<form class="standart clear" method='POST'>
	{if $arrList}
		<div class="list_wrap">
			<table class="table table-condensed table-striped table-bordered">
				<tr>
					<th>{t}Time{/t}</th>
					<th>{t}Title{/t}</th>
					<th></th>
				</tr>
				{foreach from=$arrList item=item name=logs}
					<tr class="{cycle values='odd,even'}">
						<td>{$item->added_time_formatted}</td>
						<td>{$item->title|truncate:130}</td>
						<td class="actions">
							<a class="btn btn-mini ajax" rel="news/edit_news/?id={$item->ID}" href="{$item->link_edit}">{t}Edit{/t}</a>
							<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
						</td>
					</tr>
				{/foreach}
			</table>

			{include file="helpers/paginator.tpl" ajax_path='news/list_news'}

			<p class="total_count">{$filter_count} items matching filter</p>
		</div>
	{else}
		<div class="alert alert-info">{t}List is empty{/t}</div>
	{/if}
</form>