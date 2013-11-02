<div class="controls-group {if $bHideTags}hidden{/if}">
	<label class="control-label">{t}Tags{/t}</label>

	<div class="controls">
		<div class="input-append tags">
			<input type="text" class="span5" name='tags' id='tags' value="{$recMenu->tags}"/>
			<span class="add-on"><i class="icon-tags"></i></span>
		</div>
	</div>
</div>

{if !$is_translateable && $recMenu->ID}
	<div class="control-group">
		<label class="control-label">{t code='translated_pages'}Translated pages{/t}</label>

		<div class="controls">
			<div class="input-append tags">
				<input type="text" name="new_connection" value="" id="new_connection"/>
				<span class="add-on"><i class="icon-file"></i></span>
			</div>

			<table class='page_connections'>
				{foreach from=$recMenu->pageConnections item=item}
					<tr>
						<td>{$item->title}</td>
						<td>
							<button rel="{$item->pageID}" class="btn btn-mini btn-danger">Delete</button>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
{/if}