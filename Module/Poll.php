<?php
/**
 * Poll module
 * @version 1.0.1
 */
namespace Gratheon\CMS\Module;
use Gratheon\CMS;

class Poll extends \Gratheon\CMS\ContentModule {

	public $name = 'poll';
	public $static_methods = array('svg', 'vote');
	public $models = array('content_poll_votes', 'content_poll', 'content_menu', 'content_poll_answers', 'sys_tags', 'content_tags', 'sys_user');


	function front_view($parentID) {
		$content_menu          = $this->model('Menu');
		$content_poll          = $this->model('content_poll');
		$content_poll_question = $this->model('content_poll_question');
		$content_poll_answers  = $this->model('content_poll_answers');
		$content_poll_response = $this->model('content_poll_response');
		$content_poll_votes    = $this->model('content_poll_votes');

		$recEntry = $content_menu->obj("ID=" . $parentID . " AND module IN ('poll') ORDER BY date_added DESC",
			"*,DATEDIFF(NOW(),date_added) as diff,DATE_FORMAT(date_added,'%d.%m %H:%i') as date_added2");

		$recPoll            = $content_poll->obj("parentID='$parentID'");
		$recPoll->questions = $content_poll_question->arr("pollID='{$recPoll->ID}'");

		foreach($recPoll->questions as &$question) {
			$question->answers = $content_poll_answers->arr("questionID='{$question->ID}'");
		}

		$this->assign('poll', $recPoll);


		if($this->controller->in->post) {

			if(!isset($this->controller->user->data['ID'])){
				$this->controller->session->set('messages', array(
					'error'=> array('Please login')
				));
			}

			else{
				$recResponse             = new \Gratheon\Core\Record();
				$recResponse->pollID     = $recPoll->ID;
				$recResponse->userID     = $this->controller->user->data['ID'];
				$recResponse->date_added = 'NOW()';

				$responseID = $content_poll_response->insert($recResponse);
				foreach($this->controller->in->post['question'] as $questionID => $answer) {

					$vote = new \Gratheon\Core\Record();

					$vote->questionID = $questionID;
					$vote->pollID     = $recPoll->ID;
					$vote->responseID = $responseID;
					$vote->answerID   = $answer;

					$content_poll_votes->insert($vote);
				}

				$this->controller->session->set('messages', array(
					'ok'=> array('Test saved and forwared')
				));
			}

		}

		$messages = $this->controller->session->get('messages');
		$this->controller->session->set('messages','');
		if($messages && $messages['ok']){
			$this->assign('ok', $messages['ok']);
		}

//        global $menu, $controller;
//
//        $tree = new \Gratheon\CMS\Tree;
//
//        $sys_tags = $this->model('sys_tags');
//        $content_tags = $this->model('content_tags');
//
//        $controller->add_js('front.front.article.js');
//
//
//        $recEntry->url = $menu->getPageURL($recEntry->ID);
//        $recEntry->element = $content_poll->obj('parentID=' . $recEntry->ID);
//        $recEntry->poll = $this->getPollData($recEntry->element->ID);
//
//        //Tags
//        $recEntry->arrTags = $sys_tags->arr(
//            't1.ID=t2.tagID AND t2.contentID=' . $parentID,
//            't1.ID, t1.pop, t1.title',
//                $sys_tags->table . ' t1 LEFT JOIN ' . $content_tags->table . ' t2 ON t1.ID=t2.tagID'
//        );
//
//
//        $objFile = new modFile;
//        $objFile->load_models();
//
//        $objComment = new modComment;
//        $objComment->load_models();
//
//        //add files and comments
//        $recEntry->arrFiles = $objFile->getNodeFiles($recEntry->ID);
//        $recEntry->arrComments = $objComment->getNodeComments($recEntry->ID);
//
//        //build navigation
//        $arrSelected = $tree->buildSelected($parentID);
//        $recEntry->navigation = $tree->buildLevels($arrSelected);
//
//        $this->assign('link_comment', sys_url . 'front/call/comment/front_add/?nodeID=' . $parentID);
//        $this->assign('comment_fields', $_SESSION['front']['comment_fields']);
//        $this->assign('ok_field', $_SESSION['front']['comment_field']);
		$this->assign('element', $recEntry);
	}


	function category_view(&$item) {
		$tree = new \Gratheon\CMS\Tree;
		$menu = new \Gratheon\CMS\Menu();
		$menu->loadLanguageCount();

		$sys_tags     = $this->model('sys_tags');
		$content_tags = $this->model('content_tags');
		$content_poll = $this->model('content_poll');

		$this->add_css('poll/poll.css');
		$item->element = $content_poll->obj('parentID=' . $item->ID);
		$item->poll    = $this->getPollData($item->element->ID);
		$item->url     = $menu->getPageURL($item->ID);

		#Tags
		$item->arrTags = $sys_tags->arr(
			't1.ID=t2.tagID AND t2.contentID=' . $item->ID,
			't1.ID, t1.pop, t1.title',
				$sys_tags->table . ' t1 LEFT JOIN ' . $content_tags->table . ' t2 ON t1.ID=t2.tagID'
		);

		#build navigation
		$arrSelected      = $tree->buildSelected($item->ID);
		$item->navigation = $tree->buildLevels($arrSelected);
	}


	function insert($parentID) {
		$content_poll         = $this->model('content_poll');
		$content_poll_answers = $this->model('content_poll_answers');

		$recElement = new \Gratheon\Core\Record();
		$recAnswer  = new \Gratheon\Core\Record();

		$recElement->parentID    = $parentID;
		$recElement->title       = $this->controller->in->post['title'];
		$recElement->restriction = $this->controller->in->post['restriction'];
		$recAnswer->pollID       = $recElement->ID = $content_poll->insert($recElement);

		foreach((array)$this->controller->in->post['values'] as $strAnswer) {
			if(strlen($strAnswer) > 0) {
				$recAnswer->answer = $strAnswer;
				$recAnswer->orderID++;
				$content_poll_answers->insert($recAnswer);
			}
		}
	}


	function edit($recMenu = null) {

		$content_poll         = $this->model('content_poll');
		$content_poll_answers = $this->model('content_poll_answers');
		$content_poll_votes   = $this->model('content_poll_votes');
		$sys_user             = $this->model('sys_user');

		$parentID = $recMenu->ID;

		if($parentID) {
			$recElement          = $content_poll->obj('parentID=' . $parentID);
			$recElement->answers = $content_poll_answers->arr("pollID='" . $recElement->ID . "' ORDER BY orderID");

			$arrVotes = $content_poll_votes->arr(
				"t1.pollID='" . $recElement->ID . "' ORDER BY t1.date_added",
				"DATE_FORMAT(t1.date_added,'%d.%m.%Y %H:%i') date_added_formatted,
			INET_NTOA(t1.IP) IP,
			t2.answer,
			t3.login",
					$content_poll_votes->table . ' t1 LEFT JOIN ' .
							$content_poll_answers->table . ' t2 ON t2.ID=t1.answerID LEFT JOIN ' .
							$sys_user->table . ' t3 ON t3.ID=t1.userID');

			$this->assign('arrData', $arrVotes);
			$this->assign('recElement', $recElement);
		}
		$this->assign('show_URL', true);
	}


	function update($parentID) {
		$content_poll         = $this->model('content_poll');
		$content_poll_answers = $this->model('content_poll_answers');

		$recElement = $content_poll->obj('parentID=' . $parentID);

		$recElement->title       = $this->controller->in->post['title'];
		$recElement->restriction = $this->controller->in->post['restriction'];
		$content_poll->update($recElement);
		$position = 1;

		foreach((array)$this->controller->in->post['values'] as $key => $strAnswer) {
			if(strlen($strAnswer) > 0) {
				$recAnswer          = new \Gratheon\Core\Record();
				$recAnswer->answer  = $strAnswer;
				$recAnswer->orderID = $position;
				$content_poll_answers->update($recAnswer, "ID=" . $key);
				$position++;
			}
		}

	}


	function delete($parentID) {
		$content_poll         = $this->model('content_poll');
		$content_poll_answers = $this->model('content_poll_answers');
		$content_poll_votes   = $this->model('content_poll_votes');

		$recElement = $content_poll->obj('parentID=' . $parentID);
		$content_poll_votes->delete('pollID=' . $recElement->ID);
		$content_poll_answers->delete('pollID=' . $recElement->ID);
		$content_poll->delete($recElement->ID);

	}


	//Custom methods

	function getPollData($ID) {
		$content_poll_answers = $this->model('content_poll_answers');
		$element              = new \Gratheon\Core\Record();

		$element->answers     = $content_poll_answers->arr('pollID=' . $ID);
		$arrAnswerCount       = $content_poll_answers->arrint("pollID=" . $ID, 'voteCount');
		$element->total_votes = array_sum($arrAnswerCount);

		if($element->total_votes > 0) {
			foreach($arrAnswerCount as $key => $intAnswerCount) {
				$arrPollPercentage[$key] = (100 * $intAnswerCount / $element->total_votes);
			}
		}
		$element->answer_percentage      = $arrPollPercentage;
		$element->answer_percentage_coma = implode(',', (array)$arrPollPercentage);
		return $element;
	}


	function vote() {
		$content_poll         = $this->model('content_poll');
		$content_poll_answers = $this->model('content_poll_answers');
		$content_poll_votes   = $this->model('content_poll_votes');

		global $user;

		$recVote = $recReply = new \Gratheon\Core\Record();

		$recReply->pollID    = $recVote->pollID = $ID = (int)$_GET['ID'];
		$recReply->answerID  = $recVote->answerID = (int)$_GET['answerID'];
		$recVote->IP         = "INET_ATON('" . $user->IP . "')";
		$recVote->userID     = $user->data['ID'];
		$recVote->date_added = 'NOW()';

		if(!$recVote->pollID || !$recVote->answerID) {
			return;
		}

		$objPoll = $content_poll->obj($ID);
		if($objPoll->restriction == 'IP') {
			$exVote = $content_poll_votes->obj("pollID=" . $recVote->pollID . " AND IP=" . $recVote->IP);
		}
		else {
			$exVote = $content_poll_votes->obj("pollID=" . $recVote->pollID . " AND userID=" . $user->data['ID']);
		}

		if($exVote) {
			$content_poll_votes->update($recVote, "ID=" . $exVote->ID);
			$recReply->msg = "changed";
		}
		else {
			$content_poll_votes->insert($recVote);
			$recReply->msg = "added";
		}
		$content_poll_votes->delete('IP=0');
		$content_poll_answers->q("UPDATE " . $content_poll_answers->table . " SET voteCount=(SELECT COUNT(ID) FROM " . $content_poll_votes->table . " t2 WHERE t2.answerID=" . $content_poll_answers->table . ".ID) WHERE pollID=" . $recVote->pollID);


		$recReply->arrVotes = $content_poll_answers->arrint("pollID=" . $recVote->pollID, 'voteCount');

		$oConvertor = new \Gratheon\Core\ObjectCovertor();
		return $oConvertor->arrayToJson($oConvertor->objectToArray($recReply));
	}


	function svg() {

		$ID = (int)$_GET['ID']; //(int)$_GET['PollID'];
		if(!$ID) {
			return;
		}
		$this->controller->MIME = "image/svg+xml";

		//Data initialization, should be sorted
		$Data = $this->getPollData($ID);

		$graph = new \stdClass();

		//Graph config
		$graph->width     = $_GET['w'] ? (int)$_GET['w'] : 370;
		$graph->height    = $_GET['h'] ? (int)$_GET['h'] : 100;
		$graph->padding   = 5;
		$graph->fill      = array(255, 153, 0);
		$graph->cx        = 0.5 * $graph->width;
		$graph->cy        = 0.5 * $graph->height;
		$graph->square    = $graph->width > $graph->height ? $graph->height : $graph->width;
		$graph->rx        = 0.7 * ($graph->square - $graph->padding);
		$graph->ry        = 0.45 * ($graph->square / 2 - $graph->padding);
		$graph->thickness = 0.1 * ($graph->square / 2 - $graph->padding);

		//Output
		$out = '<?xml version="1.0" encoding="utf-8"?>';
		$out .= "\n" . '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
		$out .= "\n" . '<svg width="' . $graph->width . '" height="' . $graph->height . '" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1">';

		if($Data->total_votes < 2) {
			return $out . "\n" . '</svg>';
		}

		$Data = $Data->answers;

		$out .= "\n" . '<defs>
			 <linearGradient id="MyGradient">
			    <stop offset="5%" stop-color="rgb(65,65,65)"/>
			    <stop offset="35%" stop-color="rgb(200,200,200)"/>
			    <stop offset="95%" stop-color="rgb(65,65,65)"/>
			  </linearGradient>
			</defs>';

		//echo "\n".'<script type="text/ecmascript" xlink:href="/application/front/view/js/svg_poll.js" />';
		//   echo "\n".'<ellipse cx="'.$graph->cx.'" cy="'.($graph->cy+$graph->thickness).'" rx="'.$graph->rx.'" ry="'.$graph->ry.'" fill="url(#MyGradient)" />';
		$out .= "\n" . '<ellipse cx="' . $graph->cx . '" cy="' . $graph->cy . '" rx="' . $graph->rx . '" ry="' . $graph->ry . '" fill="#905906" />';
		$out .= "<path d='
		    M" . ($graph->cx + $graph->rx) . ",$graph->cy
		    l0,$graph->thickness 
		    a$graph->rx,$graph->ry 0 1,1 " . (-2 * $graph->rx) . ",0
		    l0,-$graph->thickness
		    a$graph->rx,$graph->ry 0 1,0 " . (2 * $graph->rx) . ",0
		    ' stroke-width='0'  fill='url(#MyGradient)'/>";

		//Data processing
		//$arrPrevCoord=array('x'=>$graph->rx,'y'=>0);
		$intDegreeShift = 15;
		$intTotalValue  = 0;
		foreach((array)$Data as $recEntry) {
			$intTotalValue += $recEntry->voteCount;
		}

		if($intTotalValue) {
			foreach((array)$Data as $key => $recEntry) {
				$Data[$key]->percent  = $recEntry->voteCount / $intTotalValue;
				$Data[$key]->color[0] = round($graph->fill[0] + ($key / count($Data) * (255 - $graph->fill[0])));
				$Data[$key]->color[1] = round($graph->fill[1] + ($key / count($Data) * (255 - $graph->fill[1])));
				$Data[$key]->color[2] = round($graph->fill[2] + ($key / count($Data) * (255 - $graph->fill[2])));

				$Data[$key]->degree     = 360 * $Data[$key]->percent;
				$Data[$key]->start['x'] = $graph->cx + round(cos(deg2rad($intDegreeShift)) * $graph->rx, 3);
				$Data[$key]->start['y'] = $graph->cy + round(sin(deg2rad($intDegreeShift)) * $graph->ry, 3);
				$Data[$key]->end['x']   = $graph->cx + round(cos(deg2rad($intDegreeShift + $Data[$key]->degree)) * $graph->rx, 3);
				$Data[$key]->end['y']   = $graph->cy + round(sin(deg2rad($intDegreeShift + $Data[$key]->degree)) * $graph->ry, 3);

				$Data[$key]->tip_start['x'] = $graph->cx + round(cos(deg2rad($intDegreeShift + 0.5 * $Data[$key]->degree)) * $graph->rx, 3);
				$Data[$key]->tip_start['y'] = $graph->cy + round(sin(deg2rad($intDegreeShift + 0.5 * $Data[$key]->degree)) * $graph->ry, 3);

				$Data[$key]->tip_end['x'] = $Data[$key]->tip_start['x'] > $graph->cx ? ($graph->cx + $graph->rx + $graph->padding) : ($graph->cx - $graph->rx - $graph->padding);


				$intDegreeShift += $Data[$key]->degree; //increase degree shift

				$boolIsLargeArc = $Data[$key]->degree > 180 ? 1 : 0;
				$out .= "\n" . '<path cursor="pointer" onclick="parent.poll_vote(' . $ID . ',' . $recEntry->ID . ');this.fill=\'black\';" d="M' . $graph->cx . ',' . $graph->cy . ' L' . $Data[$key]->start['x'] . ',' . $Data[$key]->start['y'] . ' A' . $graph->rx . ',' . $graph->ry . ' 0 ' . $boolIsLargeArc . ',1 ' . $Data[$key]->end['x'] . ',' . $Data[$key]->end['y'] . ' z" style="stroke:black;stroke-width: 0;fill:rgb(' . implode(',', $Data[$key]->color) . ');fill-opacity: 1;"/>';


				/**
				 * Gradient filling
				 */

				//Fill front gradient with color
				if(($intDegreeShift + $Data[$key]->degrees) < 180) {
					$out .= "
				    <path d='
				    M" . ($Data[$key]->start['x']) . "," . $Data[$key]->start['y'] . "
				    l0,$graph->thickness
				    A$graph->rx,$graph->ry 0 0,1 " . $Data[$key]->end['x'] . "," . ($Data[$key]->end['y'] + $graph->thickness) . "
				    l0,-$graph->thickness
				    A$graph->rx,$graph->ry 0 0,0 " . ($Data[$key]->start['x']) . "," . $Data[$key]->start['y'] . "
				    ' style='stroke:black;stroke-width: 0;fill:rgb(" . implode(',', $Data[$key]->color) . ");fill-opacity: 0.5;'/>";
				}
				//Chop crossing 180-degree sectors
				elseif($intDegreeShift > 180 && ($intDegreeShift - $Data[$key]->degree) < 180) {
					$out .= "
				    <path d='
				    M" . ($Data[$key]->start['x']) . "," . $Data[$key]->start['y'] . "
				    l0,$graph->thickness
				    A$graph->rx,$graph->ry 0 0,1 " . ($graph->cx - $graph->rx) . "," . ($graph->cy + $graph->thickness) . "
				    l0,-$graph->thickness
				    A$graph->rx,$graph->ry 0 0,0 " . ($Data[$key]->start['x']) . "," . $Data[$key]->start['y'] . "
				    ' style='stroke:black;stroke-width: 0;fill:rgb(" . implode(',', $Data[$key]->color) . ");fill-opacity: 0.5;'/>";
				}

				/**
				 * Text notes
				 */
				//skip it if zero votes were made
				if(!$recEntry->voteCount) {
					continue;
				}

				$out .= "<line style='stroke:rgb(103, 103, 103);stroke-width: 1px;'
		      x1='" . $Data[$key]->tip_start['x'] . "' y1='" . $Data[$key]->tip_start['y'] . "'
		      x2='" . $Data[$key]->tip_end['x'] . "' y2='" . $Data[$key]->tip_start['y'] . "' />";

				if($Data[$key]->tip_start['x'] > $graph->cx) {
					$out .= "\n<text style='font:12px Arial;fill:rgb(103, 103, 103);' x='" . ($Data[$key]->tip_end['x'] + 2) . "' y='" . ($Data[$key]->tip_start['y'] + 6) . "'>" . $recEntry->answer . "</text>";
				}
				else {
					$out .= "\n<text style='font:12px Arial;text-anchor:end;fill:rgb(103, 103, 103);' x='" . ($Data[$key]->tip_end['x'] - 2) . "' y='" . ($Data[$key]->tip_start['y'] + 6) . "'>" . $recEntry->answer . "</text>";
				}


			}
		}

		$out .= "\n" . '</svg>';

		return $out;
	}

}