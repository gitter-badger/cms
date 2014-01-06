<div id="article_value" class="clear">
	<textarea id="article_content" class="wysiwyg" name="article_content" style="height: 400px; width: 100%;">
	{if $recMenu->ID}{$recElement->content}
		{elseif $recDraft->content}{$recDraft->content}
		{else}<p>&nbsp;</p>{/if}
	</textarea>
</div>