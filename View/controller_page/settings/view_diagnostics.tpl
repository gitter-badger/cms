<div style="background-color: white">
	<h1>{t}Diagnostics{/t}</h1>

	<div class="alert alert-info">{t code="info_diagnostics_module"}{/t}</div>

	<table class="table table-bordered">
		<tr>
			<td>Free RAM</td>
			<td>
				<div class="progress {if $free_ram_percent<15}progress-danger{elseif $free_ram_percent<30}progress-warning{else}progress-success{/if}">
					<div class="bar" style="width: {$free_ram_percent}%;"></div>
					<div class="description">{$free_ram_percent}% of {$total_ram}</div>
				</div>
			</td>
		</tr>
		<tr>
			<td>Free HDD</td>
			<td>
				<div class="progress {if $server_free_space_percent<15}progress-danger{elseif $server_free_space_percent<30}progress-warning{else}progress-success{/if}">
					<div class="bar" style="width: {$server_free_space_percent}%;"></div>
					<div class="description">{$server_free_space_percent}% of {$server_total_space}</div>
				</div>
			</td>
		</tr>
		<tr>
			<td>PHP memory limit</td>
			<td>{$memory_limit}</td>
		</tr>
		<tr>
			<td>Mysql database</td>
			<td>{$support_mysql}</td>
		</tr>
		<tr>
			<td>GD image management</td>
			<td>{$support_gd}</td>
		</tr>
		<tr>
			<td>Iconv charset conversion</td>
			<td>{$support_iconv}</td>
		</tr>
	</table>
</div>