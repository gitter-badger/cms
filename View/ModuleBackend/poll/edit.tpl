<div class="control-group clear">
	<h1>{t}Answers{/t}</h1>
	<div class="value ralign">
		<div id="gallup_value" class="hidden"><input class="p50" type="text" name="values[]" value="" /></div>
		<div id="gallup_values">
			{if $recElement->ID}
				<table class="form">
					{foreach from=$recElement->answers item=item}
						<tr>
							<td>{$item->voteCount}&nbsp;{t}votes{/t}</td>
							<td><input type="text" name="values[{$item->ID}]" value="{$item->answer}" /></td>
						</tr>
					{/foreach}
				</table>
			{else}
				<input class="p50" type="text" name="values[]" value="" />
			{/if}
		</div>
	</div>
</div>	

<div class="control-group">
	<label class="control-label">{t}Access{/t}</label>
	<div class="controls">
		<input type="radio" {if $recElement->restriction eq 'IP' || !$recElement->ID}checked='checked'{/if} name='restriction' value='IP'> Restrict by IP<br />
		<input type="radio" {if $recElement->restriction eq 'Users'}checked='checked'{/if} name='restriction' value='Users'> Registered users only
	</div>
</div>

{if $recElement->ID}
	<h1 class="clear">{t}Last votes{/t}</h1>
	{if $arrData}
		<table class="table table-bordered table-condensed">
			<tr>
				<th>{t}Time{/t}</th>
				<th>{t}Answer{/t}</th>
				<th>{t}Username{/t}</th>
				<th>{t}IP{/t}</th>
			</tr>
			{foreach from=$arrData item=item name=plans}
				<tr class="{if $smarty.foreach.plans.iteration is odd}odd{else}even{/if}">
					<td>{$item->date_added_formatted}</td>
					<td>{$item->answer}</td>
					<td>{$item->login}</td>
					<td>{$item->IP}</td>
				</tr>
			{/foreach}
		</table>
	{else}
		<div class="alert alert-info">{t}No votes were made{/t}</div>
	{/if}
{/if}

<div class="buttons clear">
	{if !$recElement->ID}
		<button class="black" onclick="add_gallup_value(); return false;"><span>{t}Add answer{/t}</span></button>
	{/if}
</div>
