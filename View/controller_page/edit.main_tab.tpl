<fieldset>

	{if !$hideTitle}
	<div class="control-group float">
		{if in_array($recMenu->module, array('image'))}
			<textarea name="title" class="span7">{$recMenu->title}</textarea>
		{else}
			<input class="span6" type="text" name="title" value="{$recMenu->title}" placeholder="{t}Title{/t}"/>
		{/if}
	</div>
	{/if}

	{if $show_URL && $recMenu->ID}
		<div class="control-group float">
			<label class="control-label"><a target="_blank" class="external" href="{$recMenu->url}">{t}URL{/t}</a></label>
			<div class="controls">
				<input type="text" name="url" value="{$recMenu->smart_url}" class="input-small"/>
			</div>
		</div>
	{/if}


		<div class="control-group float {if $modal || $recMenu->ID && $recMenu->module=='article'}hidden{/if}">
			<label class="control-label" for="module">{t}Page type{/t}</label>

			<div class="controls">
				<select name="module" class="module_select span2">
					{foreach from=$arrModules item=item}
						<option value="{$item->ID}"	{if $recMenu->module eq $item->ID || $item->ID eq $smarty.get.module}selected="selected"{/if}>{t code="module_`$item->ID`"}{/t}
					{/foreach}
				</select>

				{if count($arrMethods)>1}
					<select name="method" id="method" class="span2">
						{foreach from=$arrMethods item=item}
						<option value="{$item}" {if $recMenu->method eq $item}selected="selected"{/if}>{$item}
							{/foreach}
					</select>
				{elseif count($arrMethods) eq 1}
					<input type="hidden" name="method" value="{$arrMethods[0]}"/>
				{/if}
			</div>
		</div>

	{if $recMenu->module=='article'}
		<div id="modal_subpage_selector" class="btn-group clear">
			{foreach from=array('person','map','file','slide', 'image', 'video', 'formula', 'game','movie') key=key item=item}
				<button type="button" class="btn">
					<img class="modal_trigger" src="/vendor/Gratheon/CMS/assets/img/icons/menu/{$item}.png" alt="{t code="module_`$item`"}{/t}" data-module="{$item}"/>
				</button>
			{/foreach}
		</div>
	{/if}


	{if $contentTemplate}
		{include file=$contentTemplate}
	{/if}
</fieldset>