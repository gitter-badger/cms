<?php
class calendar extends Controller
{
	var $models = array('ems_events', 'ems_service_groups', 'ems_locations', 'ems_services', 'ems_time_demand', 'ems_recurrences', 'ems_recurrence_exceptions', 'ems_time_proposal', 'kasutajad', 'webmail_cal_events', 'webmail_recurrences');
	var $userID = 0;

	function main()
	{
		global $template;
		extract($this->models);

		$arrLocations = $ems_locations->select('1=1', 0);
		$this->assign('arrLocations', $arrLocations);
		return $this->view('calendar.main.tpl');
	}

	function default_view()
	{
		$_SESSION['calendar']['default_tab'] = $_GET['tab'];
	}

	/**
	 * Ajax responce functions
	 */
	function fetch_day_events()
	{
		extract($this->models);
		$intStartTime = mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']);
		$intEndTime = mktime(23, 59, 59, $_GET['month'], $_GET['day'], $_GET['year']);

		//fetch simple events and exceptions
		$arrEvents = $ems_events->select('
	((start_unix>=' . $intStartTime . ' AND start_unix<=' . $intEndTime . ') OR
	(end_unix>=' . $intStartTime . ' AND end_unix<=' . $intEndTime . ')) AND
	(recurrence_flag=0 OR (recurrence_flag=1 AND recurrence_exception_flag=1)) AND 
	userID=' . $this->userID . ' ORDER BY start_unix', 0,
										 "id,subject,location,description,start_unix,end_unix,all_day_event_flag, recurrence_flag, event_group_id,end_time, recurrence_exception_flag");


		//fetch recurrencies and generate normal events
		$arrRecurrences = $ems_events->select('
	((t2.rec_start_unix>=' . $intStartTime . ' AND t2.rec_start_unix<=' . $intEndTime . ') OR
	(t2.rec_end_unix>=' . $intStartTime . ' AND t2.rec_end_unix<=' . $intEndTime . ') OR
	(t2.rec_start_unix<' . $intStartTime . ' AND t2.rec_end_unix>' . $intEndTime . ')) AND
	t1.recurrence_flag=1 AND 
	t1.recurrence_exception_flag=0 AND 
	t1.userID=' . $this->userID . ' ORDER BY start_unix', 0,
											  "t1.id,t1.subject,t1.location,t1.description,t1.start_unix,t1.end_unix,t1.all_day_event_flag, t1.recurrence_flag,
	t1.recurrence_exception_flag,t1.event_group_id,t1.end_time, t2.id as r_id,t2.rec_frequency,t2.rec_type,t2.rec_start_unix,
	FROM_UNIXTIME(t1.start_unix,'%H') as sH,
	FROM_UNIXTIME(t1.start_unix,'%i') as sM,
	FROM_UNIXTIME(t1.end_unix,'%H') as eH,
	FROM_UNIXTIME(t1.end_unix,'%i') as eM",
											  $ems_events->table . ' AS t1 LEFT JOIN ' . $ems_recurrences->table . ' AS t2 ON t1.id=t2.e_id');


		//fetch exceptions
		$arrExceptions = $ems_recurrence_exceptions->arrint('exc_dateunix=' . $intStartTime, 'r_id');


		//Set reccurent date to selected day
		if (is_array($arrRecurrences))
			foreach ($arrRecurrences as $key => $item) {

				$intPeriod = strtotime("+$item->rec_frequency " . str_replace('s', '', $item->rec_type)) - time();

				$arrRecurrences[$key]->start_unix = mktime($item->sH, $item->sM, 0, $_GET['month'], $_GET['day'], $_GET['year']);
				$arrRecurrences[$key]->end_unix = mktime($item->eH, $item->eM, 0, $_GET['month'], $_GET['day'], $_GET['year']);

				$intRecurrentAbsStart = mktime(0, 0, 0, date('m', $item->rec_start_unix), date('d', $item->rec_start_unix), date('Y', $item->rec_start_unix));
				if ((mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']) - $intRecurrentAbsStart) % $intPeriod) unset($arrRecurrences[$key]);

				//create hole for exceptions
				if (in_array($item->r_id, $arrExceptions)) unset($arrRecurrences[$key]);
			}


		//do date sorting one more time, so that recurrent and normal events are in same timeline
		$arrEvents = array_merge($arrEvents, $arrRecurrences);
		usort($arrEvents, 'date_sort');

		//do not calculate offset for all day events
		if (is_array($arrEvents))
			foreach ($arrEvents as $key => $item)
				if ($item->all_day_event_flag) $arrEvents[$key]->end_unix = $arrEvents[$key]->start_unix = mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']);

		$oConvertor = new ObjectCovertor();

		$arrEvents = $this->order_day_events($arrEvents); //make a width and left offset calculation

		if (is_array($arrEvents))
			foreach ($arrEvents as $recEvent) {
				$recEvent->start_time = date('H:i', $recEvent->start_unix);
				$arrArrEvents[] = $oConvertor->objectToArray($recEvent);
			}

		if (isset($arrArrEvents)) $arrResponce['events'] = $arrArrEvents;
		$arrResponce['day_start'] = mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']);
		$arrResponce['day_end'] = mktime(23, 59, 59, $_GET['month'], $_GET['day'], $_GET['year']);

		$this->use_gz = false;

		echo $oConvertor->arrayToJson($arrResponce);
	}

	function move_event()
	{
		extract($this->models);
		$recEvent = $ems_events->select('id=' . $_GET['ID']);
		if (is_object($recEvent)) {

			$intHeight = $recEvent->end_unix - $recEvent->start_unix;
			$recEvent->start_unix = $_GET['unix_start'];
			$recEvent->end_unix = $recEvent->start_unix + $intHeight;
			$recEvent->start_time = date('H:i:00', $recEvent->start_unix);
			$recEvent->end_time = date('H:i:00', $recEvent->end_unix);
			$recEvent->start_date = date('d/m/Y', $recEvent->start_unix);
			$recEvent->end_date = date('d/m/Y', $recEvent->end_unix);

			unset($recEvent->locationID);
			$ems_events->update($recEvent, 'id=' . $recEvent->id);
		}

	}

	function resize_event()
	{
		extract($this->models);
		$recEvent = $ems_events->select('id=' . $_GET['ID']);
		if (is_object($recEvent)) {
			$recEvent->end_unix = $recEvent->start_unix + $_GET['height'];
			$recEvent->end_date = date('d/m/Y', $recEvent->end_unix);
			$recEvent->end_time = date('H:i:00', $recEvent->end_unix);
			unset($recEvent->locationID);
			$ems_events->update($recEvent, 'id=' . $recEvent->id);
		}
	}

	function new_simple_event()
	{
		extract($this->models);
		$intStartDate = $_GET['unix_start'];
		$intEndDate = $_GET['unix_end'];
		$recCalender->start_date = date('d/m/Y', $intStartDate);
		$recCalender->end_date = date('d/m/Y', $intEndDate);
		$recCalender->start_time = date('H:i:s', $intStartDate);
		$recCalender->end_time = date('H:i:s', $intEndDate);
		$recCalender->start_unix = $intStartDate;
		$recCalender->end_unix = $intEndDate;
		$recCalender->subject = $_GET['subject'];
		$recCalender->userID = $this->userID;
		$ems_events->insert($recCalender);
	}

	function save_event()
	{
		extract($this->models);

		//convert dates
		$arrStartDate = explode('.', $_POST['start_date']);
		$arrEndDate = explode('.', $_POST['end_date']);
		$arrStartTime = explode(':', $_POST['start_time']);
		$arrEndTime = explode(':', $_POST['end_time']);

		$recCalender->recurrence_flag = isset($_POST['recurrency']) ? 1 : 0;

		//create timestamp
		if ($recCalender->recurrence_flag) {
			$intRecStartDate = mktime($arrStartTime[0], $arrStartTime[1], 0, $arrStartDate[1], $arrStartDate[0], $arrStartDate[2]);
			$intRecEndDate = mktime($arrEndTime[0], $arrEndTime[1], 0, $arrEndDate[1], $arrEndDate[0], $arrEndDate[2]);

			$intStartDate = mktime($arrStartTime[0], $arrStartTime[1], 0, $arrStartDate[1], $arrStartDate[0], $arrStartDate[2]);
			$intEndDate = mktime($arrEndTime[0], $arrEndTime[1], 0, $arrStartDate[1], $arrStartDate[0], $arrStartDate[2]);
		}
		else {
			$intStartDate = mktime($arrStartTime[0], $arrStartTime[1], 0, $arrStartDate[1], $arrStartDate[0], $arrStartDate[2]);
			$intEndDate = mktime($arrEndTime[0], $arrEndTime[1], 0, $arrEndDate[1], $arrEndDate[0], $arrEndDate[2]);
		}

		$recCalender->start_date = date('d/m/Y', $intStartDate);
		$recCalender->end_date = date('d/m/Y', $intEndDate);
		$recCalender->start_time = date('H:i:s', $intStartDate);
		$recCalender->end_time = date('H:i:s', $intEndDate);


		$recCalender->start_unix = $intStartDate;
		$recCalender->all_day_event_flag = isset($_POST['all_day']) ? 1 : 0;
		$recCalender->end_unix = $intEndDate;
		$recCalender->userID = $this->userID;
		$recCalender->subject = $_POST['subject'];

		$strLocation = $ems_locations->int($_POST['locationID'], 'title');
		if ($_POST['location'] == $strLocation) $recCalender->locationID = $_POST['locationID'];
		else $recCalender->location = $_POST['location'];


		$recCalender->event_group_id = $_POST['group_id'];

		if ($recCalender->recurrence_flag) {
			$recReccur->rec_start_date = date('d/m/Y', $intRecStartDate);
			$recReccur->rec_end_date = date('d/m/Y', $intRecEndDate);
			$recReccur->rec_start_unix = $intRecStartDate;
			$recReccur->rec_end_unix = $intRecEndDate;
			$recReccur->rec_type = $_POST['period2'];
			$recReccur->rec_frequency = $_POST['period'];
		}

		if (isset($_GET['ID'])) {
			$recReccur->e_id = $recCalender->ID = $_GET['ID'];
			$ems_events->update($recCalender);

			$exRecurrent = $ems_recurrences->obj('e_id=' . $recReccur->e_id);

			if ($recCalender->recurrence_flag) {
				if (is_object($exRecurrent))
					$ems_recurrences->update($recReccur, 'e_id=' . $recReccur->e_id);
				else
					$ems_recurrences->insert($recReccur);
			}

		}
		else {
			$recReccur->e_id = $recCalender->ID = $ems_events->insert($recCalender);
			if ($recCalender->recurrence_flag) $ems_recurrences->insert($recReccur);

		}
	}

	function delete_event()
	{
		extract($this->models);
		$ems_events->delete('id=' . $_GET['ID']);
	}

	function edit_event()
	{
		extract($this->models);
		$recEvent = $ems_events->obj('id=' . $_GET['ID']);
		$recEvent->start_date = date('d.m.Y', $recEvent->start_unix);
		$recEvent->start_time = date('H:i', $recEvent->start_unix);
		$recEvent->end_date = date('d.m.Y', $recEvent->end_unix);
		$recEvent->end_time = date('H:i', $recEvent->end_unix);
		if ($recEvent->locationID)
			$recEvent->location = $ems_locations->int($recEvent->locationID, 'title');

		if ($recEvent->recurrence_flag) {
			$recRecur = $ems_recurrences->obj('e_id=' . $_GET['ID']);
			$recEvent->start_date = date('d.m.Y', $recRecur->rec_start_unix);
			$recEvent->end_date = date('d.m.Y', $recRecur->rec_end_unix);
			$recEvent->rec_type = $recRecur->rec_type;
			$recEvent->rec_frequency = $recRecur->rec_frequency;
		}


		$this->use_gz = false;
		$oConvertor = new ObjectCovertor();
		echo $oConvertor->arrayToJson($oConvertor->arrayToJson($recEvent));
	}

	function split_recurrent()
	{
		extract($this->models);
		$recEvent = $ems_events->obj('id=' . $_GET['ID'], "*,FROM_UNIXTIME(start_unix,'%H') as sH,FROM_UNIXTIME(start_unix,'%i') as sM,FROM_UNIXTIME(end_unix,'%H') as eH,FROM_UNIXTIME(end_unix,'%i') as eM");
		$recRecurr = $ems_recurrences->obj('e_id=' . $_GET['ID']);

		$recEvent->recurrence_exception_flag = 1;
		$recEvent->start_unix = mktime($recEvent->sH, $recEvent->sM, 0, $_GET['month'], $_GET['day'], $_GET['year']);
		$recEvent->end_unix = mktime($recEvent->eH, $recEvent->eM, 0, $_GET['month'], $_GET['day'], $_GET['year']);
		unset($recEvent->id);
		unset($recEvent->sH);
		unset($recEvent->eH);
		unset($recEvent->sM);
		unset($recEvent->eM);
		if (!$recEvent->locationID) unset($recEvent->locationID);
		$recEvent->id = $ems_events->insert($recEvent);

		$recException->userID = $recEvent->userID;
		$recException->r_id = $recRecurr->id;
		$recException->exc_event_id = $recEvent->id;
		$recException->exc_dateunix = mktime(0, 0, 0, $_GET['month'], $_GET['day'], $_GET['year']);
		$recException->exc_date = $_GET['day'] . '/' . $_GET['month'] . '/' . $_GET['year'];
		$ems_recurrence_exceptions->insert($recException);
	}

	function join_recurrent()
	{
		extract($this->models);
		$ems_events->delete('id=' . $_GET['ID']);
		$ems_recurrence_exceptions->delete('exc_event_id=' . $_GET['ID']);
	}


	/**
	 * Modifies array, adds maxlevel, level params for
	 * user interface to draw correct position.
	 */

	function order_day_events($arrEvents)
	{
		$arrLevel1 = array();
		//find first-level events
		foreach ($arrEvents as $key => $recEvent) {
			if (!$this->cross_event($recEvent, $arrLevel1)) {
				$recEvent->level = 1;
				$recEvent->maxlevel = 1;
				$arrLevel1[] = $recEvent;
			}
			else $arrUnsorted[] = $recEvent;
		}

		$arrEvents = $arrLevel1;
		$arrLeveledEvents[1] = $arrEvents;
		$arrLeveledEvents[2] = array();

		//match each event with previous levels
		if (isset($arrUnsorted) && is_array($arrUnsorted))
			foreach ($arrUnsorted as $key => $recEvent) {
				$recEvent->group = $this->cross_event(&$recEvent, &$arrEvents);
				$recParent = end($recEvent->group);

				//scan lower levels for free space
				for ($i = 2; $i < $recParent->level + 1; $i++) {
					if (!$this->cross_event($recEvent, $arrLeveledEvents[$i - 1])) {
						$recEvent->level = $i - 1;
						$recEvent->maxlevel = $recParent->maxlevel;
						break;
					}
				}
				//go on higher level if no space is available
				if (!isset($recEvent->level)) {
					$recEvent->level = $recParent->level + 1;
					if ($recEvent->level > $recParent->maxlevel)
						$recEvent->maxlevel = $recParent->maxlevel + 1;
					else $recEvent->maxlevel = $recParent->maxlevel;
				}
				//			pre($recEvent);
				$this->update_max_level($recEvent, $recEvent->maxlevel);
				$arrEvents[] = $recEvent;
				$arrLeveledEvents[$recEvent->level][] = $recEvent;
				//			pre($recEvent);
			}
		foreach ($arrEvents as $key => $item)
			$arrEvents[$key]->group = '';

		return $arrEvents;
	}

	function update_max_level(&$recEvent, $maxLevel)
	{
		$recEvent->maxlevel = $maxLevel;
		if (isset($recEvent->group) && is_array($recEvent->group)) {
			foreach ($recEvent->group as $key => $item)
				$item->maxlevel = $maxLevel;

			foreach ($recEvent->group as $key => $item)
				$this->update_max_level($item, $maxLevel);

		}

	}

	/**
	 * Checks wether event start date is within lasting period
	 * of other event. Referencing event must be of maximum level.
	 * Returns other event's id. Otherwise returns false.
	 *
	 */
	function cross_event(&$recEvent, &$arrEvents)
	{
		$arrReturn = array();
		if (is_array($arrEvents))
			foreach ($arrEvents as $key => $recEvent2) {
				if ($recEvent2->start_unix > $recEvent->start_unix && $recEvent2->start_unix < $recEvent->end_unix ||
					$recEvent2->end_unix > $recEvent->start_unix && $recEvent2->end_unix < $recEvent->end_unix ||
					$recEvent2->start_unix <= $recEvent->start_unix && $recEvent2->end_unix >= $recEvent->end_unix
				)
					$arrReturn[] = $recEvent2;
			}
		if (count($arrReturn) > 0) return $arrReturn;
		else return false;
	}
}

function date_sort($a, $b)
{
	if ($a->start_unix > $b->start_unix) return true;
	else return false;
}

?>