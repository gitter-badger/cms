{if $recElement->ID}
<div class="control-group clear">
	<label class="control-label">{t}Original filename{/t}</label>
	<div class="controls">
		<strong>{$recElement->filename}</strong> <a target="_blank" class="btn btn-mini" href="{$recElement->url}">Download</a>
	</div>
</div>
{elseif !$recElement->ID}
	<div class="control-group">
		<label class="control-label">{t}Source{/t}</label>
		<div class="controls">
			<label class="switch selected" id="url">
				<input type="radio" class="hidden" name="mode" value="browser" checked=checked> {t}Browser{/t}
			</label>
			
			<label class="switch" id="service">
				<input type="radio" class="hidden" name="mode" value="ftp"> {t}FTP folder{/t}
			</label>
		</div>
	</div>
{*
	<div class="control-group">
		<label for="file">{t}Scribd{/t}</label>
		<div class="controls">
			<input type='checkbox' name='scribd_upload' value="1" checked=checked/> upload<br />
			<input type='checkbox' name='scribd_show' value="1" checked=checked/> show
		</div>
	</div>
*}
	<div class="p100 toggler toggle_browser">
		<label for="file">{t}File{/t}</label>
		<div class="controls">
			<input type='file' name='file'>
		</div>
	</div>
	
	<div class="p100 toggler toggle_ftp">
		<label class="control-label">{t}Files{/t}</label>
		<div class="controls">
		{if $arrFiles}
			<select name="ftp_files[]" multiple='multiple' size="10">
				{foreach from=$arrFiles item=file}
					<option value="{$file}" selected='selected'>{$file}</option>
				{/foreach}
			</select>
		{else}
			<div class="alert alert-info">{t}Incoming ftp folder is empty{/t} (/res/incoming/)</div>
		{/if}
		</div>
	</div>
{/if}

{if $recElement->id3}
	<div class="control-group">
		<label class="control-label">{t}Filesize{/t}</label>
		<div class="controls">
			{$recElement->id3.filesize}
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">{t}Playtime{/t}</label>
		<div class="controls">
			{$recElement->id3.playtime_string}
		</div>
	</div>
	
	<div style="padding:10px; border:1px dotted #AAA; width:300px; margin:10px auto;clear:both;">
		<a href='{$recElement->url}'>{$recElement->filename}</a><br />
		<span style="color:#AAA;">
		{$recElement->id3.title}
		{if $recElement->id3.artist && $recElement->id3.title},{/if}
		{$recElement->id3.artist}</span><br />
		
		<object type="application/x-shockwave-flash" data="{$link_audio_player}" class="audioplayer" style="height:20px;width:200px;">
			<param name="FlashVars" value="playerID=1&amp;soundFile={$recElement->url}">
		</object>
	</div>
{/if}


{*if $recElement->scribd}
	<div class="p100 clear">
		<label for="file">{t}Scribd{/t}</label>
		<div class="controls">
			<input type='checkbox' name='scribd_upload' value="1" {if $recElement->scribd_upload}checked=checked{/if}/> upload<br />
			<input type='checkbox' name='scribd_show' value="1" {if $recElement->scribd_show}checked=checked{/if}/> show
		</div>
	</div>
	
	<div style="text-align:center;" class="clear">
		<object data="{$recElement->scribd_source}" height="550" id="doc_935150317115710" type="application/x-shockwave-flash" width="800">
			<param name="name" value="doc_{$recElement->scribd->docID}" />
			<param name="align" value="middle" />
			<param name="quality" value="high" />
			<param name="play" value="true" />
			<param name="loop" value="true" />
			<param name="scale" value="showall" />
			<param name="wmode" value="opaque" />
			<param name="devicefont" value="false" />
			<param name="bgcolor" value="#ffffff" />
			<param name="menu" value="true" />
			<param name="allowFullScreen" value="true" />
			
			<param name="allowScriptAccess" value="always" />
			<param name="src" value="{$recElement->scribd_source}" />
			<param name="allowfullscreen" value="true" />
		</object>
	</div>
{/if*}