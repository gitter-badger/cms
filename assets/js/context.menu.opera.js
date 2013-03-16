//Handling context menu
//Support for opera

$(document).ready(function() {
(function(){

	if ( 'oncontextmenu' in document.createElement('foo') ){
		//contextmenu supported - nothing to do
		return;
	}
	function dispatchCtxMenuEvent(e,evType){
		var doc = e.target.ownerDocument||(e.view?e.view.document:null)||e.target;
		var newEv = doc.createEvent('MouseEvent');
		newEv.initMouseEvent(evType||'contextmenu', true, true, doc.defaultView, e.detail,
		e.screenX, e.screenY, e.clientX, e.clientY, e.ctrlKey, e.altKey,
		e.shiftKey, e.metaKey, e.button, e.relatedTarget);
		newEv.synthetic = true;
		e.target.dispatchEvent(newEv);
	};


	//contextmenu must be fired on mousedown if we want to cancel the menu
	addEventListener('mousedown',function(e){
		//right-click doesn't fire click event. Only mouseup
		if( e && e.button == 2 ){
			cancelMenu(e);
			return false;
		}
	},true);

	var overrideButton;
	function cancelMenu(e){
		if(!overrideButton){
			var doc = e.target.ownerDocument;
			overrideButton = doc.createElement('input');
			overrideButton.type='button';
			(doc.body||doc.documentElement).appendChild(overrideButton);
		}
		overrideButton.style='position:absolute;top:'+(e.clientY-2)+'px;left:'+(e.clientX-2)+'px;width:5px;height:5px;opacity:0.01';
	}

	addEventListener('mouseup',function(e){
		if(overrideButton){
			overrideButton.parentNode.removeChild(overrideButton);
			overrideButton = undefined;
		}
		if( e && e.button == 2 ){
			dispatchCtxMenuEvent(e, 'contextmenu');
			return false;
		}
	},true);

})( true, 1000 );
});
