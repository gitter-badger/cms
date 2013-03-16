{if count($objPaginator->pages)>1}
<div class="pagination pagination-centered">
	<ul>
		{if $objPaginator->selected > $objPaginator->prev_page}
			<li class="prev"><a class="ajax" href="{$objPaginator->url}&amp;page={$objPaginator->prev_page}">« {t}Previous{/t}</a>
			</li>
		{/if}

		{foreach from=$objPaginator->pages item=page key=key}
			<li  {if $objPaginator->selected eq  $key}class="active"{/if}>
				<a class="ajax" href="{$objPaginator->url}&amp;page={$key}">{$page}</a>
			</li>
		{/foreach}

		{if $objPaginator->selected < $objPaginator->next_page}
			<li class="next"><a class="ajax" href="{$objPaginator->url}&amp;page={$objPaginator->next_page}">{t}Next{/t} »</a></li>
		{/if}
	</ul>
</div>

{*
<div class="paginator" id="paginator1" title="{t}Use Ctrl+right and Ctrl+left for keyboard navigation{/t}">
	<ul class="paging clear">
		{if $objPaginator->selected > $objPaginator->prev_page}
			<li class="prev"><a href="{$objPaginator->url}&amp;page={$objPaginator->prev_page}">« {t}Previous{/t}</a>
			</li>
		{/if}

		{foreach from=$objPaginator->pages item=page key=key}
			<li>
				<a {if $objPaginator->selected eq  $key}class="active"{/if} href="{$objPaginator->url}&amp;page={$key}">{$page}</a>
			</li>
		{/foreach}

		{if $objPaginator->selected < $objPaginator->next_page}
			<li class="next"><a href="{$objPaginator->url}&amp;page={$objPaginator->next_page}">{t}Next{/t} »</a></li>
		{/if}
	</ul>
</div>

<input type="hidden" value="{$objPaginator->url}&page={$objPaginator->next_page}" id="NextPage"/>
<input type="hidden" value="{$objPaginator->url}&page={$objPaginator->prev_page}" id="PrevPage"/>

<link rel="stylesheet" type="text/css" href="{$smarty.const.sys_url}cms/external_libraries/paginator3000/paginator3000.css"/>
<script type="text/javascript" src="{$smarty.const.sys_url}cms/external_libraries/paginator3000/paginator3000.js"></script>


<script type="text/javascript">
	pag1 = new Paginator('paginator1', {$objPaginator->page_count}, 10, {$objPaginator->selected}, "{$objPaginator->url}page=");
		{literal}
		$(document).mouseup(function () {
			$('.paginator a').addClass('ajax');
		});{/literal}

</script>
*}
{/if}