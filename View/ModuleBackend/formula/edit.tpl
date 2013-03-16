<div id="formula_preview" class="clear">{if $recElement->format eq 'latex'}\[{/if}{$recElement->content}{if $recElement->format eq 'latex'}\]{/if}</div>

<div class="control-group">
	<label class="control-label">Format</label>

	<div class="controls">
		<select name="format">
			<option value="latex" {if $recElement->for1mat eq 'latex'}selected="selected"{/if}>LaTeX</option>
			<option value="mathml" {if $recElement->format eq 'mathml'}selected="selected"{/if}>MathML</option>
			<option value="ascii" {if $recElement->format eq 'ascii'}selected="selected"{/if}>ASCII MathML</option>
		</select>
	</div>
</div>

<div class="control-group">
	<label class="control-label">{t}Formula{/t}</label>

	<div class="controls">
		<textarea style="height:100px;" class="span7" name='content' id="formula">{$recElement->content}</textarea>
	</div>
</div>

<div class="control-group">
	<label class="control-label">{t}Description{/t}</label>

	<div class="controls">
		<textarea style="height:100px;" class="span7" name='description'>{$recElement->description}</textarea>
	</div>
</div>

{literal}
<script type="text/javascript" language="JavaScript">
	$(document).ready(function () {
		$('#formula').blur(function () {
			$('#formula_preview').html('\\['+$(this).val()+'\\]');
			var math = document.getElementById("formula_preview");
			MathJax.Hub.Queue(["Typeset", MathJax.Hub, math]);
		});
	});
</script>
{/literal}