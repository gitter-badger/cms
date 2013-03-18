<div class="ajaxSettingsWrapper">
	<div class="alert alert-info">{t code='sync_account_info'}{/t}</div>
	{if $arrAccounts}
		<table class="table table-striped table-bordered table-condensed">
			<tr>
				<th>{t}Service{/t}</th>
				<th></th>
			</tr>
			{foreach from=$arrAccounts item=item name=plans}
				<tr class="{cycle values='odd,even'}">
					<td>{$item->service}</td>
					<td class="actions">
						<a href="{$item->link_edit}" class="btn btn-mini ajax">{t}Edit{/t}</a>
						<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
					</td>
				</tr>
			{/foreach}
		</table>
	{else}
		<div class="alert alert-info">{t}List is empty{/t}</div>
	{/if}
</div>