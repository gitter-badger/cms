<noscript>
	<div class="alert alert-error">
		{t}You must enable javascript{/t}
	</div>
</noscript>

{if $errors}
	<div class="alert alert-error">
		{foreach from=$errors item=item}
			{$item}<br />
		{/foreach}
	</div>
{/if}

{if $ok}
<div class="alert alert-success">
	{foreach from=$ok item=item}
			{$item}<br />
	{/foreach}
</div>
{/if}

{if $info}
	<div class="alert alert-info">
		{foreach from=$info item=item}
			{$item}<br />
		{/foreach}
	</div>
{/if}