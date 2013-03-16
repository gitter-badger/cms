{literal}
<style type="text/css">
	#content_area .CodeMirror-scroll {
		height: auto;
		overflow-y: auto;
		overflow-x: auto;
	}
</style>
{/literal}
<div class="control-group">
	<label class="control-label">Language</label>

	<div class="controls">
		<select name="language" id="code_language">
		{foreach from=$supportedLanguages  item=codeLang}
			<option value="{$codeLang}"
					{if $recElement->language eq $codeLang}selected="selected"{/if}>{$codeLang}</option>
		{/foreach}
			<option value="" {if $recElement->language eq ''}selected="selected"{/if}>plain text</option>
		</select>
	</div>
</div>

<textarea style="width: 100%;" name='content' id="code">{$recElement->content}</textarea>