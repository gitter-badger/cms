<form class="standart ajax" action='{$link_save}' method='POST'> 	 
	<div class="control-group">
		<div class="label">
			<label for="groupID">{t}Group{/t}</label>
		</div>
		<div class="controls">
			<select name="groupID" id="groupID">
				{foreach from=$groups item=group}
					<option value="{$group->ID}" {if $group->ID eq $item->groupID}selected="selected"{/if}>{$group->title}
				{/foreach}
			</select>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="langID">{t}Language{/t}</label>
		</div>
		<div class="controls">
			<select name="langID" id="langID">
				{foreach from=$languages item=lang}
					<option value="{$lang->ID}" {if $lang->ID eq $item->lang}selected="selected"{/if}>{$lang->native}
				{/foreach}
			</select>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="login">{t}Username{/t}</label>
		</div>
		<div class="controls">
			<input id="login" type="text" name="login" value="{$item->login}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="password">{t}Password{/t}</label>
		</div>
		<div class="controls">
			<input type="password" name="void_password" value="" class="hidden"/>
			<input id="password" type="password" name="password" value="" />
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="firstname">{t}Firstname{/t}</label>
		</div>
		<div class="controls">
			<input id="firstname" type="text" name="firstname" value="{$item->firstname}"/>
		</div>
	</div>	
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Lastname{/t}</label>
		</div>
		<div class="controls">
			<input id="lastname" type="text" name="lastname" value="{$item->lastname}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">IP</label>
		</div>
		<div class="controls">
			{$item->IP}
		</div>
	</div>
	
	{if $item->date_visited}
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Last visit{/t}</label>
		</div>
		<div class="controls">
			{$item->date_visited}
		</div>
	</div>
	{/if}
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Date added{/t}</label>
		</div>
		<div class="controls">
			{$item->date_added}
		</div>
	</div>
	
	<h1 class="clear">{t}Contact info{/t}</h1>
	
	<div class="control-group">
		<div class="label">
			<label for="email">{t}Email{/t}</label>
		</div>
		<div class="controls">
			<input id="email" type="text" name="email" value="{$item->email}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Address{/t}</label>
		</div>
		<div class="controls">
			<input id="lastname" type="text" name="home_address" value="{$item->home_address}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Post index{/t}</label>
		</div>
		<div class="controls">
			<input id="lastname" type="text" name="post_index" value="{$item->post_index}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="lastname">{t}Mobile phone{/t}</label>
		</div>
		<div class="controls">
			<input id="lastname" type="text" name="phone_mobile" value="{$item->phone_mobile}"/>
		</div>
	</div>
	
	<div class="control-group">
		<div class="label">
			<label for="country_id">{t}Country{/t}</label>
		</div>
		<div class="controls">
			<select name="country_id" id="country_id">
				{foreach from=$countries item=country}
					<option value="{$country->id}" {if $country->id eq $item->country_id}selected="selected"{/if}>{$country->eng_title}
				{/foreach}
			</select>
		</div>
	</div>
	
	<div class="buttons clear">
		<button id="backbutton"><span>&laquo; {t}Back{/t}</span></button>
		<button name="save_standart" id='savebutton'><span>{t}Save{/t}</span></button>
	</div>
</form>

<script language="Javascript">
{literal}
jQuery('#backbutton').click(function(){
	Content.get('{/literal}{$link_list}{literal}');
	return false;
});
{/literal}
</script>