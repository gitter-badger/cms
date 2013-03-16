<div class="controls-group {if $bHideTags}hidden{/if}">
	<label class="control-label">{t}Tags{/t}</label>
	<div class="controls input-prepend tags">
		<span class="add-on"><i class="icon-tags"></i></span><input class="span2" name='tags' id='tags' value="{$recMenu->tags}">
	</div>
</div>

{if !$is_translateable && $recMenu->ID}
    <div class="control-group">
        <label class="control-label">{t code='translated_pages'}Translated pages{/t}</label>
        <div class="controls">
            <ul class='page_connections'>
            {foreach from=$recMenu->pageConnections item=item}
                <li>{$item->title} <img src="{$smarty.const.sys_url}app/content/img/cms/icons/delete.png" rel="{$item->pageID}" /></li>
            {/foreach}
            </ul>

			<div class="input-prepend tags">
				<span class="add-on"><i class="icon-file"></i></span><input type="text" name="new_connection" value="" id="new_connection"/>
			</div>

        </div>
    </div>
{/if}