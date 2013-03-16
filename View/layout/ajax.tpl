<div class="well">
	{if $title}
		<h1>{$title}
		{if $title_badge}<span class="label label-info">{$title_badge}</span>{/if}
		</h1>
	{/if}

	{include file='helpers/messages.tpl'}

	{foreach from=$controller->arrStyles item=item}
		<link rel="stylesheet" type="text/css" href="{$item.url}" media="{$item.media}" />
	{/foreach}

	{include file=$content_template}

	<script language="Javascript" type="text/javascript">
		{foreach from=$controller->arrJSVars key=key item=item}
			var {$key}={$item};
		{/foreach}
	</script>

	{foreach from=$controller->scripts item=item}
		<script type="text/javascript" src='{$item}' charset="utf-8" language="JavaScript"></script>
	{/foreach}
</div>