<form class="form-horizontal ajax" action='{$link_save}' method='POST' style="background-color: white">
	<img src="{$image->source}" style="max-height:500px; float:left"/>

	<div style="float:left; width: 500px; margin-left: 20px;">
		<h1>{$image->filename}</h1>

		<div class="control-group">
			<label class="control-label" for="date_added_formatted">{t}Size{/t}</label>

			<div class="controls">
				{$image->size} bytes<br/>
				{$image->width} x {$image->height} px
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="date_added_formatted">{t}Date added{/t}</label>

			<div class="controls">
				{$image->date_added|@date_format:"d.m.Y H:i"}
			</div>
		</div>

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
			<a href="#image/list_images/&page={$smarty.get.page}" class="btn btn-inverse">{t}Back{/t}</a>
			<a href="#image/delete_image/{$image->ID}?page={$smarty.get.page}" class="btn btn-danger confirm">Delete</a>
		</div>
	</div>
	<div class="clear"></div>
</form>