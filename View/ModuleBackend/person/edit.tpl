<div class="control-group clear">
	<label class="control-label">{t}Person name{/t}</label>
	<div class="controls">
		<input type="text" name="person_name" value="{$recElement->firstname} {$recElement->lastname}" class="span4" />
        <input type="hidden" name="person_id" value="{$recElement->ID}"/>
	</div>
</div>

