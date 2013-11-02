<div class="ajaxSettingsWrapper">
<h1>{t}Translations import{/t}</h1>
{if $import_info}
	<div class="alert alert-success">{t}Inserted{/t} <strong>{$import_info.inserts}</strong> {t}rows{/t}, {t}updated{/t} {$import_info.updates} {t}rows{/t}, {t}skipped{/t} {$import_info.skipped} {t}rows{/t}</div>
{else}
	<div class="alert alert-info">{t}CSV should be saved as unicode text{/t}</div>
{/if}

<form class="standart ajax" method='POST' enctype="multipart/form-data" action="{$link_import}"> 	 
	<div class="control-group">
		<div class="label"><label class="control-label">{t}CSV File{/t}</label></div>
		<div class="controls">
		    <input type="file" name="file" />
		</div>
	</div>
	<div class="control-group">
		<div class="label"></div>
		<div class="controls">
		    <label class="control-label">
			    <input type="checkbox" name="overwrite" value="1"/>
			    {t}overwrite{/t}
		    </label>
		</div>
	</div>
		
	<div class="buttons clear">
		<a class="button ajax" href="{$link_list}"><span>&laquo; {t}Back{/t}</span></a>
		<button name="save_standart" id='savebutton'><span>{t}Save{/t}</span></button>
	</div>
</form>
</div>