<div style="clear:both;display: table;">
	<h2>{t}Explicit content presense{/t}</h2>

	<div class="control-group  float">
		<label class="control-label">{t}Sex{/t}</label>
		<div class="controls"><input class="span1" name="xrate[sex]" value="{$recElement->xrate.sex.rating}" type="text"/>
		</div>
	</div>

	<div class="control-group  float">
		<label class="control-label">{t}Violence{/t}</label>
		<div class="controls"><input class="span1" name="xrate[violence]" value="{$recElement->xrate.violence.rating}"
								  type="text"/></div>
	</div>

	<div class="control-group  float">
		<label class="control-label">{t}Nudity{/t}</label>
		<div class="controls"><input class="span1" name="xrate[nude]" value="{$recElement->xrate.nude.rating}"
								  type="text"/></div>
	</div>

	<div class="control-group  float">
		<label class="control-label">{t}Disgust{/t}</label>
		<div class="controls"><input class="span1" name="xrate[disgust]" value="{$recElement->xrate.disgust.rating}"
								  type="text"/></div>
	</div>
	<div class="control-group  float">
		<label class="control-label">{t}Asocial behaviour{/t}</label>
		<div class="controls"><input class="span1" name="xrate[asocial]" value="{$recElement->xrate.asocial.rating}"
								  type="text"/></div>
	</div>
	<div class="control-group  float">
		<label class="control-label">{t}Faith{/t}</label>
		<div class="controls"><input class="span1" name="xrate[faith]" value="{$recElement->xrate.faith.rating}"
								  type="text"/></div>
	</div>
	<div class="control-group  float">
		<label class="control-label">{t}Spoiler{/t}</label>
		<div class="controls"><input class="span1" name="xrate[spoiler]" value="{$recElement->xrate.spoiler.rating}"
								  type="text"/></div>
	</div>
	<div class="control-group  float">
		<label class="control-label">{t}Empathy{/t}</label>
		<div class="controls"><input class="span1" name="xrate[empathy]" value="{$recElement->xrate.empathy.rating}"
								  type="text"/></div>
	</div>
	<div class="control-group  float">
		<label class="control-label">{t}Language{/t}</label>
		<div class="controls"><input class="span1" name="xrate[lang]" value="{$recElement->xrate.lang.rating}"
								  type="text"/></div>
	</div>
</div>


{if $recElement->EXIF}
<h2>{t}Embedded exif data{/t}</h2>
<div class="float" style="width:100%;overflow: auto;max-height: 250px;">
	<table class="table table-bordered">
		{foreach from=$recElement->EXIF item=row key=key}
			<tr><th>{$key}</th><td>{$row|@print_r}</td></tr>
		{/foreach}
	</table>
</div>
{/if}