<div class="ajaxSettingsWrapper">
<form class="standart ajax" action='{$link_save}' method='POST'>
	<div class="control-group">
		<div class="label">
			<label for="title">{t}Group{/t}</label>
		</div>
		<div class="controls">
			<input id="title" type="text" name="title" value="{$item->title}"/>
		</div>
	</div>
	
	<div class="buttons">
		<button id="backbutton"><span>&laquo; {t}Back{/t}</span></button>
		<button name="save_standart" id='savebutton'><span>{t}Save{/t}</span></button>
	</div>
</form>

</div>