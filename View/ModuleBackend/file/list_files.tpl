<h1>{t}Files{/t}</h1>

<form class="standart clear" method='POST'>
{if $files}
	<div class="list_wrap">
		<table class="table table-condensed table-striped table-bordered">
			<tr>
				<th>{t}Time{/t}</th>
				<th>{t}Type{/t}</th>
				<th>{t}Filename{/t}</th>
				<th>{t}Size{/t}</th>
				<th>Download</th>
				<th></th>
			</tr>
			{foreach from=$files item=item name=logs}
				<tr class="{cycle values='odd,even'}">
					<td>{$item->date_added}</td>
					<td><img src="{$item->link_icon}" />{$item->ext}</td>
					<td>{$item->filename|truncate:130}</td>
					<td>{$item->size}</td>

					<td class="actions">
						<a class="btn btn-mini" target="_blank" href="{$item->link_download}">{t}Download{/t}</a>
						{if $item->link_download_local && $item->link_download_local!=$item->link_download}
							<a class="btn btn-mini" target="_blank" href="{$item->link_download_local}">{t}Local{/t}</a>
						{/if}
</td>
					<td>
						<a class="btn btn-mini ajax" rel="file/edit_file/?id={$item->ID}" href="{$item->link_edit}">{t}Edit{/t}</a>
						{if $item->link_page}
							<a class="btn btn-mini" href="{$item->link_page}">{t}Page{/t}</a>
						{else}
							<a class="btn btn-mini btn-danger ajax" href="{$item->link_delete}">{t}Delete{/t}</a>
						{/if}

					</td>
				</tr>
			{/foreach}
		</table>

	{include file="helpers/paginator.tpl" ajax_path='file/list_files'}
	</div>
	{else}
	<div class="alert alert-info">{t}List is empty{/t}</div>
{/if}
</form>