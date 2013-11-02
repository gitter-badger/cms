{if $recMenu->method eq 'register'}
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Activation mail title{/t}</label></div>
		<div class="value" id="article_value">
		    <input name="activate_title" type="text" value="{$arrEmail->title}" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Activation mail (text){/t}</label></div>
		<div class="value" id="activation_value">
		    <textarea name="activate_text">{$arrEmail->text}</textarea>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Activation mail (html){/t}</label></div>
		<div class="value" id="article_value">
		    <textarea id='mce' name='activate_html' wrap='off' class='textarea textarea-js mceEditor'>{$arrEmail->html}</textarea>
		</div>
	</div>
{/if}

{if $recMenu->method eq 'forgot'}
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Reminder mail title{/t}</label></div>
		<div class="value" id="article_value">
		    <input name="forgot_title" type="text" value="{$arrEmail->title}" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Reminder mail (text){/t}</label></div>
		<div class="value" id="article_value">
		    <textarea name="forgot_text">{$arrEmail->text}</textarea>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label"><label class="control-label">{t}Reminder mail (html){/t}</label></div>
		<div class="value" id="article_value">
		    <textarea id='mce' name='forgot_html' wrap='off' class='textarea textarea-js mceEditor'>{$arrEmail->html}</textarea>
		</div>
	</div>
{/if}