<form class="standart ajax" action='{$link_save}' method='POST'> 	 
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Description{/t}</label></div>
		<div class="controls">
		    <input name="description" type="text" value="{$arrItem->description}" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Tag{/t}</label></div>
		<div class="controls">
		    <input name="tag" type="text" value="{$arrItem->tag}" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Pages{/t}</label></div>
		<div class="value" id="activation_value">
			<select name="pageIDs[]" size=25 multiple='multiple' style="margin-top:20px;width:100%;">
				{foreach from=$arrTree item=item}
					{if $item->ID ne $recElement->parentID}
						<option value="{$item->ID}" {if in_array($item->ID,(array)$arrItem->pageIDs)}selected='selected'{/if}>{section name=connections loop=$item->level}&nbsp;&nbsp;&nbsp;{/section}{$item->title}
					{/if}
				{/foreach}
			</select>
		</div>
	</div>
	
	<div class="buttons clear">
		<a class="button ajax" href="{$link_list}"><span>&laquo; {t}Back{/t}</span></a>
		<button name="save_standart" id='savebutton'><span>{t}Save{/t}</span></button>
	</div>
</form>