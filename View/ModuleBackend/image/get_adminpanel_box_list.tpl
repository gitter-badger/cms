<div class="dashboard_box dashboard_box_{$block.name} well">
    <div class="dyn_content recent_content {*if $smarty.foreach.results.index > 0}hidden{/if*}" id="dyn_tab_{$key}">
        <ul class="thumbnails">
		{foreach from=$block.data item=item key=key}
            <li class="span1">
                <a href="#" class="thumbnail">
					<img src="{$item->link_square}" alt="{$item->title}" class="page" data-id="{$item->nodeID}"/>
                </a>
            </li>


		{/foreach}

        </ul>


    </div>
</div>