<script language="Javascript" type="text/javascript">
	{$translations}
	{foreach from=$controller->arrJSVars key=key item=item}
		var {$key}={$item};
	{/foreach}
</script>

{foreach from=$controller->scripts item=item}
	<script type="text/javascript" src='{$item}' charset="utf-8" language="JavaScript"></script>
{/foreach}

{foreach from=$controller->arrStyles item=item}
<link rel="stylesheet" type="text/css" href="{$item.url}" media="{$item.media}" />
{/foreach}