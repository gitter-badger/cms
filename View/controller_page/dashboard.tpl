<div class="clear" id="dashboard_boxes">
	{if !$arrLastContent}
		<p class="info">{t}No content found{/t}</p>
	{else}
		{foreach from=$arrLastContent item=block key=key name='results'}
			{if $block.template}
				{include file=$block.template}
			{/if}
		{/foreach}
	{/if}
</div>

<div class="clear"></div>
{include file='helpers/js_css.tpl'}