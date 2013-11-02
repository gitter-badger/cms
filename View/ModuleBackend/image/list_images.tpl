<div style="background-color: white;">
<h1>{t}Images{/t}</h1>

{if $images}
	<ul class="thumbnails" style="background-color: white;">
		{foreach from=$images item=image}
			<li class="span2">
				<div class="thumbnail" style="height: 80px; overflow: hidden;">
					<a href="#image/edit_image/{$image->ID}?page={$objPaginator->selected}">
						<img style="width: 100%; height: auto;" src="{$image->image_link}">


					</a>
				</div>
				<div class="title" style="">{$image->filename}</div>
			</li>
		{/foreach}
	</ul>
	{include file="helpers/paginator.tpl" ajax_path='image/list_images'}
{/if}
</div>