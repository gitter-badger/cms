<div class="control-group">
	<label class="control-label">Link to game</label>

	<div class="controls">
		<select name="elementID">
			{foreach from=$games item=item key=key}
				<option value="{$item.ID}" {if $recMenu->elementID eq $item.ID}selected="selected" {/if}>{$item.title}</option>
			{/foreach}
		</select>
	</div>
</div>