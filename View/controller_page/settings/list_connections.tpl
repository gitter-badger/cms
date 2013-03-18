{if $arrList}
	<div class="ajaxSettingsWrapper">
		<table class="table table-striped table-bordered table-condensed">
			<tr>
				<th>{t code='connection_pages'}{/t}</th>
				<th>{t}Tag{/t}</th>
				<th></th>
			</tr>
			{foreach from=$arrList item=item name=plans}
				<tr class="{cycle values='odd,even'}">
					<td>
						{foreach from=$item->arrLinks item=link key=key}
							<a href="/content/#{$link.ID}" onclick="MenuTree.editDeepChild({$link.ID});">{$link.title} ({$link.language})</a>

							{if $key<count($item->arrLinks)-1}&#8596;{/if}
						{/foreach}
					</td>
					<td>{$item->tag}</td>
					<td class="actions">
						<a class="btn btn-mini ajax" rel="{$item->link_edit_ajax}" href="{$item->link_edit}">{t}Edit{/t}</a>
					</td>
				</tr>
			{/foreach}
		</table>
		{include file="helpers/paginator.tpl"}
	</div>
{/if}

<a class="ajax btn btn-primary" href="{$link_add_connection}">{t}Add connection{/t}</a>
