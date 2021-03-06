<div class="dashboard_box dashboard_box_{$block.name} well">
	<div class="dyn_content recent_content {*if $smarty.foreach.results.index > 0}hidden{/if*}" id="dyn_tab_{$key}">
		<ul class="nav nav-list">
			<li class="nav-header">{$block.title}</li>
			{foreach from=$block.data item=item}
				<li>
					<a href="{$smarty.const.sys_url}content/#page/{$item->ID}">{$item->title}</a>:
					{$item->content|truncate:150|escape:html}
					<a href="{$item->link_delete}" class="btn btn-mini btn-danger">delete</a>
				</li>
			{/foreach}
		</ul>

		<div class="clear"></div>
	</div>
</div>


{*<div class="dashboard_box dashboard_box_{$block.name}">*}
{*<h1>{$block.title} <span class="count">{$block.count}</span></h1>*}
{*<div class="dyn_content recent_content *}{*if $smarty.foreach.results.index > 0}hidden{/if*}{*" id="dyn_tab_{$key}">*}

{*<table class="data dashboard_comments">*}
{*{foreach from=$block.data item=item}*}
{*<tr class="{cycle values='odd,even'}">*}
{*<td style="max-height: 40px;overflow: hidden;display: block">*}
{*<a href="{$smarty.const.sys_url}content/#page/{$item->ID}">{$item->title}</a>:*}
{*<a href="{$item->link_delete}" class="delete"><img src="{$smarty.const.sys_url}app/content/img/cms/icons/delete.png" width="16" height="16" alt="x" style="float:right;"/></a>*}
{*{$item->content|truncate:150|escape:html}*}
{*</td>*}
{*</tr>*}
{*{/foreach}*}
{*</table>*}

{*<div class="clear"></div>*}
{*</div>*}
{*</div>*}