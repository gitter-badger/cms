<div class="dashboard_box dashboard_box_{$block.name} well">
    <div class="dyn_content recent_content {*if $smarty.foreach.results.index > 0}hidden{/if*}" id="dyn_tab_{$key}">

        <ul class="nav nav-list">
            <li class="nav-header">{$block.title}</li>
			{foreach from=$block.data item=item}
				<li><a href="#page/{$item->ID}" class="{$item->ext}">{$item->title}</a></li>
			{/foreach}
        </ul>


        <div class="clear"></div>
    </div>
</div>