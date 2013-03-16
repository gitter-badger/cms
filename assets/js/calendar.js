var Cal={
	mode:'add',
	url:'/',
	selectedID:0,
	selectedTab:'day',


	//canvas size
	'left':0,
	'top':0,
	'height':960,
	'width':552,

	'timeline_x':40,
	'scroll_x':25, //scroll on the right

	'form_width':400,
	'form_lasso':170, //height from top to lasso bottom end
	'alldayMax':0, //maximum number of events in the All Day section

	langDays:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
	langMonths:['January','February','March','April','May','June','July','August','September','October','November','December'],
	colors:[{title:'#29527A',bg:'#668CB3'},{title:'#B1440E',bg:'#E6804D'}],

	day_events:{},
	week_days:{},
	week_events:{},

	day:{
	'day':1,
	'month':1,
	'year':1970,
	'unix_start':0,
	'unix_end':0,
	'date':new Date()
	},

	day_canvas:{
	'timeline_width':40,
	'min_element_height':13,
	'form_top_offset':85, //form offset for
	'grid_mouse_down':false,
	'eventid_prefix':'day_event_',
	'rec_image':'', //recurrence icon URL
	'rec_exc_image':'' //recurrence exception icon URL
	},

	week_canvas:{
	'timeline_width':40,
	'day_width':100, //width in pixels
	'min_element_height':13,
	'form_top_offset':85,
	'grid_mouse_down':false,
	'eventid_prefix':'week_event_',
	'allday_element_height':20
	},

	week:{
	'unix_start':0, //real week start time in unix. Starts and includes Sunday.
	'unix_end':0,   //real week end time in unix
	'day_start':1, //visible first day. Monday here
	'day_end':7,		// visible last day. Friday here
	'focusedDay':0,
	'all_day_events':[0,0,0,0,0,0,0] //every day count of all_day_events, eg. monday:0 etc.
	},

	month:{
	'day_height':80,
	'day_width':77,
	'max_days':30,
	'prefix':'month_day_'
	},

	init:function(){
		Cal.url='/content/calendar/';
		//set constant variables
		Cal.langDays=[t('Sunday'),t('Monday'),t('Tuesday'),t('Wednesday'),t('Thursday'),t('Friday'),t('Saturday'),t('Sunday')];
		Cal.langMonths=[t('January'),t('February'),t('March'),t('April'),t('May'),t('June'),t('July'),t('August'),t('September'),t('October'),t('November'),t('December')];
		Cal.day_canvas.rec_image='img/calendar/repeat_white.gif';
		Cal.day_canvas.rec_exc_image='img/calendar/icon_repeat_broken_white.gif';

		Cal.day.date=new Date();
		//set starting date
		Cal.day.day=Cal.day.date.getDate();
		Cal.day.month=Cal.day.date.getMonth()+1;
		Cal.day.year=Cal.day.date.getFullYear();

		//Cal.day.date.setFullYear(Cal.day.year,Cal.day.month-1,Cal.day.day);
		Cal.day.date.setHours(0,0,0,0);
		Cal.day.unix_start=Cal.day.date.getTime()/1000;

		Cal.week.unix_start=Cal.day.unix_start-24*3600*(Cal.day.date.getDay()-1);
		Cal.week.unix_end=Cal.week.unix_start+7*24*3600;

		var totalWidth=$('cal_wrapper').offsetWidth;
		Cal.width=totalWidth-Cal.timeline_x-Cal.scroll_x;
		Cal.week_canvas.day_width=Math.round(Cal.width/(Cal.week.day_end-Cal.week.day_start+1));
		Cal.month.day_width=Math.round((Cal.width+Cal.timeline_x)/7);
		$('week_allday').style.width=Cal.width+'px';
		$('day_select').style.width=Cal.width+'px';
		Cal.drawRules();


		Draggables.addObserver({onEnd:Cal.onMove});
		Draggables.addObserver({onDrag:Cal.onMoveProcess});
		Draggables.addObserver({onEnd:Cal.onResize});
		Draggables.addObserver({onDrag:Cal.onResizeProcess});


		//view default tab
		Cal.view('day');
		Cal.left=getX('day');
		Cal.top=getY('day');
		$('recurrency2').toggle();

		var tempHeight=jQuery('#cal_wrapper').height()-jQuery('#navigation').height()-10;
		jQuery('#navigation').width(jQuery('#cal_wrapper').width()-5);
		jQuery('#tab_day').height(tempHeight);
		jQuery('#tab_week').height(tempHeight);
		jQuery('#tab_month').height(tempHeight);

	},

	//View management functions
	view:function(tab){
		Cal.selectedTab=tab;
		$('month').innerHTML='';
		var str='';
		$('day_heading').hide();
		$('week_heading').hide();
		$('month_heading').hide();

		switch (tab){
			case 'day':
			$(Cal.selectedTab+'_option_split').hide();
			$(Cal.selectedTab+'_option_join').hide();
			$(Cal.selectedTab+'_select').hide();
			$(Cal.selectedTab+'_select').hide();
			$(Cal.selectedTab+'_option_advanced').hide();
			$(Cal.selectedTab+'_event_options').hide();

			Cal.day.date.setFullYear(Cal.day.year,Cal.day.month-1,Cal.day.day);
			jQuery('#'+tab+'_heading').show();
			break;

			case 'week':
			$(Cal.selectedTab+'_option_split').hide();
			$(Cal.selectedTab+'_option_join').hide();
			$(Cal.selectedTab+'_select').hide();
			$(Cal.selectedTab+'_select').hide();
			$(Cal.selectedTab+'_option_advanced').hide();
			$(Cal.selectedTab+'_event_options').hide();

			var offset=(Cal.day.date.getDay());
			Cal.week.unix_start=Cal.day.unix_start-offset*24*3600;
			Cal.week.unix_end=Cal.week.unix_start+6*24*3600;
			jQuery('#'+tab+'_heading').show();
			$('week_selected_day').style.width=Cal.week_canvas.day_width+'px';
			$('week_selected_day').style.height=Cal.height+'px';
			$('week_selected_day').style.left=((offset-Cal.week.day_start)*Cal.week_canvas.day_width+1*Cal.week_canvas.timeline_width)+'px';

			for (var i=Cal.week.day_start;i<=Cal.week.day_end;i++){
				Cal.week_days[i]={};
				Cal.week_days[i].unix_start=Cal.week.unix_start+i*24*3600;
				Cal.week_days[i].unix_end=Cal.week.unix_end+(i+1)*24*3600;
				Cal.week_days[i].date=new Date(Cal.week_days[i].unix_start*1000);
				Cal.week_days[i].day=Cal.week_days[i].date.getDate();
				Cal.week_days[i].month=Cal.week_days[i].date.getMonth()+1;
				Cal.week_days[i].year=Cal.week_days[i].date.getFullYear();
			}

			break;

			case 'month':
			Cal.drawMonth();
			jQuery('#'+tab+'_heading').show();
			break;
		}

		Cal.fetch_events();
		var ajax =  new Ajax.Request(Cal.url+'default_view/?tab='+tab, {method:'GET', asynchronous:true, evalScripts:true});
		Cal.drawHeader(tab);
		Cal.emptyAllDay();
		Cal.switchTab(Cal.selectedTab);
		$(tab).scrollTop=300;
	},

	drawMonth:function(){
		var monthStart=new Date();
		var iterDate=new Date();
		var iterDay=1;
		var intLeft=0;
		var intTop=0;

		monthStart.setMonth(Cal.day.date.getMonth());
		Cal.day.month=Cal.day.date.getMonth()+1;
		Cal.day.year=Cal.day.date.getFullYear();
		Cal.day.day=Cal.day.date.getDate();

		monthStart.getDay();
		iterDate=monthStart;
		Cal.month.max_days=(32 - new Date(Cal.day.date.getFullYear(), Cal.day.date.getMonth(), 32).getDate());

		while(iterDay<Cal.month.max_days){
			iterDate.setDate(iterDay);
			var intWeekDay=iterDate.getDay()==0? 7 :iterDate.getDay(); //transform to 1-7 timeline
			intLeft=Cal.month.day_width*(intWeekDay-1);

			element=document.createElement('div');
			element.className='month_day';
			element.id=Cal.month.prefix+iterDay;
			element.style.width=Cal.month.day_width-1+'px';
			element.style.height=Cal.month.day_height-1+'px';
			element.innerHTML='<div class="month_header" onclick="Cal.month2day('+iterDay+');">'+iterDay+'</div>';
			element.style.left=intLeft+'px';
			element.style.top=intTop+'px';
			if (iterDay==Cal.day.date.getDate()) 	element.style.backgroundColor='#FFFFB3';

			if (intWeekDay==7) intTop=intTop+Cal.month.day_height;
			iterDay++;
			$('month').appendChild(element);
		}
	},

	month2day:function(day){
		Cal.day.day=day;
		Cal.day.date.setDate(day);
		Cal.view('day');
	},

	drawHeader:function(){
		$('day_heading').hide();
		$('week_heading').hide();
		var str='';
		switch (Cal.selectedTab){
			case 'day':
			str=Cal.langDays[Cal.day.date.getDay()]+', <b>'+Cal.day.date.getDate()+' '+Cal.langMonths[Cal.day.date.getMonth()]+'</b>';
			$('day_heading').innerHTML=str;
			$('day_heading').show();
			break;

			case 'week':
			str=str+"<table cellspacing=2 cellpadding=0><tr>";
			for (var i=Cal.week.day_start;i<=Cal.week.day_end;i++){

				str=str+"<td width='"+(2+1*Cal.week_canvas.day_width)+"' class='all_day_cell ";
				if (i==Cal.day.date.getDay())	str=str+"all_day_cell_selected";

				str=str+"' onclick='Cal.week2day("+i+");'>"+Cal.langDays[i]+"<br />"+Cal.week_days[i].day+'/'+(Cal.week_days[i].month)+"</td>";
			}
			str=str+'</tr></table>';
			$('week_heading').innerHTML=str;
			$('week_heading').show();
			break;

			case 'month':
			str=str+"<table cellspacing=2 cellpadding=0 width="+(7*Cal.month.day_width)+"><tr>";
			for (var i=1;i<=7;i++){
				var intDay=i;
				if (intDay==7) intDay=0;
				str=str+"<td width='"+Cal.month.day_width+"' style='text-align:center;";
				str=str+"'>"+Cal.langDays[intDay]+"</td>";
			}
			str=str+'</tr></table>';
			$('month_heading').innerHTML=Cal.langMonths[Cal.day.date.getMonth()]+' '+Cal.day.date.getFullYear();
			$('month_days').innerHTML=str;
			$('month_heading').show();
			break;


		}
	},

	switchTab:function(selected){
		jQuery('#tab_day').hide();
		jQuery('#tab_week').hide();
		jQuery('#tab_month').hide();

		jQuery('#tabb_'+selected).addClass('tab_selected');
		jQuery('#tab_'+selected).show()
	},


	week2day:function(i){
		Cal.day=Cal.week_days[i];
		Cal.view('day');
	},

	changePeriod:function (offset){
		$('form').hide();
		switch(Cal.selectedTab){
			case 'day':
			$(Cal.selectedTab+'_event_options').hide();
			$(Cal.selectedTab+'_option_advanced').hide();
			$(Cal.selectedTab+'_select').hide();
			Cal.day.date.setTime(Cal.day.date.getTime()+offset*1000*24*3600);
			Cal.day.day=Cal.day.date.getDate();
			Cal.day.month=Cal.day.date.getMonth()+1;
			Cal.day.year=Cal.day.date.getFullYear();
			Cal.view('day');
			break;
			case 'week':
			$(Cal.selectedTab+'_event_options').hide();
			$(Cal.selectedTab+'_option_advanced').hide();
			$(Cal.selectedTab+'_select').hide();
			Cal.day.date.setTime(Cal.day.date.getTime()+offset*7*1000*24*3600);
			Cal.day.unix_start=Cal.day.unix_start+offset*7*24*3600;
			Cal.day.unix_end=Cal.day.unix_end+offset*7*24*3600;
			Cal.day.day=Cal.day.date.getDate();
			Cal.day.month=Cal.day.date.getMonth()+1;
			Cal.day.year=Cal.day.date.getFullYear();
			Cal.view('week');
			break;
			case 'month':
			Cal.day.date.setMonth(Cal.day.date.getMonth()+offset);
			Cal.day.month=Cal.day.date.getMonth()+offset;
			Cal.view('month');
			break;
		}
		Cal.emptyAllDay();
	},

	fetch_events:function(){
		Cal.emptyEvents();
		Cal.emptyAllDay();
		switch(Cal.selectedTab){

			case 'day':
			var ajax =  new Ajax.Request(
			Cal.url+'fetch_day_events/?day='+Cal.day.day+'&month='+Cal.day.month+'&year='+Cal.day.year, {method:'GET', asynchronous:true, evalScripts:true,
			onComplete:function(responce){

				arrResponce=eval('(' + responce.responseText + ')');

				Cal.day.unix_start=arrResponce.day_start;
				Cal.day.unix_end=arrResponce.day_end;

				arrResponce.events.each(function(s,i){
					Cal.draw_day_event(s,i);
					Cal.day_events[s.id]=s;
				}
				);
			}
			});
			Cal.drawHeader();
			break;

			case 'week':
			var		week_day='';
			for (var i=Cal.week.day_start;i<=Cal.week.day_end;i++){
				week_day=Cal.week_days[i];

				var ajax =  new Ajax.Request(
				Cal.url+'fetch_day_events/?day='+week_day.day+'&month='+week_day.month+'&year='+week_day.year,
				{		method:'GET', asynchronous:true, evalScripts:true,
				onComplete:function(responce){Cal.receive_week_events(responce);}

				});

			};
			Cal.drawHeader();
			break;

			case 'month':
			for (var i=1;i<=Cal.month.max_days;i++)
			{
				var ajax =  new Ajax.Request(Cal.url+'fetch_day_events/?day='+i+'&month='+Cal.day.month+'&year='+Cal.day.year,
				{
					method:'GET', asynchronous:true, evalScripts:true,
					onComplete:function(responce){
						arrResponce=eval('(' + responce.responseText + ')');
						arrResponce.events.each(function(s,i){	Cal.draw_month_event(s,i);});
					}
				});

			}
			break;
		}
	},


	receive_week_events:function(responce){
		arrResponce=eval('(' + responce.responseText + ')');
		i=Math.round((arrResponce.day_start-Cal.week.unix_start)/(24*3600));
		arrResponce.events.each(function(o,iteration){
			Cal.draw_week_event(o,iteration,i);
			Cal.week_events[o.id]=o;
		});
	},


	// Canvas conversion functions
	time2pix:function (timestamp,zero){
		var timeperpix=((24*3600)/Cal.height);
		return Math.round((timestamp-zero)/timeperpix);
	},


	pix2weektime:function(x,y){
		var intDay=Math.round((x-Cal.week_canvas.timeline_width)/Cal.week_canvas.day_width)+Cal.week.day_start;

		var intTop=Math.round(24*3600*y/Cal.height);
		var intTime=Cal.week.unix_start+intDay*24*3600+intTop;
		return intTime;
	},


	pix2unix:function (topOffset,zero){
		return zero+24*3600*topOffset/Cal.height;
	},

	pix2hour:function (topOffset){
		var timestamp=Cal.day.unix_start+24*3600*topOffset/Cal.height;
		var tempDate=new Date(timestamp*1000);
		var strReturn= tempDate.getHours()>9 ? tempDate.getHours() : '0'+tempDate.getHours();
		strReturn+=tempDate.getMinutes()>9 ? ':'+tempDate.getMinutes() : ':0'+tempDate.getMinutes();
		return  strReturn;
	},


	emptyAllDay:function(){
		$('week_allday').style.height=Cal.week_canvas.allday_element_height+'px';
		document.getElementsByClassName('week_allday_event').each(function(s){	$('week_allday').removeChild(s);	});
		Cal.week.all_day_events=[0,0,0,0,0,0,0];
		Cal.alldayMax=0;
	},

	emptyEvents:function(){
		document.getElementsByClassName('day_event').each(function(s){	$('day').removeChild(s);	});
		document.getElementsByClassName('week_event').each(function(s){	$('week').removeChild(s);	});
	},


	//Drawing functions

	draw_month_event:function (obj, i){

		var StartDate=new Date(obj.start_unix*1000);
		var parentID=Cal.month.prefix+StartDate.getDate();
		var str='';
		element=document.createElement('div');
		element.className+='month_event';
		if (obj.all_day_event_flag==0) {
			str=str+obj.start_time;
			element.style.color=Cal.colors[obj.event_group_id-1].title;
		}
		else{
			element.style.backgroundColor=Cal.colors[obj.event_group_id-1].bg;
			element.style.color='white';
		}

		str=str+' '+obj.subject
		element.innerHTML=str;
		$(parentID).appendChild(element);
	},


	draw_week_event:function (obj,i,day){
		var intElementHeight=Cal.time2pix(obj.end_unix,Cal.week.unix_start)-Cal.time2pix(obj.start_unix,Cal.week.unix_start);
		var strID=Cal.week_canvas.eventid_prefix+obj.id;
		if (obj.recurrence_flag && !obj.recurrence_exception_flag) strID=strID+'_'+day;

		if (!obj.all_day_event_flag)	{
			if (intElementHeight<Cal.week_canvas.min_element_height) intElementHeight=Cal.day_canvas.min_element_height;
			if (intElementHeight>Cal.height) intElementHeight=Cal.height;

			element=document.createElement('div');

			element.className+='week_event';
			element.style.backgroundColor=Cal.colors[obj.event_group_id-1].bg;;
			element.style.border='1px solid '+Cal.colors[obj.event_group_id-1].title;
			element.style.height=intElementHeight-3+'px';
			element.style.zIndex=3+i;
			element.style.top=Cal.time2pix(obj.start_unix,Cal.week.unix_start+day*24*3600)+'px';

			var left=Cal.week_canvas.day_width*(day)+Cal.week_canvas.timeline_width;
			var width=Math.round((Cal.week_canvas.day_width)/obj.maxlevel)-4;

			if (obj.level>1)	left=Math.round(left+((obj.level-1)/obj.maxlevel)*(Cal.week_canvas.day_width));
			if (obj.level!=obj.maxlevel) width=Math.round(width*1.7);

			element.style.left=left+'px';
			element.style.width=width+'px';

			element.id=strID;
			element.onmouseover=function(){Cal.focus(obj.id,day);}
			element.ondblclick=function(){Cal.Edit();}

			strContent="<dl>";
			strContent+="<dt id='"+element.id+"_dt' style='background-color: "+Cal.colors[obj.event_group_id-1].title+";'>"+obj.start_time;
			if (obj.recurrence_flag && obj.recurrence_exception_flag) strContent+="&nbsp;&nbsp;<img src='"+Cal.day_canvas.rec_exc_image+"'>";
			else if (obj.recurrence_flag)	strContent+="&nbsp;&nbsp;<img src='"+Cal.day_canvas.rec_image+"'>";

			strContent+="</dt><dd class='subject'>"+obj.subject+"</dd>";

			strContent+="<dd unselectable='on' class='resizebar' id='"+element.id+"_resize'/></dl>";

			element.innerHTML=strContent;
			$('week').appendChild(element);

			var currentDiv=new Draggable(element.id);
			var currentDiv=new Draggable(element.id+'_resize');
		}
		//all-day events
		else{

			element=document.createElement('div');
			element.className+='week_allday_event';
			element.style.backgroundColor=Cal.colors[obj.event_group_id-1].bg;;
			element.style.border='1px solid '+Cal.colors[obj.event_group_id-1].title;
			element.style.height=Cal.week_canvas.allday_element_height-3+'px';
			element.style.top=Cal.week.all_day_events[day]*Cal.week_canvas.allday_element_height+1+'px';
			element.style.position='absolute';
			element.style.color='white';
			element.style.width=Cal.week_canvas.day_width-5+'px';
			element.style.left=Cal.week_canvas.day_width*(day-Cal.week.day_start)+3+'px';
			element.style.zIndex=3;
			element.innerHTML=obj.subject;
			element.ondblclick=function(){Cal.Edit();}
			element.onmouseover=function(){Cal.focus(obj.id,day);}
			element.id=strID;
			$('week_allday').appendChild(element);

			Cal.week.all_day_events[day]++;

			if(Cal.alldayMax<Cal.week.all_day_events[day]) {
				Cal.alldayMax=Cal.week.all_day_events[day];
				Cal.drawRules();
			}
			$('week_allday').style.height=Cal.alldayMax*Cal.week_canvas.allday_element_height+'px';
		}

	},




	drawRules:function(){
		for (var i=Cal.week.day_start-1;i<=Cal.week.day_end;i++){
			$('week_vrule_'+i).style.height=Cal.height+'px';
			$('week_vrule_'+i).style.left=Cal.week_canvas.timeline_width-3+i*Cal.week_canvas.day_width+'px';

			$('week_allday_vrule_'+i).style.height=Cal.alldayMax*Cal.week_canvas.allday_element_height+'px';
			$('week_allday_vrule_'+i).style.left=i*Cal.week_canvas.day_width+'px';
		}
	},



	draw_day_event:function (obj,i){
		var intElementHeight=Cal.time2pix(obj.end_unix,Cal.day.unix_start)-Cal.time2pix(obj.start_unix,Cal.day.unix_start);

		if (intElementHeight<Cal.day_canvas.min_element_height) intElementHeight=Cal.day_canvas.min_element_height;
		if (intElementHeight>Cal.height) intElementHeight=Cal.height;

		element=document.createElement('div');

		element.className+='day_event';
		element.style.backgroundColor=Cal.colors[obj.event_group_id-1].bg;;
		element.style.border='1px solid '+Cal.colors[obj.event_group_id-1].title;
		element.style.height=intElementHeight-3+'px';

		element.style.zIndex=3+i;
		element.style.top=Cal.time2pix(obj.start_unix,Cal.day.unix_start)+'px';

		if (obj.level>1)	element.style.left=Math.round(((obj.level-1)/obj.maxlevel)*(Cal.width-Cal.day_canvas.timeline_width)+Cal.day_canvas.timeline_width)+'px';
		else element.style.left=Cal.day_canvas.timeline_width+'px';


		var width=Math.round((Cal.width-Cal.day_canvas.timeline_width)/obj.maxlevel);

		if (obj.level!=obj.maxlevel) width=width*2;

		if (obj.maxlevel>1)	width=width-4;

		element.style.width=width+'px';

		element.id=Cal.day_canvas.eventid_prefix+obj.id;
		element.onmouseover=function(){Cal.focus(obj.id);}
		element.ondblclick=function(){Cal.Edit();}

		strContent="<dl><dt id='"+element.id+"_dt' style='background-color: "+Cal.colors[obj.event_group_id-1].title+";'>"+obj.start_time;
		if (obj.recurrence_flag && obj.recurrence_exception_flag) strContent+="&nbsp;&nbsp;<img src='"+Cal.day_canvas.rec_exc_image+"'>";
		else if (obj.recurrence_flag)	strContent+="&nbsp;&nbsp;<img src='"+Cal.day_canvas.rec_image+"'>";
		strContent+="</dt><dd class='subject'>"+obj.subject+"</dd><dd unselectable='on' class='resizebar' id='"+element.id+"_resize'/></dl>";

		element.innerHTML=strContent;

		$('day').appendChild(element);

		var currentDiv=new Draggable(element.id,{constraint:'vertical'});
		var currentDiv=new Draggable(element.id+'_resize',{constraint:'vertical'});
	},


	//Drag and drop events
	onMove:function (e,o){
		if (!(o.element.className=='day_event' || o.element.className=='week_event')) return false;

		var element=$(o.element.id);
		var ID=element.id.replace(Cal[Cal.selectedTab+'_canvas'].eventid_prefix,'');

		switch(Cal.selectedTab){
			case 'day':
			var intTime=(3600*24*element.style.top.replace('px','')/Cal.height)+Cal.day.unix_start;
			var ajax =  new Ajax.Request(Cal.url+'move_event/?ID='+ID+'&unix_start='+intTime, {method:'GET', asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});
			break;

			case 'week':
			ID=ID.split('_');
			ID=ID[0];
			var x=Math.round(1*element.style.left.replace('px',''));
			var y=1*element.style.top.replace('px','');
			var intTime=Cal.pix2weektime(x,y);
			var ajax =  new Ajax.Request(Cal.url+'move_event/?ID='+ID+'&unix_start='+intTime, {method:'GET', asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});
			break;
		}


		$('form').hide();
	},

	onMoveProcess:function(e,o){
		if (!(o.element.className=='day_event' || o.element.className=='week_event')) return false;
		$(Cal.selectedTab+'_event_options').hide();
		var element=$(o.element.id);
		$(element.id+'_dt').innerHTML=Cal.pix2hour(element.style.top.replace('px',''));
	},

	onResizeProcess:function (e,o){
		if (o.element.className!='resizebar') return false;
		var element=$(o.element.id);
		var eventID=element.id.replace('_resize','');
		if (($(element).style.top.replace('px','')*1)<0) $(element).style.bottom=0+'px';
		var intHeight=($(element).style.height.replace('px','')*1+$(element).style.top.replace('px','')*1);
		if (intHeight<Cal[Cal.selectedTab+'_canvas'].min_element_height) intHeight=Cal[Cal.selectedTab+'_canvas'].min_element_height;
		$(eventID).style.height=intHeight+'px';
	},

	onResize:function (e,o){
		if (o.element.className!='resizebar') return false;
		var element=$(o.element.id);
		var eventID=element.id.replace('_resize','');
		var intHeight=($(element).style.height.replace('px','')*1+$(element).style.top.replace('px','')*1);
		$(eventID).style.height=intHeight+'px';
		var intTime=24*3600*(intHeight/Cal.height);
		var ID=eventID.replace(Cal[Cal.selectedTab+'_canvas'].eventid_prefix,'');

		ID=ID.split('_');
		ID=ID[0];

		var ajax =  new Ajax.Request(Cal.url+'resize_event/?ID='+ID+'&height='+intTime, {method:'GET',asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});

		$(element).style.bottom=0+'px';
		$(element).style.top=	($(eventID).style.height.replace('px','')*1-$(element).style.height.replace('px','')*1)+'px';
	},



	onResizeNew:function (e){
		switch(Cal.selectedTab){
			case 'day':
			var intTop=$('day_select').style.top.replace('px','')
			var intHeight=getPosition(e).y-getY($('day'))+$('day').scrollTop - intTop;
			if (Cal.day_canvas.grid_mouse_down && intHeight>1){
				$('day_select').style.height=intHeight+'px';
				var intFontsize=intHeight<60 ? Math.round(intHeight/2) : 30;
				if (intFontsize<10) intFontsize=10;
				$('day_select').innerHTML="<span style='position:absolute;left:30px;top:-"+intFontsize+"px;color:#8997A5;font-size:"+intFontsize+"px;'>"+Cal.pix2hour(intTop)+' - '+Cal.pix2hour(1*intHeight+1*intTop)+"</span>	<textarea id='day_textarea' style='height:"+(intHeight-5)+"px;' onkeypress='return Cal.onEnterNew(this,event)'></textarea>	";
				$('day_option_advanced').style.top=intTop-15+'px';
				$('day_option_advanced').show();
			}
			break;
			case 'week':
			var intTop=$('week_select').style.top.replace('px','')
			var intHeight=getPosition(e).y-getY($('week'))+$('week').scrollTop - intTop;
			if (Cal.week_canvas.grid_mouse_down && intHeight>1){
				$('week_select').style.height=intHeight+'px';
				var intFontsize=intHeight<60 ? Math.round(intHeight/2) : 30;
				if (intFontsize<10) intFontsize=10;
				$('week_select').innerHTML="<span style='position:absolute;left:30px;top:-10px;color:#8997A5;font-size:10px;'>"+Cal.pix2hour(intTop)+' - '+Cal.pix2hour(1*intHeight+1*intTop)+"</span>	<textarea id='week_textarea' style='height:"+(intHeight-5)+"px;' onkeypress='return Cal.onEnterNew(this,event)'></textarea>	";
				$('week_option_advanced').style.top=intTop-15+'px';
				$('week_option_advanced').show();
			}
			break;
		}
	},

	onPositionNew:function (e){
		switch(Cal.selectedTab){

			case 'day':
			$('day_select').style.height='2px';
			var intTop=getPosition(e).y-getY($('day'))+$('day').scrollTop;
			$('day_select').style.top=intTop+'px';
			$('day_select').innerHTML='';//pix2hour(intTop)+' - '+pix2hour(intTop+Cal.day_canvas.min_element_height);
			$('day_select').show();
			$('day_option_advanced').hide();
			Cal.day_canvas.grid_mouse_down=1;
			$('form').hide();
			break;

			case 'week':
			$('week_select').style.height='2px';
			var intTop=getPosition(e).y-getY($('week'))+$('week').scrollTop;
			var intLeft=getPosition(e).x-Cal.left-Cal.week_canvas.timeline_width;
			var intDay=Cal.week_canvas.day_width*Math.ceil((intLeft/Cal.week_canvas.day_width)-Cal.week.day_start);

			$('week_select').style.top=intTop+'px';
			$('week_select').style.width=(Cal.week_canvas.day_width-5)+'px';
			$('week_select').style.left=Cal.week_canvas.timeline_width+intDay+'px';
			$('week_option_advanced').style.top=intTop-16+'px';
			$('week_option_advanced').style.left=Cal.week_canvas.timeline_width+intDay+'px';
			$('week_select').innerHTML='';
			$('week_select').show();
			Cal.week_canvas.grid_mouse_down=1;
			break;
		}
	},

	onReadyNew:function (){
		switch(Cal.selectedTab){
			case 'day':
			Cal.day_canvas.grid_mouse_down=0;
			$('day_option_advanced').show();
			if ($('day_textarea'))	$('day_textarea').focus();

			var intTop=$('day_select').style.top.replace('px','');
			var intHeight=$('day_select').style.height.replace('px','');

			$('start_date').value=zero(Cal.day.day)+'.'+zero(Cal.day.month)+'.'+Cal.day.year;
			$('end_date').value=zero(Cal.day.day)+'.'+zero(Cal.day.month)+'.'+Cal.day.year;
			$('start_time').value=Cal.pix2hour(intTop);
			$('end_time').value=Cal.pix2hour(1*intHeight+1*intTop);
			break;
			case 'week':
			Cal.week_canvas.grid_mouse_down=0;
			$('week_option_advanced').show();
			if ($('week_textarea')) $('week_textarea').focus();

			var intTop=$('week_select').style.top.replace('px','');
			var intHeight=$('week_select').style.height.replace('px','');
			var intLeft=1*$('week_select').style.left.replace('px','')+(1*$('week_select').style.width.replace('px','')/2)
			var unix_start=Cal.pix2weektime(intLeft,intTop);
			var unix_end=Cal.pix2weektime(intLeft,1*intTop+1*intHeight);
			var date_start=new Date(unix_start*1000);
			var date_end=new Date(unix_start*1000);

			$('start_date').value=zero(date_start.getDate())+'.'+zero(1+date_start.getMonth())+'.'+date_start.getFullYear();
			$('end_date').value=zero(date_end.getDate())+'.'+zero(1+date_end.getMonth())+'.'+date_end.getFullYear();
			$('start_time').value=Cal.pix2hour(intTop);
			$('end_time').value=Cal.pix2hour(1*intHeight+1*intTop);
			break;
		}
	},




	focus:function(ID,day){
		Cal.selectedID=ID;
		Cal.week.focusedDay=day;

		var canvas=Cal[Cal.selectedTab+'_canvas'];
		var events=Cal[Cal.selectedTab+'_events'];
		var strID=ID;

		if (events[ID].recurrence_flag==1 && events[ID].recurrence_exception_flag==0 && Cal.selectedTab=='week')
		strID=strID+'_'+day;

		var intLeft=$(canvas.eventid_prefix+strID).style.left.replace('px','')*1


		$(Cal.selectedTab+'_event_options').style.top=($(canvas.eventid_prefix+strID).style.top.replace('px','')*1)+'px'; //-20

		if (events[ID].recurrence_flag==1 && Cal.selectedTab=='week') intLeft=(day)*Cal.week_canvas.day_width-Cal.week_canvas.timeline_width-20;
		$(Cal.selectedTab+'_event_options').style.left=intLeft+($(canvas.eventid_prefix+strID).style.width.replace('px','')*0.5-20)+'px';


		if (events[ID].recurrence_flag==1 && events[ID].recurrence_exception_flag==0){
			$(Cal.selectedTab+'_option_split').show();
			$(Cal.selectedTab+'_option_join').hide();
		}
		else if (events[ID].recurrence_flag==1 && events[ID].recurrence_exception_flag==1){
			$(Cal.selectedTab+'_option_split').hide();
			$(Cal.selectedTab+'_option_join').show();
		}
		else{
			$(Cal.selectedTab+'_option_split').hide();
			$(Cal.selectedTab+'_option_join').hide();
		}
		$(Cal.selectedTab+'_event_options').show();

	},




	Edit:function(){
		Cal.mode='edit';

		var ajax =  new Ajax.Request(
		Cal.url+'edit_event/?ID='+Cal.selectedID, {method:'GET', asynchronous:true, evalScripts:true,
		onComplete:function(responce){
			f=eval('(' + responce.responseText + ')');

			$('subject').value=f.subject;
			$('txtCombo').value=f.location;
			$('selCombo').selectedIndex=f.locationID;

			if (f.all_day_event_flag)	{
				$('all_day').checked=true;
				$('recurrency').hide();
				$('start_time_box').hide();
				$('end_time_box').hide()
			}
			else {
				$('all_day').checked=false;
				$('recurrency').show();
				$('start_time_box').show();
				$('end_time_box').show()
			}

			if (f.recurrence_flag){
				$('start_date').value=f.start_date;
				$('end_date').value=f.end_date;
				$('recurrency_checkbox').checked=true;

				if (f.rec_type=='days')	$('rec_type').selectedIndex=0;
				else if (f.rec_type=='weeks')	$('rec_type').selectedIndex=1;
				else $('rec_type').selectedIndex=2;
				$('rec_frequency').selectedIndex=f.rec_frequency-1;

				$('recurrency2').show();
			}
			else{
				$('start_date').value=f.start_date;
				$('end_date').value=f.end_date;
				$('recurrency_checkbox').checked=false;
				$('recurrency2').hide();
			}

			$('end_time').value=f.end_time;
			$('start_time').value=f.start_time;

			$('group_id').selectedIndex=f.event_group_id-1;

			Cal.ShowForm();
			$('subject').focus();
		}});
	},


	onEnterNew:function (o,e){
		$('subject').value=o.value;
		var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
		if (keyCode == 13) {
			switch (Cal.selectedTab){
				case 'day':
				var unix_start=Cal.pix2unix($('day_select').style.top.replace('px',''),Cal.day.unix_start);
				var unix_end=Cal.pix2unix(1*$('day_select').style.top.replace('px','')+1*$('day_select').style.height.replace('px',''),Cal.day.unix_start);
				var ajax =  new Ajax.Request(Cal.url+'new_simple_event/?subject='+o.value+'&unix_start='+unix_start+'&unix_end='+unix_end, {method:'GET',asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});
				$('day_select').hide();
				$('day_option_advanced').hide();
				break;

				case 'week':
				var intTop=$('week_select').style.top.replace('px','');
				var intHeight=$('week_select').style.height.replace('px','');
				var intLeft=1*$('week_select').style.left.replace('px','')+(1*$('week_select').style.width.replace('px','')/2)
				var unix_start=Cal.pix2weektime(intLeft,intTop);
				var unix_end=Cal.pix2weektime(intLeft,1*intTop+1*intHeight);
				var ajax =  new Ajax.Request(Cal.url+'new_simple_event/?subject='+o.value+'&unix_start='+unix_start+'&unix_end='+unix_end, {method:'GET',asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});
				$('week_select').hide();
				$('week_option_advanced').hide();
				break;
			}
		}
	},


	ShowForm:function(){
		$('form_bg').style.height=document.body.clientHeight+'px';
		$('form_bg').show();
		$('form').show();
	},

	FormClick:function(e){
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	},

	FormReccurentCheck:function(){
		if ($('recurrency_checkbox').checked) $('recurrency2').show();
		else {
			$('recurrency2').hide();
			$('end_date').value=	$('start_date').value;
		}

	},

	//Event management

	Submit:function(){
		if (Cal.mode=='edit')
		var ajax =  new Ajax.Request(Cal.url+'save_event/?ID='+Cal.selectedID, {method:'POST',postBody:Form.serialize('registration_form'),asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});

		else if (Cal.mode=='add')
		var ajax =  new Ajax.Request(Cal.url+'save_event', {method:'POST',postBody:Form.serialize('registration_form'),asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});


		$('form').hide();
		$(Cal.selectedTab+'_select').hide();
		$(Cal.selectedTab+'_option_advanced').hide();
	},

	Delete:function(strConfirm){
		$(Cal.selectedTab+'_event_options').hide();
		if (confirm(strConfirm))
		var ajax =  new Ajax.Request(Cal.url+'delete_event/?ID='+Cal.selectedID, {method:'GET',asynchronous:true, evalScripts:true,	onComplete:Cal.fetch_events});
	},


	SplitRecurrent:function(){
		switch(Cal.selectedTab){
			case 'day':
			var ajax =  new Ajax.Request(Cal.url+'split_recurrent/?ID='+Cal.selectedID+'&day='+Cal.day.day+'&month='+Cal.day.month+'&year='+Cal.day.year, {method:'GET', asynchronous:true, evalScripts:true, onComplete:Cal.fetch_events})
			$('day_event_options').hide();
			break;
			case 'week':
			var tmpDate=new Date(1000*(Cal.week.unix_start+Cal.week.focusedDay*24*3600));
			var ajax =  new Ajax.Request(Cal.url+'split_recurrent/?ID='+Cal.selectedID+'&day='+tmpDate.getDate()+'&month='+(tmpDate.getMonth()+1)+'&year='+tmpDate.getFullYear(), {method:'GET', asynchronous:true, evalScripts:true, onComplete:Cal.fetch_events})
			$('week_event_options').hide();
			break;
		}

	},

	JoinRecurrent:function(){
		var ajax =  new Ajax.Request(Cal.url+'join_recurrent/?ID='+Cal.selectedID, {method:'GET', asynchronous:true, evalScripts:true, onComplete:Cal.fetch_events})
		$('day_event_options').hide();
		$('week_event_options').hide();
	}

}


//Positioning functions, used by Cal object

function getPosition(e) {
	e = e || window.event;
	var cursor = {x:0, y:0};

	if (e.pageX || e.pageY) {
		cursor.x = e.pageX;
		cursor.y = e.pageY;
	}
	else {
		var de = document.documentElement;
		var b = document.body;
		cursor.x = e.clientX +
		(de.scrollLeft || b.scrollLeft) - (de.clientLeft || 0);
		cursor.y = e.clientY +
		(de.scrollTop || b.scrollTop) - (de.clientTop || 0);
	}
	return cursor;
}

function getY( oElement ){
	var iReturnValue = 0;
	while( oElement != null ) {
		iReturnValue += oElement.offsetTop;
		oElement = oElement.offsetParent;
	}
	return iReturnValue;
}

function getX( oElement ){
	var iReturnValue = 0;
	while( oElement != null ) {
		iReturnValue += oElement.offsetLeft;
		oElement = oElement.offsetParent;
	}
	return iReturnValue;
}

function zero(e){
	return e<9?'0'+e:e;
}

function ValiKuup(kuup){
	displayCal(kuup, 'dd.mm.yyyy', kuup);
}