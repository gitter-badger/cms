<!--Event registration form -->
<div id='form_bg' onclick="$('form_bg').hide();$('form').hide();">
	<div id='form' onclick="Cal.FormClick(event);">  
		<div id='form_inner' style="position:relative;">
			<form id='registration_form'>
				<div class='center'><textarea type='text' name='subject' id='subject'></textarea></div>
				<div style='position:relative;'>
					<label>{t}Location{/t}</label>
					<input name='location' onkeypress="JavaScript:return fakeCombo(this,event);" id="txtCombo" class="txtBox" /><br/>
					<select name='locationID' id="selCombo" class="dropDown" onChange="JavaScript:mySelect(this);" />
						{foreach from=$arrLocations item=item}
						<option value="{$item->ID}"/>{$item->title}
						{/foreach}
					</select>
				</div>
				
				<div style='margin-top:15px;'>
					<label>{t}Group{/t}</label>
					<select name='group_id' id='group_id'>
						<option value="1">{t}Private{/t}
						<option value="2">{t}Work{/t}
					</select>
				</div>
				
				<div>
					<label>{t}All day{/t}</label>
					<input type="checkbox" name="all_day" onchange="$('recurrency').toggle();$('start_time_box').toggle();$('end_time_box').toggle()" id='all_day'>
				</div>
				
				<div id='start_time_box'>
					<label>{t}Start time{/t}</label>
					<input name="start_date" type="text" id="start_date" style="width: 85px;  " value="{$smarty.now|date_format:"%d.%m.%Y"}" size="10" maxlength="10"> 
					<input type="text" size=5 name="start_time" value='08:00' id="start_time">
				</div>
				
				<div id='end_time_box'>
					<label>{t}End time{/t}</label>
					<input name="end_date"  type="text" class="kasutaja_input" id="end_date" style=" width: 85px; " value="{$smarty.now+604800|date_format:"%d.%m.%Y"}" size="10" maxlength="10"> 
					<input type="text" size=5 name="end_time" class="kasutaja_select" value='17:00' id="end_time">
				</div>
				
				{*literal}
				<script type="text/javascript">
				function start_cal(cal) {
					$('start_time').value = cal.date.print("%H:%M");
				}
				function end_cal(cal) {
					$('end_time').value = cal.date.print("%H:%M");
				}
				Calendar.setup({
					inputField     :    "start_date",   // id of the input field
					ifFormat       :    "%d.%m.%Y",       // format of the input field
					showsTime      :    true,
					timeFormat     :    "24",
					onUpdate       : start_cal
				});
				Calendar.setup({
					inputField     :    "end_date",
					ifFormat       :    "%d.%m.%Y",
					showsTime      :    true,
					timeFormat     :    "24",
					onUpdate       : end_cal
				});
				</script>
				{/literal*}
				
				<div  id='recurrency'>
					<label>{t}Recurrency{/t}</label>
					<input type="checkbox" name="recurrency" id='recurrency_checkbox' onchange="Cal.FormReccurentCheck()">
				</div>
				
				<div id='recurrency2'>
					<label>{t}Period{/t}</label>
					<select id='rec_frequency' name='period'>
						<option value="1">1
						<option value="2">2
						<option value="3">3
						<option value="4">4
						<option value="5">5
						<option value="6">6
						<option value="7">7
					</select>
					<select id='rec_type' name='period2'>
						<option value="days">{t}Day{/t}
						<option value="weeks">{t}Week{/t}
						<option value="years">{t}Year{/t}
					</select>
				</div>
				
				<button onclick="Cal.Submit();$('form_bg').hide();"><span>{t}Send{/t}</span></button>
			</form>
		</div>
		<img onclick="$('form').hide();$('form_bg').hide();" unselectable='on' src='img/calendar/close.gif' style="cursor:pointer;position:absolute;top:10px;right:10px;">
	</div>
<!--/Event registration form -->
</div>

<div id='cal_wrapper'>
	<div id='navigation'>
		<div class="w40">
			<img id='btn_prev' class="a" src='img/calendar/icon/go-previous.png' onclick="Cal.changePeriod(-1);">
			<img id='btn_next' class="a" src='img/calendar/icon/go-next.png' onclick="Cal.changePeriod(1);">
		</div>
		<div id='day_heading' style='text-align:center;'></div>
		<div id='week_heading' style='text-align:center;'></div>
		<div id='month_heading'></div>
	</div>

	
	<div id='tab_day' class='hidden'>
		<div id='day' style=" overflow-x: hidden; overflow-y: scroll;position:relative;" onscroll="$('form').hide();">   
			{section name=hrule loop=48}
				<div id="day_hrule_{$smarty.section.hrule.rownum}" class="hrule {cycle values="hruleodd,hruleeven"}" style="top: {math equation="x*20" x=$smarty.section.hrule.rownum }px; z-index: 1;"></div>
			{/section}
		
			<ul class='timeline' id='day_timeline'>
				<li>00<li>
				<li>01<li>
				<li>02<li>
				<li>03<li>
				<li>04<li>
				<li>05<li>
				<li>06<li>
				<li>07<li>
				<li id='day_08'>08<li>
				<li>09<li>
				<li>10<li>
				<li>11<li>
				<li>12<li>
				<li>13<li>
				<li>14<li>
				<li>15<li>
				<li>16<li>
				<li>17<li>
				<li>18<li>
				<li>19<li>
				<li>20<li>
				<li>21<li>
				<li>22<li>
				<li>23<li>
			</ul>
		
			<div id='day_select'></div>
			<div id='day_grid' onmousedown="Cal.onPositionNew(event);" onmousemove="Cal.onResizeNew(event);" onmouseup="Cal.onReadyNew();"></div>
			
			<div id='day_event_options'>
				<img id='day_option_edit' title='{t}Edit event{/t}' src='img/calendar/note_edit.png' style='cursor:pointer;' onclick="Cal.Edit();">
				<img id='day_option_delete' title='{t}Delete event{/t}' src='img/calendar/note_delete.png' style='cursor:pointer;' onclick="Cal.Delete('{t}Are you shure?{/t}');">
				<img id='day_option_split' title='{t}Make exception from series{/t}' src='img/calendar/arrow_divide.png' style='cursor:pointer;' onclick="Cal.SplitRecurrent();">
				<img id='day_option_join' title='{t}Return to series timeline{/t}' src='img/calendar/arrow_join.png' style='cursor:pointer;' onclick="Cal.JoinRecurrent();">
			</div>
			
			<img id='day_option_advanced' title='{t}Advanced properties{/t}' src='img/calendar/note.png' style='cursor:pointer;z-index:10;left:40px;position:absolute;' onclick="Cal.mode='add';Cal.ShowForm();">
		</div>
	</div>
	
	
	
	<div id='tab_week' class='hidden'>
		<div id='week_allday'>
			{section name=vrule loop=8 start=0}
				<div id="week_allday_vrule_{$smarty.section.vrule.index}" class="vrule" style="left: {math equation="x*100" x=$smarty.section.vrule.index }px; z-index: 1;"></div>
			{/section}
		</div>
	
		<div id='week' style="overflow-x: hidden; overflow-y: scroll;height:500px;position:relative;" onscroll="$('form').hide();">
			<ul class='timeline' id='week_timeline'>
				<li>00<li><li>01<li><li>02<li><li>03<li><li>04<li><li>05<li><li>06<li><li>07<li>
				<li id='day_08'>08<li>
				<li>09<li><li>10<li><li>11<li><li>12<li><li>13<li><li>14<li><li>15<li><li>16<li><li>17<li><li>18<li><li>19<li><li>20<li><li>21<li><li>22<li><li>23<li>
			</ul>
		
			{section name=hrule loop=48}
				<div id="week_hrule_{$smarty.section.hrule.rownum}" class="hrule {cycle values="hruleodd,hruleeven"}" style="top: {math equation="x*20" x=$smarty.section.hrule.rownum }px; z-index: 1;"></div>
			{/section}
		
			{section name=vrule loop=8 start=0}
				<div id="week_vrule_{$smarty.section.vrule.index}" class="vrule" style="left: {math equation="x*100" x=$smarty.section.vrule.index }px; z-index: 1;"></div>
			{/section}
		
			<div id='week_select'></div>
			<div id='week_selected_day'></div>
			<div id='week_grid' onmousedown="Cal.onPositionNew(event);" onmousemove="Cal.onResizeNew(event);" onmouseup="Cal.onReadyNew();"></div>
			<div id='week_event_options'>
				<img id='week_option_edit' title='{t}Edit event{/t}' src='img/calendar/note_edit.png' style='cursor:pointer;' onclick="Cal.Edit();">
				<img id='week_option_delete' title='{t}Delete event{/t}' src='img/calendar/note_delete.png' style='cursor:pointer;' onclick="Cal.Delete('##Are you shure?##');">
				<img id='week_option_split' title='{t}Make exception from series{/t}' src='img/calendar/arrow_divide.png' style='cursor:pointer;' onclick="Cal.SplitRecurrent();">
				<img id='week_option_join' title='{t}Return to series timeline{/t}' src='img/calendar/arrow_join.png' style='cursor:pointer;' onclick="Cal.JoinRecurrent();">
			</div>
			
			<img id='week_option_advanced' title='{t}Advanced properties{/t}' src='img/calendar/note.png' style='cursor:pointer;z-index:10;left:40px;position:absolute;' onclick="Cal.mode='add';Cal.ShowForm();">
		</div>
	</div>
	
	<div id='tab_month' class='hidden'>
		<div id='month_days'></div>
		<div id='month' style=" overflow-x: hidden; overflow-y: scroll;height:500px;position:relative;" onscroll="$('form').hide();"></div>
	</div>
</div>


<!--Cal initialization-->
{*literal}
<script type="text/javascript">	
window.onresize=function(){
	/*Cal.left=getX($(Cal.selectedTab));
	Cal.top=getY($(Cal.selectedTab));
	*/

	Cal.initialize();
	Cal.view(Cal.selectedTab);

	if ($('form').visible()) Cal.ShowForm();
}


</script>
{/literal*}
<!--/Cal initialization-->