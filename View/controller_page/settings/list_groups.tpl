{if $arrData}
	<div class="list_wrap">
		<table class="table table-striped table-bordered table-condensed">
			<tr>
				<th>{t}Group{/t}</th>
				<th>{t}Users{/t}</th>
				<th></th>
			</tr>
			{foreach from=$arrData item=item name=plans}
				<tr class="{cycle values='odd,even'}">
					<td>{$item->title}</td>
					<td>{$item->user_count}</td>
					<td class="actions">
						<a class="ajax btn btn-mini" href="{$item->link_edit}">{t}Edit{/t}</a>
					</td>
				</tr>
			{/foreach}
		</table>

		{include file="helpers/paginator.tpl"}
	</div>
{/if}
<a class="btn btn-primary ajax" href="{$link_add}">{t}Add{/t}</a>