<div class="p100 destination_type page">
	<div class="label"><label class="control-label">{t}Pages to{/t}...</label></div>
	<div class="controls">
		<label class="control-label"><input type="radio" name="listing_mode" value="hide" {if $recElement->listing_mode eq 'hide' || !$recElement}checked='checked'{/if}/> {t}Hide{/t}</label><br />
		<label class="control-label"><input type="radio" name="listing_mode" value="show" {if $recElement->listing_mode eq 'show'}checked='checked'{/if}/> {t}Show{/t}</label>
		<br />
		<select name="pageIDs[]" size=15 multiple='multiple' style="margin-top:20px;">
			{foreach from=$arrTree item=item}
				{if $item->ID ne $recElement->parentID}
					<option value="{$item->ID}" {if in_array($item->ID,(array)$recElement->pageIDs)}selected='selected'{/if}>{section name=connections loop=$item->level}&nbsp;&nbsp;&nbsp;{/section}{$item->title}
				{/if}
			{/foreach}
		</select>
	</div>
</div>