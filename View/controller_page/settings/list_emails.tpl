{if $arrList}
	<table class="table table-striped table-bordered table-condensed">
		<tr>
			<th>{t}Title{/t}</th>
			<th>{t}Code{/t}</th>
			<th></th>
		</tr>
		{foreach from=$arrList item=item name=plans}
			<tr class="{cycle values='odd,even'}">
				<td>{$item->title}</td>
				<td>{$item->tag}</td>
				<td class="actions">
					<a class="ajax" rel="{$item->link_edit_ajax}" href="{$item->link_edit}">{t}Edit{/t}</a>
				</td>
			</tr>
		{/foreach}
	</table>
	{include file="helpers/paginator.tpl"}
{/if}