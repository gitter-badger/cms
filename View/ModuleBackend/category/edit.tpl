<div class="control-group clear">
	<label class="control-label" for="elements_per_page">{t}Elements on page{/t}</label>
	<div class="controls"><input class="span1" type="text" name="elements_per_page" id="elements_per_page" value="{if $recElement->elements_per_page}{$recElement->elements_per_page}{else}5{/if}" /></div>
</div>

<div class="control-group">
	<label class="control-label">{t}Order by{/t}</label>
	<div class="controls">
		<input type="radio" {if $recElement->orderby eq 'date_added' || !$recElement->orderby}checked='checked'{/if} name="orderby" value="date_added"> {t}Date added{/t}<br />
		<input type="radio" {if $recElement->orderby eq 'title'}	 checked='checked'{/if} name="orderby" value="title"> {t}Title{/t}<br />
		<input type="radio" {if $recElement->orderby eq 'position'}	 checked='checked'{/if} name="orderby" value="position"> {t}Position{/t}<br />
	</div>
</div>

<div class="control-group float">
	<label class="control-label">{t}Listing deepness{/t}</label>
	<div class="controls">
		<input type="radio" {if $recElement->deepness eq 'only_children' || !$recElement->deepness}checked='checked'{/if} name="deepness" value="only_children"> {t}Only children{/t}<br />
		<input type="radio" {if $recElement->deepness eq 'entire_tree'}	 checked='checked'{/if} name="deepness" value="entire_tree"> {t}Entire tree{/t}<br />
	</div>
</div>