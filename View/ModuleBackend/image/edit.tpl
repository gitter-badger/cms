<!--Image preview-->
{if $recElement->ID}
<span class="badge badge-success">{$recElement->image_format}</span>
<span class="clear badge badge-info">
{if $recElement->cloud_storage}{t}Cloud hosted{/t}
	{else}{t}Local server storage{/t}{/if}
</span>

<input type="hidden" name="crop_coords" value=""/>


<div class="clear">
	<ul class="thumbnails">
		<li class="span7">
			<div class="thumbnail" id="preview_image">
				<img src="{$recElement->link_image}" alt="">
				<div class="caption"><h5>{t}Original{/t}</h5></div>
			</div>
		</li>
		<li>
			<div class="thumbnail">
				<img src="{$recElement->link_square}?rand={1|rand:99999}" alt="">
				<div class="caption"><h5>{t}square{/t}</h5></div>
			</div>
		</li>
		<li>
			<div class="thumbnail">
				<img src="{$recElement->link_rectangle}?rand={1|rand:99999}" alt="">
				<div class="caption"><h5>{t}rectangle{/t}</h5></div>
			</div>
		</li>
	</ul>
</div>
{else}
<div class="control-group float">
	<label class="control-label">{t}File{/t}</label>

	<div class="controls"><input type="file" name="file"></div>
</div>
{/if}

<div style="padding-top: 20px;" class="clear">
	<h4>{t}Image form{/t}</h4>

	<div class="control-group  float">
		<label class="control-label">{t}Resized image contraint{/t}</label>

		<div class="controls"><input name="thumbnail_size" class="span1"
									 value="{if $smarty.get.ID}{$recElement->thumbnail_size}{else}200{/if}"
									 type="text"/></div>
	</div>

	<div class="control-group  float">
		<label class="control-label">{t}Thumbnail form{/t}</label>

		<div class="controls">
			<select name="thumbnail_type" class="span2">
				<option {if $recElement->thumbnail_type eq 'thumb'}selected=selected{/if} value="thumb">{t}landscape{/t}
				<option {if $recElement->thumbnail_type eq 'portrait'}selected=selected{/if} value="portrait">{t}
					portrait{/t}
				<option  {if $recElement->thumbnail_type eq 'square'}selected=selected{/if} value="square">{t}square{/t}
				<option {if $recElement->thumbnail_type eq 'original'}selected=selected{/if} value="original">{t}
					original{/t}
			</select>
		</div>
	</div>

	<div class="control-group  float">
		<label class="control-label">{t}Float position{/t}</label>

		<div class="controls">
			<select name="float_position" class="span2">
				<option {if $recElement->float_position eq 'right'}selected=selected{/if} value="right">{t}right{/t}
				<option  {if $recElement->float_position eq 'bottom'}selected=selected{/if} value="bottom">{t}bottom{/t}
				<option  {if $recElement->float_position eq 'inline'}selected=selected{/if} value="inline">{t}inline{/t}
			</select>
		</div>
	</div>

	<input type="hidden" name="crop_position" value=""/>
</div>