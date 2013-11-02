<div class="clear destination_type">
	<label class="control-label">{t}Pages to{/t}...</label>
	<div class="controls">
		<label><input type="radio" name="listing_mode" value="hide" {if $recElement->listing_mode eq 'hide' || !$recElement}checked='checked'{/if}/> {t}Hide{/t}</label>

		<label><input type="radio" name="listing_mode" value="show" {if $recElement->listing_mode eq 'show'}checked='checked'{/if}/> {t}Show{/t}</label><br />

		<select name="pageIDs[]" size=15 multiple='multiple' style="margin-top:20px;width:100%;">
			{foreach from=$arrTree item=item}
				{if $item->ID ne $recElement->parentID}
					<option value="{$item->ID}" {if in_array($item->ID,(array)$recElement->pageIDs)}selected='selected'{/if}>{section name=connections loop=$item->level}&nbsp;&nbsp;&nbsp;{/section}{$item->title}
				{/if}
			{/foreach}
		</select>
	</div>
</div>