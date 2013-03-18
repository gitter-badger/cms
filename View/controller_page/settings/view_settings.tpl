<div class="ajaxSettingsWrapper" style="padding-left: 30px;">
	<h1>{t}Site settings{/t}</h1>

	<form class="ajax" action='{$link_save}' method='POST'>
		{foreach from=$arrConfigVars key=key item=arrCategory}
			<h2 class="clear">{t code="module_`$key`"}{$key}{/t}</h2>
			{foreach from=$arrCategory item=arrVar}
				{if $arrVar->var_type eq 'select' && !$arrVar->select_values}

				{else}
					<div>
						<div style="display: table-cell;width:200px;">
							{if $arrVar->var_type neq 'checkbox'}
								<label for="groupID">{t code="`$key`_`$arrVar->var_name`"}{$arrVar->var_name}{/t}</label>
							{/if}
						</div>
						<div style="display: table-cell;">
							{if $arrVar->var_type eq 'text'}
								<input type="text" name="var_name[{$key}][{$arrVar->var_name}]" value="{$arrVar->var_value}"/>
							{elseif $arrVar->var_type eq 'textarea'}
								<textarea name="var_name[{$key}][{$arrVar->var_name}]">{$arrVar->var_value}</textarea>
							{elseif $arrVar->var_type eq 'password'}
								<input type="password" name="var_name[{$key}][{$arrVar->var_name}]" value=""/>
							{elseif $arrVar->var_type eq 'radio' && $arrVar->select_values}
								{foreach from=$arrVar->select_values key=sRadioValue item=sRadioValueTranslation}
									<label class="control-label"><input type="radio" name="var_name[{$key}][{$arrVar->var_name}]" value="{$sRadioValue}" {if $sRadioValue eq $arrVar->var_value}checked='checked'{/if}/>{$sRadioValueTranslation}
									</label>
									<br/>
								{/foreach}
							{elseif $arrVar->var_type eq 'select'}
								<select name="var_name[{$key}][{$arrVar->var_name}]">
									<option value=""></option>
									{foreach from=$arrVar->select_values key=sRadioValue item=aRadioValue}
										<option value="{$aRadioValue.ID}" {if $aRadioValue.ID eq $arrVar->var_value}selected=selected{/if}>{$aRadioValue.key}</option>
									{/foreach}
								</select>
							{elseif $arrVar->var_type eq 'checkbox'}
								<input type="hidden" name="var_name[{$key}][{$arrVar->var_name}]" value="0"/>
								<input type="checkbox" name="var_name[{$key}][{$arrVar->var_name}]" value="1" {if $arrVar->var_value}checked=checked{/if} />
								{t code="`$key`_`$arrVar->var_name`"}{$arrVar->var_name}{/t}
							{/if}
						</div>
					</div>
				{/if}
			{/foreach}

		{/foreach}

		<div class="clear">
			<button name="save_standart" id='savebutton' class="btn btn-primary">{t}Save{/t}</button>
		</div>

	</form>
</div>