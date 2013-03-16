<div class="control-group">
	<div class="label"><label class="control-label">{t}Target{/t}</label></div>
	<div class="value" style="height:550px;overflow-y:scroll;background-color:white;">
	
		<ul>
			{foreach from=$arrTree item=item}
				{if $item->ID ne $recElement->parentID}
					<li {if in_array($item->ID,(array)$recElement->pageConnections)}class='selected'{/if}>
						<label class="control-label">
							<input type="radio"  name="destinationID" value="{$item->ID}" {if $item->ID eq $recMenu->elementID}checked='checked'{/if}>
							{section name=connections loop=$item->level}&nbsp;&nbsp;&nbsp;{/section}{$item->title}
						</label>
					</li>
				{/if}
			{/foreach}
		</ul>
	</div>
</div>
