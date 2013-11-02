{include file='helpers/messages.tpl'}

<form class="form-horizontal ajax" action='{$link_save}' method='POST'>

		<input type="text" name="title" placeholder="{t}Title{/t}" value="{$recElement->title}" style="border:none;padding: 5px 3%;width: 94%;margin:5px 0;font-size: 19px;"/>


		<textarea name='content' style="border:none;height: 600px;width:94%;font-family: Consolas, monospace;font-size:14px;padding: 5px 3%;">{$recElement->content}</textarea>



	{if $pages}
		<table class="table table-bordered">
			<tr>
				<th>Page</th>
				<th>Language</th>
			</tr>
			{foreach from=$pages item=item}
				<tr>
					<td>{$item->title}</td>
					<td>{$item->langID}</td>
					<td><a href="#page/{$item->ID}" class="btn btn-mini btn-primary">{t}Edit{/t}</a></td>
				</tr>
			{/foreach}
		</table>
	{/if}

	<div class="form-actions">
		<a class="btn btn-inverse ajax" rel="{$link_back}">&laquo; {t}Back{/t}</a>
		<button name="save_standart" id='savebutton' class="btn btn-primary">{t}Save{/t}</button>
	</div>
</form>
{include file='helpers/js_css.tpl'}