<div class="control-group clear">
	<label class="control-label">{t}Destination type{/t}</label>
	<div class="controls">
		<label><input type="radio" name="destination_type" value="URL" {if !$recElement->ID || $recElement->destination_type eq 'URL'}checked=checked{/if}> {t}External URL{/t}</label>
		<label><input type="radio" name="destination_type" value="page" {if $recElement->destination_type eq 'page'}checked=checked{/if}> {t}Page{/t}</label>
		<label><input type="radio" name="destination_type" value="connection" {if $recElement->destination_type eq 'connection'}checked=checked{/if}> {t}Connection{/t}</label>
	</div>
</div>

<div class="control-group destination_type URL">
	<label class="control-label">{t}Target URL{/t}</label>
	<div class="controls">
		<input type="text" name="URL" value="{$recElement->URL}" class="span5">
	</div>
</div>


<div class="control-group destination_type page">
		<ul class='page_connections well' style="max-height: 500px;overflow-y: scroll;list-style:none;">
			{foreach from=$arrTree item=item}
				{if $item->ID ne $recElement->parentID}
					<li {if $item->ID eq $recElement->pageID}class='selected'{/if}>
						<label>
							<input type="radio"  name="pageID" value="{$item->ID}" {if $item->ID eq $recElement->pageID}checked='checked'{/if}>
							{section name=connections loop=$item->level}&nbsp;&nbsp;&nbsp;{/section}
							<img src="{$smarty.const.sys_url}/app/content/img/cms/icons/menu/{$item->module}.png"> {$item->title}
						</label>
					</li>
				{/if}
			{/foreach}
		</ul>
</div>

<div class="control-group destination_type connection">
	<label class="control-label">{t}Connection{/t}</label>
	<div class="controls">
		<select name="connectionID" size=7 style="width:250px;">
			{foreach from=$arrConnections item=item}
				<option value="{$item->ID}" {if $item->ID eq $recElement->connectionID}selected='selected'{/if}>{$item->description}
			{/foreach}
		</select>
	</div>
</div>

{literal}
<script language="Javascript">
$(document).ready(function(){
	$("input[name='destination_type']").change(function(){
		$('.destination_type').hide();
		$('.'+$("input[name='destination_type']:checked").val()).show();
	});
	
	$('.page_connections li').click(function(){
		$('.page_connections li').removeClass('selected');	
		$(this).addClass('selected');
		$('input',$(this)).attr('checked','checked');
	});

	$('.destination_type').hide();
	$('.'+$("input[name='destination_type']:checked").val()).show();
});
</script>
{/literal}