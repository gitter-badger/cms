<table id="faq_values" class="data w100">
		<tr>
			<th>{t}Question{/t}</th>
			<th>{t}Answer{/t}</th>
			<th></th>
		</tr>
	{foreach from=$recElement->answers item=item}
		<tr>
			<td><textarea class="expanding_height" name="questions[]">{$item->question}</textarea></td>
			<td><textarea class="expanding_height" name="answers[]">{$item->answer}</textarea></td>
			<td class="actions">
				<span class="a move_up" >{t}Up{/t}</span>
				<span class="a move_down" >{t}Down{/t}</span>
				<span class="a red remove" >{t}Remove{/t}</span>
			</td>
		</tr>
	{/foreach}
</table>

<div class="control-group">
	<table class="form w100">
		<tr>
			<td>{t}Question{/t}</td>
			<td>{t}Answer{/t}</td>
			<td></td>
		</tr>
		<tr>
			<td><textarea id="question" name="questions[]"></textarea></td>
			<td><textarea id="answer" name="answers[]"></textarea></td>
			<td></td>
		</tr>
		</table>
</div>	

<div class="buttons clear">
	{if !$recElement->ID}
		<button class="black" onclick="return add_faq_value();"><span>{t}Add answer{/t}</span></button>
	{/if}
</div>