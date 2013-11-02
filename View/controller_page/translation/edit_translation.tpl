<div class="ajaxSettingsWrapper">

	<form class="form-horizontal ajax" action='{$link_save}' method='POST'>

		<div style="width:500px;" class="pull-left">
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
					<input class="input-xxlarge" id="{$templang}_title" type="text" tabindex='1' name="{$templang}" value="{if $item->application}{$item->$templang}{elseif $templang eq 'eng'}{$smarty.get.eng}{/if}"/>
				</div>
			</div>
		{/foreach}

			</div>


		<div class="pull-right muted well" style="width:300px;">
			<strong><i class="icon icon-info-sign"></i> How to use</strong>

			<div class="popover-content">
				<p>To use translations in templates you can write <var>{literal}{t}Text in english{/t}</var> or
					<var>{t code="some_code"}{/t}</var> to connect translation entry to the actual location{/literal}</p>
			</div>
		</div>


		<div class="form-actions clear">
			<a class="btn btn-inverse" href="#translation/list_translations/">{t}Back{/t}</a>
			<button class="btn btn-primary" name="save_standart" id='savebutton'>{t}Save{/t}</button>
		</div>
	</form>

	<div class="clear"></div>
</div>