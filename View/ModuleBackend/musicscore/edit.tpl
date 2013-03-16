<div class="control-group clear">
	<label class="control-label">{t}Service{/t}</label>
	<div class="controls">
		<select name="service">
		{foreach from=$arrServices key=site item=site_descr}
		<option value="{$site}" {if $recElement->service eq $site}selected='selected'{/if}>{$site_descr}
			{/foreach}
		</select><br/>
		<textarea name="serviceCode" style="margin-top:3px;width:400px;min-width:200px;">{$recElement->service_id}</textarea>
	</div>
</div>

{if $recMenu->ID}
	<div class="clear"></div>
	{$recElement->html}
{/if}