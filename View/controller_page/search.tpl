{if arrResults}
	<h1>{t}Search results{/t} &#171;{$query}&#187;</h1>

	<div class="tabbed" style='margin-top:30px;'>
		<ul class="nav nav-tabs tabs">
			{foreach from=$arrResults key=key item=item}
				<li data-tab="tabs-{$item->name}"><a>{$item->title}</a></li>
			{/foreach}
		</ul>


		{foreach from=$arrResults key=key item=module}
			<div id="tabs-{$module->name}">
				{foreach from=$module->list item=item}
					{include file="ModuleBackend/`$module->name`/search.item.tpl"}
				{/foreach}
				<div class="clear"></div>
			</div>
		{/foreach}
	</div>
{else}
	<p class='info'>{t}Nothing found{/t}</p>
{/if}
<div class="clear"></div>