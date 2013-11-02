{if count($objPaginator->pages)>1}
	<div class="pagination pagination-centered">
		<ul>
			{if $objPaginator->selected > $objPaginator->prev_page}
				<li class="prev">
					<a class="ajax" rel="{$ajax_path}?page={$key}" href="{$objPaginator->url}&amp;page={$objPaginator->prev_page}">« {t}Previous{/t}</a>
				</li>
			{/if}

			{foreach from=$objPaginator->pages item=page key=key}
				<li {if $objPaginator->selected eq  $key}class="active"{/if}>
					<a class="ajax" rel="{$ajax_path}?page={$key}" href="{$objPaginator->url}&amp;page={$key}">{$page}</a>
				</li>
			{/foreach}

			{if $objPaginator->selected < $objPaginator->next_page}
				<li class="next"><a class="ajax" rel="{$ajax_path}?page={$key}" href="{$objPaginator->url}&amp;page={$objPaginator->next_page}">{t}Next{/t} »</a>
				</li>
			{/if}
		</ul>
	</div>
{/if}