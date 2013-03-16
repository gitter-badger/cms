<form class="form-horizontal ajax" action='{$link_save}' method='POST'>

	<h3>Sync account</h3>
	<div class="control-group">

		<label class="control-label" for="service">{t}Service{/t}</label>
		<div class="controls">
			<input id="service" type="text" name="service" value="{$item->service}"/>
		</div>
	</div>

	{if !$service->bUseOauth}
	<div class="control-group">
		<label class="control-label" for="login">{t}Login{/t}</label>
		<div class="controls">
			<input id="login" type="text" name="login" value="{$item->login}"/>
		</div>
	</div>
	
	<div class="control-group">
		<label class="control-label" for="password">{t}Password{/t}</label>
		<div class="controls">
			<input id="password" type="password" name="password" value=""/>
		</div>
	</div>

	{else}

		<div class="control-group">
			<label class="control-label" for="key">{t}Consumer key{/t}</label>
			<div class="controls">
				<input id="key" type="text" name="key" value="{$item->key}"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="key2">{t}Consumer secret{/t}</label>
			<div class="controls">
				<input id="key2" type="text" name="key2" value="{$item->key2}"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="key3">{t}Access key{/t}</label>
			<div class="controls">
				<input id="key3" type="text" name="key3" value="{$item->key3}"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="key4">{t}Access secret{/t}</label>
			<div class="controls">
				<input id="key4" type="text" name="key4" value="{$item->key4}"/>
			</div>
		</div>
	{/if}
	
	<div class="form-actions">
		<button name="save_standart" class="btn btn-primary" id='savebutton'><span>{t}Save{/t}</span></button>
		{if $service->bUseOauth}
			<a class='btn' href="{$link_sync}" target="_blank"><span>{t}Connect{/t}</span></a>
		{/if}
	</div>
</form>

{if $facebook_commenting_api}
	<div id="fb-root"></div>
	<script src="http://connect.facebook.net/en_US/all.js"></script>
	<div id="fb_data" class="clear"></div>
{/if}