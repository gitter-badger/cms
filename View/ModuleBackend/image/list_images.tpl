{foreach from=$images item=image}
	<img src="{$image->link_square}" alt='{$image->ID}' onclick="MenuTree.editDeepChild('{$image->parentID}');" style="cursor:pointer;"/>
{/foreach}