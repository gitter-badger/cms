{include file='helpers/messages.tpl'}

<form class="form-horizontal ajax" action='{$link_save}' method='POST'>
	<div class="tabbed">
		<ul class="nav nav-tabs">
			{foreach from=$arrLanguages key=key item=item}
				<li data-tab="tab-{$key}"><a>{$item.native_spell}</a></li>
			{/foreach}
		</ul>

		{foreach from=$arrLanguages key=key item=item}
			<div id="tab-{$key}">
				<input style="width:70%;font-size: 1.1rem;" type="text" tabindex='1' name="title[{$key}]"
					   value="{$objNews->title[$key].title}"/>

				<div class="control-group">
					<textarea name='news_editor[{$key}]' wrap='off' class="wysiwyg"
							  style="height: {if strlen($objNews->content[$key].content)>500}500{else}200{/if}px;">{$objNews->content[$key].content}</textarea>
				</div>

				<div class="clear"></div>
			</div>
		{/foreach}
	</div>


	<fieldset>
		<div class="control-group">
			<label class="control-label" for="date_added_formatted">{t}Date of event{/t}</label>

			<div class="controls">
				<input type="text" class="date span2" id="date_open_from_formatted" name="date_open_from_formatted" value="{$objNews->date_open_from_formatted}"/>
				<input type="text" class="time span1" name="time_open_from_formatted" value="{$objNews->time_open_from_formatted}"/>
				&mdash;
				<input type="text" class="date span2" id="date_open_from_formatted" name="date_open_to_formatted" value="{$objNews->date_open_to_formatted}"/>
				<input type="text" class="time span1" name="time_open_to_formatted" value="{$objNews->time_open_to_formatted}"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="date_added_formatted">{t}Date added{/t}</label>

			<div class="controls">
				<input type="text" class="date span2" id="date_added_formatted" name="date_added_formatted" value="{$objNews->date_added_formatted}"/>
				<input type="text" class="time span1" name="time_added_formatted" value="{$objNews->time_added_formatted}"/>
			</div>
		</div>

		{if $news_images}
			<div class="control-group">
				<label class="control-label" for="date_added_formatted">{t}Attached images{/t}</label>

				<div class="controls">
					{foreach from=$news_images item=item}
						<label style="width:160px;display: inline-block;font-size: 11px;border:1px solid #323232;background-color: #cacaca;margin:3px;color:black;">
							<img src="{$item->link_square}"/><br/>

							<div style="padding:3px 10px;overflow: hidden;height: 24px;">
								<input type="checkbox" name="image_ids[]" value="{$item->ID}" checked="checked"/>&nbsp; {$item->filename}
							</div>
						</label>
					{/foreach}
				</div>
			</div>
		{/if}

		<div class="control-group">
			<label class="control-label" for="date_added_formatted">{t}Add recent images{/t}</label>

			<div class="controls">
				{foreach from=$latest_images item=item key=key}
					{if $key>2}
						<label style="width:300px;font-size: 11px;border:1px solid #323232;background-color: #cacaca;margin:3px;color:black;">
							<img src="{$item->link_rectangle}" style="max-height:30px;width:40px;"/>

							<div style="padding:3px 10px;overflow: hidden;height: 24px;display: inline-block;">
								<input type="checkbox" name="image_ids[]" value="{$item->ID}"/>&nbsp; {$item->filename}
							</div>
						</label>
					{else}
						<label style="float:left; width:160px;display: inline-block;font-size: 11px;border:1px solid #323232;background-color: #cacaca;margin:3px;color:black;">
							<img src="{$item->link_square}" style="max-height:90px;"/>

							<div style="padding:3px 10px;overflow: hidden;height: 24px;display: inline-block;">
								<input type="checkbox" name="image_ids[]" value="{$item->ID}"/>&nbsp; {$item->filename}
							</div>
						</label>
					{/if}
					{if $key==2}<br/>{/if}
				{/foreach}
			</div>
		</div>
	</fieldset>

	<div class="form-actions">
		<a class="btn btn-inverse ajax" href="{$link_back}">&laquo; {t}Back{/t}</a>
		<button name="save_standart" id='savebutton' class="btn btn-primary">{t}Save{/t}</button>
	</div>
</form>
{include file='helpers/js_css.tpl'}