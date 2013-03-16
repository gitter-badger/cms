<div class="control-group clear">
	<label class="control-label">E-mail</label>

	<div class="controls"><input type="text" name="email" value="{$recElement->email}"/></div>
</div>

<div class="control-group">
	<label class="control-label">{t}Author URL{/t}</label>

	<div class="controls">
		<input type="text" name="url" value="{$recElement->url}"/>
		<label><input type='checkbox' name="show_url" value="1" {if $recElement->show_url}checked=checked{/if}> {t}
			show{/t}</label>
	</div>
</div>

<div class="control-group">
	<label class="control-label">{t}Comment{/t}</label>

	<div class="controls">
		<textarea style="height:200px;" class="span7" id='comment_editor' name='wysiwyg'>{$recElement->content}</textarea>

		<label>
		<input type='checkbox' onclick="$('comment').value=transliterateText($('comment').value); return false;">
		{t}Translit{/t}
		</label>
	</div>
</div>