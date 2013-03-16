<div class="control-group">
	<div class="label"><label class="control-label">{t}Send email{/t}</label></div>
	<div class="controls"><input name="send_email"  value="1" type="checkbox" {if $recElement->send_email || !$recMenu->ID}checked=checked{/if} /></div>
</div>

<div class="control-group">
	<div class="label"><label class="control-label">{t}Log{/t}</label></div>
	<div class="controls"><input name="log_db"  value="1" type="checkbox" {if $recElement->log_db || !$recMenu->ID}checked=checked{/if} /></div>
</div>

<div class="control-group">
	<div class="label"><label class="control-label">{t}Receiver E-mail{/t}</label></div>
	<div class="controls"><input  type="text" name="email" value="{$recElement->email}" /></div>
</div>

<div class="control-group">
	<div class="label"><label class="control-label">{t}E-mail title{/t}</label></div>
	<div class="controls"><input  type="text" name="email_title" value="{$recElement->email_title}" /></div>
</div>

{if $arrList}
	<table class="data w100">
		<tr>
			<th>{t}Full name{/t}</th>
			<th>{t}Email{/t}</th>
			<th>{t}Message{/t}</th>
			<th></th>
		</tr>
		{foreach from=$arrList item=item name=plans}
			<tr class="{cycle values='odd,even'}">
				<td>{$item->fullname}</td>
				<td>{$item->email}</td> 
				<td>{$item->message|truncate}</td>
				<td class="actions">
					<a class="red ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
				</td>
			</tr>
		{/foreach}
	</table>
	
	{include file="element.paginator.tpl"}
{/if}