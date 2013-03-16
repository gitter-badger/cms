<div class="control-group clear">
  <label class="control-label" for="geoX">{t}Coordinates{/t}</label>
  <div class="controls">
	  <input class="span2" type="text" name="geoX" id="geoX" value="{$recElement->geoX}" />
	  <input class="span2" type="text" name="geoY" value="{$recElement->geoY}" />
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="zoom">{t}Zoom{/t}</label>
  <div class="controls"><input class="span1" type='text' name="zoom" id="zoom" value="{$recElement->zoom}"/></div>
</div>

<div class="control-group">
  <label class="control-label" for="service">{t}Service{/t}</label>
  <div class="controls">
	  <select name="service" id="service">
		  <option value="google" {if $recElement->service eq 'google'}selected="selected"{/if}>Google</option>
		  <option value="bing" {if $recElement->service eq 'bing'}selected="selected"{/if}>Bing</option>
		  <option value="nokia" {if $recElement->service eq 'nokia'}selected="selected"{/if}>Nokia</option>
		  <option value="yandex" {if $recElement->service eq 'yandex'}selected="selected"{/if}>Yandex</option>
		  <option value="yahoo" {if $recElement->service eq 'yahoo'}selected="selected"{/if}>Yahoo</option>
	  </select>
  </div>
</div>
