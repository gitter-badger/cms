<div class="dashboard_box dashboard_box_{$block.name} well">
    <div class="dyn_content recent_content {*if $smarty.foreach.results.index > 0}hidden{/if*}" id="dyn_tab_{$key}">
        <ul class="thumbnails">
		{foreach from=$block.data item=item}
            <li class="span2">
                <div class="thumbnail">

					{if $item->site eq 'youtube.com'}
						<img width="100%" src="http://i4.ytimg.com/vi/{$item->site_id}/default.jpg" alt=''/>
					{*http://img.youtube.com/vi/{$item->site_id}/0.jpg*}
					{/if}

                    <div class="caption">
						<a href="{$smarty.const.sys_url}content/#page/{$item->parentID}">{$item->title}</a>
                    </div>
                </div>
            </li>
		{/foreach}

        </ul>

        <div class="clear"></div>
    </div>
</div>