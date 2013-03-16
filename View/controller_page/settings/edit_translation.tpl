<form class="form-horizontal ajax" action='{$link_save}' method='POST'>
	<div class="control-group">
		<label class="control-label" for="application">{t}Application{/t}</label>
		<div class="controls">
			<select name="application" id="application">
				<option value="front" {if $item->application eq 'front'}selected=selected{/if}>{t}Public{/t}
				<option value="content" {if $item->application eq 'content'}selected=selected{/if}>{t}Adminpanel{/t}
			</select>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label" for="code">{t}Code{/t}</label>
		<div class="controls">
			<input id="code" type="text" name="code" value="{if $item->code}{$item->code}{else}{$smarty.get.code}{/if}"/>
		</div>
	</div>
	
	{foreach from=$arrAvailableLanguages item=lang}
		{assign var=templang value=$lang->ID}
		<div class="control-group">
			<label class="control-label" for="{$templang}_title">{$lang->english}</label>
			<div class="controls">
				<input id="{$templang}_title" type="text" tabindex='1' name="{$templang}" value="{if $item->application}{$item->$templang}{elseif $templang eq 'eng'}{$smarty.get.eng}{/if}"/>
			</div>
		</div>
	{/foreach}
	
	
	<div class="form-actions clear">
		<button class="btn btn-primary" name="save_standart" id='savebutton'>{t}Save{/t}</button>
	</div>
</form>