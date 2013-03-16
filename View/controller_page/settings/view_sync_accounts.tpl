<table class="data w100">
	<tr>
		<th>{t}Service{/t}</th>
		<th></th>
	</tr>
	{foreach from=$arrAccounts item=item name=plans}
		<tr class="{cycle values='odd,even'}">
			<td>{$item->service}</td>
			<td class="actions">
				<a href="{$item->link_connect}">{t}Connect{/t}</a>
				<a class="red ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
			</td>
		</tr>
	{/foreach}
</table>