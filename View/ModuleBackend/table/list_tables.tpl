<h1>{t}Tables{/t}</h1>

<a href="{$link_add}" class="btn btn-primary ajax pull-right">{t}Add{/t}</a>

<form class="standart clear" method='POST'>
	{if $arrList}
		<div class="list_wrap">
			<table class="table table-condensed table-striped table-bordered">
				<tr>
					<th>{t}Title{/t}</th>
					<th></th>
				</tr>
				{foreach from=$arrList item=item name=logs}
					<tr class="{cycle values='odd,even'}">
						<td>{$item->title}</td>
						<td class="actions">
							<a class="btn btn-mini ajax" rel="table/edit_table/?id={$item->ID}" href="{$item->link_edit}">{t}Edit{/t}</a>
							<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
						</td>
					</tr>
				{/foreach}
			</table>

			{include file="helpers/paginator.tpl" ajax_path='table/list_table'}

			<p class="total_count">{$filter_count} items matching filter</p>
		</div>
	{else}
		<div class="alert alert-info">{t}List is empty{/t}</div>
	{/if}
</form>