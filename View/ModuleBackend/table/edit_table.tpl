{include file='helpers/messages.tpl'}

<form class="form-horizontal ajax" action='{$link_save}' method='POST'>

	<div class="control-group">
		<label class="control-label">{t}Title{/t}</label>

		<div class="controls">
			<input type="text" name="title" value="{$movie->title}"/>
		</div>
	</div>

	<table id="cells"></table>

	<div class="form-actions">
		<a class="btn btn-inverse ajax" href="{$link_back}">&laquo; {t}Back{/t}</a>
		<button name="save_standart" id='savebutton' class="btn btn-primary">{t}Save{/t}</button>
	</div>
</form>
{include file='helpers/js_css.tpl'}