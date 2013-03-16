<div class="control-group clear">
	<label class="control-label">{t}Source{/t}</label>
	<div class="controls radio">
		<label class="{if !$recElement || $recElement->mode eq 'service'}selected{/if}" id="service">
			<input type="radio" name="mode" value="service" {if !$recElement || $recElement->mode eq 'service'}checked=checked{/if}> {t}Public service{/t}
		</label>
		<label class="{if $recElement->mode eq 'url'}selected{/if}" id="url">
			<input type="radio" name="mode" value="url" {if $recElement->mode eq 'url'}checked=checked{/if}> {t}Custom URL{/t}
		</label>
	</div>
</div>

<div class="control-group toggle_service">
	<label class="control-label" for="site">{t}Service{/t}</label>
	<div class="controls">
		<textarea name="site_id" id="site_id" class="span6" style="height:60px;margin-bottom:3px;">{$recElement->site_id}</textarea>
		<br />
		<select name="site" id="site">
			{foreach from=$arrSupportedSites key=site item=site_descr}
				<option value="{$site}" {if $recElement->site eq $site}selected='selected'{/if}>{$site_descr}
			{/foreach}
		</select>
	</div>
</div>

<div class="control-group toggle_url">
	<label class="control-label" for="external_flv">{t}File URI{/t}</label>
	<div class="controls">
		<input type="text" value="{$recElement->external_flv}" name="external_flv" id="external_flv" class="span6" />
	</div>
</div>

{if count($arrSupportedPlayers)>1}
	<div class="control-group toggle_url">
        <label class="control-label" for="custom_player">{t}Player{/t}</label>
        <div class="controls">
            <select name="custom_player" id="custom_player">
                {foreach from=$arrSupportedPlayers item=player}
                    <option value="{$player}" {if $recElement->custom_player eq $player}selected='selected'{/if}>{$player}
                {/foreach}
            </select>
        </div>
    </div>
{else}
    <input type="hidden" name='custom_player' value='{$arrSupportedPlayers[0]}' />
{/if}

{if $recMenu->ID}
	<div style="clear:both; width:{$recElement->width}px; margin:20px;">
	{if $recElement->mode eq 'url'}
		<video id="video_tag" width='{$recElement->width}' height='{$recElement->height}' controls="controls">
			{if strpos($recElement->external_flv,'mp4')!==false}
		        <source class="mp4" src="{$recElement->external_flv}" type="video/mp4">
			{/if}
			{if strpos($recElement->external_flv,'ogv')!==false}
		        <source class="mp4" src="{$recElement->external_flv}" type="video/ogg">
			{/if}
	    </video>
	{elseif $recElement->file_ext eq 'wmv'}
		<object type="application/x-ms-wmp" width='{$recElement->width}' height='{$recElement->height}'>
			<param name="url" value="{$recElement->flash_path}">
			<param name="uiMode" value="full">
			<param name="ShowDisplay" value="1">
			<param name="stretchToFit" value="1">
			<param name="transparentatStart" value="true">
			<param name="AutoStart" value="0">
		</object>
	{elseif $recElement->site eq 'html'}
		{$recElement->site_id}
	{else}
		<object type="application/x-shockwave-flash" data="{$recElement->flash_path}" class="video" width='{$recElement->width}' height='{$recElement->height}'>
			<param name='movie' value="{$recElement->flash_path}" />
			<param name='FlashVars' value="{$recElement->FlashVars}" />
			<param name='quality' value='high' />
			<param name='bgcolor' value='#000000' />
			<param name='wmode' 	value='transparent' />
		</object>
	{/if}
	</div>
{/if}