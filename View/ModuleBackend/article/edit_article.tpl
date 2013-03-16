{include file='helpers/messages.tpl'}

<form class="form-horizontal ajax" action='{$link_save}' method='POST'>

	<div class="control-group">
		<textarea name='content[{$key}]' wrap='off' class="wysiwyg" style="height: 200px;">{$objNews->content[$key].content}</textarea>
	</div>

	<div class="control-group">
		<label class="control-label" for="date_added_formatted">{t}Date added{/t}</label>

		<div class="controls">
			<input type="text" class="date span2" id="date_added_formatted" name="date_added_formatted" value="{$objNews->date_added_formatted}"/>
			<input type="text" class="time span1" name="time_added_formatted" value="{$objNews->time_added_formatted}"/>
		</div>
	</div>

	<div class="form-actions">
		<a class="btn btn-inverse ajax" href="{$link_back}">&laquo; {t}Back{/t}</a>
		<button name="save_standart" id='savebutton' class="btn btn-primary">{t}Save{/t}</button>
	</div>
</form>
{include file='helpers/js_css.tpl'}