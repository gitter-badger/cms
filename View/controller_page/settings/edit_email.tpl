<div class="ajaxSettingsWrapper">
<form class="standart ajax" action='{$link_save}' method='POST'>
	<div class="control-group">
		<div class="label"><label class="control-label">Tag</label></div>
		<div class="controls">
		    {$arrEmail->tag}
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Email title{/t}</label></div>
		<div class="value" id="article_value">
		    <input name="title" type="text" value="{$arrEmail->title}" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Text content{/t}</label></div>
		<div class="value" id="activation_value">
		    <textarea name="text" style="height:200px;">{$arrEmail->text}</textarea>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label"><label class="control-label">{t}HTML content{/t}</label></div>
		<div class="value" id="article_value">
		    <textarea class="wysiwyg"  style="height:400px;" name='html' wrap='off' class='textarea textarea-js mceEditor'>{$arrEmail->html}</textarea>
		</div>
	</div>
	
	<div class="buttons clear">
		<a class="button ajax" href="{$link_list}"><span>&laquo; {t}Back{/t}</span></a>
		<button name="save_standart" id='savebutton'><span>{t}Save{/t}</span></button>
	</div>
</form>
</div>