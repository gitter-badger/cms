/*
 * jQuery SimpleTree Drag&Drop plugin
 * Update on 22th May 2008
 * Version 0.3x
 *
 * Licensed under BSD <http://en.wikipedia.org/wiki/BSD_License>
 * Copyright (c) 2009, Peter Panov <panov@elcat.kg>, IKEEN Group http://www.ikeen.com
 * Heavily modified by Artjom Kurapov <artkurapov@gmail.com>
 * All rights reserved.
 *
 */

$(document).ready(function() {
    $.fn.simpleTree = function simpleTree(opt) {
        var TREE = this;
        var ROOT = $('#mainmenu');
        ROOT = ROOT[0];
        var mousePressed = false;
        var mouseMoved = false;
        var dragMoveType = false;
        var dragNode_destination = false;
        var dragNode_source = false;
        var dragDropTimer = false;
        var ajaxCache = Array();
        var tapHoldUsed = false;

        TREE.arrQueue = Array();
        TREE.intQueueIndex = 0;

        TREE.option = {
            drag:        true,
            animate:    false,
            autoclose:    false,
            speed:        'fast',
            afterAjax:    false,
            afterMove:    false,
            afterClick:    false,
            afterDblClick:    false,
            // added by Erik Dohmen (2BinBusiness.nl) to make context menu cliks available
            afterContextMenu:    false,
            // added by Artjom Kurapov
            urlReadNodes: '/',
            urlAnchorBase: '/',

            docToFolderConvert:true
        };

        TREE.option = $.extend(TREE.option, opt);
        
        $.extend(this, {getSelected: function() {
            return $('span.active', this).parent();
        }});

        TREE.editDeepChild = function(ID) {
            var nID = "" + ID;
            nID = nID.replace('node', '');
            jQuery.getJSON(sys_url + 'content/menu/menu_parents/?langID=' + Content.langID + '&ID=' + nID + '',
                function(arrItems) {
                    TREE.arrQueue = arrItems;
                    TREE.intQueueIndex = 0;
                    //console.log(arrItems);

                    if ($('#node' + arrItems[0])[0]) {
                        TREE.nodeToggle($('#node' + arrItems[0])[0]);
                    }
                });
        };

        TREE.init = function(obj) {
            TREE.setTreeNodes(obj, false);
            TREE.nodeToggle($('#node1', obj)[0]);
        };

        TREE.setTreeNodes = function(obj, useParent) {
            obj = useParent ? obj.parent() : obj;

            $('ul li>span', obj).addClass('text')
                .bind('selectstart',
                function() {
                    return false;
                })
                .bind("touchend",
                    function(e) {
                        $('.active', TREE).attr('class', 'text');
                        if (this.className == 'text') {
                            this.className = 'active';
                        }
                        if (typeof TREE.option.afterContextMenu == 'function') {
                            TREE.option.afterContextMenu(e, $(this).parent());
                        }

                        TREE.tapHoldUsed = true;
                        return false;
                    })
                .click(
                function() {
                    if(TREE.tapHoldUsed) {
                        TREE.tapHoldUsed=false;
                        return false;
                    }
                    window.location = TREE.option.urlAnchorBase + '#page/' + $(this).parents().get(0).id.replace('node', '');
                    Content.parentID = $(this).parents().get(0).id;
                    $('.active', TREE).attr('class', 'text');
                    if (this.className == 'text') {
                        this.className = 'active';
                    }

                    if (typeof TREE.option.afterClick == 'function') {
                        TREE.option.afterClick($(this).parent());
                    }
                    return false;
                }).dblclick(
                function() {
                    mousePressed = false;
                    TREE.nodeToggle($(this).parent().get(0));
                    if (typeof TREE.option.afterDblClick == 'function') {
                        TREE.option.afterDblClick($(this).parent());
                    }
                    return false;
                    // added by Erik Dohmen (2BinBusiness.nl) to make context menu actions
                    // available
                })
                .bind("contextmenu",
                function(e) {
                    $('.active', TREE).attr('class', 'text');
                    if (this.className == 'text') {
                        this.className = 'active';
                    }
                    if (typeof TREE.option.afterContextMenu == 'function') {
                        TREE.option.afterContextMenu(e, $(this).parent());
                    }
                    return false;
                }).mousedown(
                function(event) {
                    mousePressed = true;
                    cloneNode = $(this).parent().clone();
                    var LI = $(this).parent();
                    if (TREE.option.drag) {
                        $('>ul', cloneNode).hide();
                        $('body').append('<div id="drag_container"><ul></ul></div>');
                        $('#drag_container').hide().css({opacity:'0.8'});
                        $('#drag_container >ul').append(cloneNode);
                        $("<img>").attr({id    : "tree_plus",src    : "img/cms/icons/add.png"}).css({width: "7px",display: "block",position: "absolute",left    : "5px",top: "5px", display:'none'}).appendTo("body");
                        $(document).bind("mousemove", {LI:LI}, TREE.dragStart).bind("mouseup", TREE.dragEnd);
                    }
                    return false;
                }).mouseup(function() {
                    ddest = $(this).parent();
                    if (mousePressed && mouseMoved && dragNode_source && $(ddest).attr('id') != $(dragNode_source).attr('id')) {
                        TREE.moveNodeToFolder($(this).parent());
                    }
                    TREE.eventDestroy();
                });

            $('li', obj).each(
                function(i) {
                    var className = this.className;
                    var open = false;
                    var cloneNode = false;
                    var LI = this;
                    var childNode = $('>ul', this);
                    if (childNode.size() > 0) {
                        var setClassName = 'folder-';
                        if (className && className.indexOf('open') >= 0) {
                            setClassName = setClassName + 'open';
                            open = true;
                        } else {
                            setClassName = setClassName + 'close';
                        }
                        this.className += ' ' + setClassName + ($(this).is(':last-child') ? '-last' : '');

                        if (!open || className.indexOf('ajax') >= 0)childNode.hide();

                        //TREE.setTrigger(this);
                    } else {
                        var setClassName = ' doc';
                        this.className += setClassName + ($(this).is(':last-child') ? '-last' : '');
                    }

                    TREE.setTrigger(this);

                }).before('<li class="line">&nbsp;</li>')
                .filter(':last-child').after('<li class="line-last"></li>');
            TREE.setEventLine($('.line, .line-last', obj));
        };

        TREE.closeNearby = function(obj) {
            $(obj).siblings().filter('.folder-open, .folder-open-last').each(function() {
                var childUl = $('>ul', this);
                var className = this.className;
                this.className = className.replace('open', 'close');
                if (TREE.option.animate) {
                    childUl.animate({height:"toggle"}, TREE.option.speed);
                } else {
                    childUl.hide();
                }
            });
        };

        TREE.getParentID = function(ID) {
            return $('#node' + ID).parent().parent().attr('id').replace('node', '');
        }

        TREE.nodeToggle = function(obj, clearCache) {

            var childUl = $('>ul', obj);

            if (1 * obj.id.replace('node', '') == TREE.arrQueue[TREE.intQueueIndex]) {
                obj.className = obj.className.replace('close', 'open');
                childUl.show();
                if (TREE.option.autoclose)TREE.closeNearby(obj);
                //if(childUl.is('.ajax'))
                TREE.setAjaxNodes(childUl, obj.id);
            }

            else {
                if (childUl.is(':visible')) {
                    obj.className = obj.className.replace('open', 'close');

                    if (TREE.option.animate) {
                        childUl.animate({height:"toggle"}, TREE.option.speed);
                    } else {
                        childUl.hide();
                    }
                } else {
                    obj.className = obj.className.replace('close', 'open');
                    if (TREE.option.animate) {
                        childUl.animate({height:"toggle"}, TREE.option.speed, function() {
                            if (TREE.option.autoclose)TREE.closeNearby(obj);
                            if (childUl.is('.ajax')) {
                                if (clearCache) {
                                    ajaxCache[$.inArray(obj.id, ajaxCache)] = null; // clean cache for node movement
                                }

                                TREE.setAjaxNodes(childUl, obj.id);
                            }
                        });
                    } else {
                        childUl.show();
                        if (TREE.option.autoclose)TREE.closeNearby(obj);
                        if (childUl.is('.ajax'))TREE.setAjaxNodes(childUl, obj.id);
                    }
                }
            }
        };

        TREE.Load = function(ID) {
            var obj = $('#node' + ID, ROOT)[0];

            obj.className = obj.className.replace('close', 'open');

            var childUl = $('>ul', obj);
            childUl.html('');
            childUl.show();

            if (TREE.option.autoclose) {
                TREE.closeNearby(obj);
            }
            ajaxCache[$.inArray(ID, ajaxCache)] = null;
            TREE.setAjaxNodes(childUl, ID);
        };

        //added by Artjom Kurapov
        TREE.draw = function(ID, item) {
            var strNode = '';
            strNode += "<li id='node" + item.ID + "' rel='" + item.module + "'>";
            //class='icon_"+item.module+"'
            if (item.title.length < 1) {
                item.title = '(' + t('empty') + ')';
            }
            strNode += "<span>" + item.title + "</span>";
            if (item.children.length > 0) {
                strNode += '<ul class="ajax"></ul>';
            }

            strNode += '</li>';

            return strNode;
        };

        TREE.setAjaxNodes = function(node, parentId, callback) {
            if ($.inArray(parentId, ajaxCache) == -1) {
                ajaxCache[ajaxCache.length] = parentId;
                var url = TREE.option.urlReadNodes + '&ID=' + (parentId + "").replace('node', '') + '&langID=' + Content.langID;//$.trim($('>li', node).text());

                $.getJSON(
                    url,
                    function(arrItems) {
                        var strElement = '';
                        var item;

                        for (var i = 0; i < arrItems.length; i++) {

                            item = arrItems[i];

                            strElement = strElement + MenuTree.draw(parentId, item);
                        }
                        ;

                        node.removeAttr('class');
                        node.html(strElement);
                        $.extend(node, {url:url});

                        TREE.setTreeNodes(node, true);
                        if (typeof TREE.option.afterAjax == 'function') {
                            TREE.option.afterAjax(node);
                        }
                        if (typeof callback == 'function') {
                            callback(node);
                        }

                        //console.log(arrItems);
                        //console.log(TREE.arrQueue);


                        if (TREE.arrQueue.length > 0) {
                            TREE.intQueueIndex++;
                            var tempID = TREE.arrQueue[TREE.intQueueIndex];

                            //console.log(tempID);
                            //console.log(parentId);
                            /*
                             if(parentId==tempID){

                             tempID=TREE.arrQueue[TREE.intQueueIndex];
                             }
                             */

                            if (TREE.intQueueIndex + 1 >= TREE.arrQueue.length) {
                                TREE.intQueueIndex = 0;
                                TREE.arrQueue = [];
                                if (tempID) {
                                    $('#node' + tempID + '>span').trigger('click');
                                }

                            }
                            else {
                                if (tempID > 0 && $('#node' + tempID)[0]) {
                                    TREE.nodeToggle($('#node' + tempID)[0]);
                                }
                            }
                        }
                    });
            }

            else {

                if (parentId == 'node1' && $("#" + parentId + " ul").html().length > 10) {
                    TREE.intQueueIndex++;
                    var tempID = TREE.arrQueue[TREE.intQueueIndex];

                    if (tempID > 0 && $('#node' + tempID)[0]) {
                        TREE.nodeToggle($('#node' + tempID)[0]);
                    }

                }
            }
        };

        TREE.addNode = function(id, text, callback) {
            var temp_node = $('<li><ul><li id="' + id + '"><span>' + text + '</span></li></ul></li>');
            TREE.setTreeNodes(temp_node);
            dragNode_destination = TREE.getSelected();
            dragNode_source = $('.doc-last', temp_node);
            TREE.moveNodeToFolder(dragNode_destination);
            temp_node.remove();
            if (typeof(callback) == 'function') {
                callback(dragNode_destination, dragNode_source);
            }
        };

        TREE.delNode = function(callback) {
            dragNode_source = TREE.getSelected();
            TREE.checkNodeIsLast(dragNode_source[0]);
            dragNode_source.prev().remove();
            dragNode_source.remove();
            if (typeof(callback) == 'function') {
                callback(dragNode_destination);
            }
        };

        TREE.setTrigger = function(node) {
            //console.log(node);
            $('>span', node).before('<img class="icon_' + $(node).attr('rel') + ' trigger" src="'+sys_url+'vendor/Gratheon/CMS/assets/img/s.gif" border=0 />');
            var trigger = $('>.trigger', node);
            trigger.click(function(event) {
                TREE.nodeToggle(node);
            });
            if (!$.browser.msie) {
                trigger.css('float', 'left');
            }
        };

        TREE.dragStart = function(event) {
            var LI = $(event.data.LI);
            if (mousePressed) {
                mouseMoved = true;
                if (dragDropTimer) clearTimeout(dragDropTimer);
                if ($('#drag_container:not(:visible)')) {
                    $('#drag_container').show();
                    LI.prev('.line').hide();
                    dragNode_source = LI;
                }
                $('#drag_container').css({position:'absolute', "left" : (event.pageX + 5), "top": (event.pageY + 15) });
                //if(LI.is(':visible'))LI.hide();

                var temp_move = false;
                if (event.target.tagName.toLowerCase() == 'span' && $.inArray(event.target.className, Array('text', 'active', 'trigger')) != -1) {
                    var parent = event.target.parentNode;
                    var offs = $(parent).offset({scroll:false});
                    var screenScroll = {x : (offs.left - 3),y : event.pageY - offs.top};
                    var isrc = $("#tree_plus").attr('src');
                    var ajaxChildSize = $('>ul.ajax', parent).size();
                    var ajaxChild = $('>ul.ajax', parent);
                    screenScroll.x += 19;
                    screenScroll.y = event.pageY - screenScroll.y + 5;

                    if (parent.className.indexOf('folder-close') >= 0 && ajaxChildSize == 0) {
                        if (isrc.indexOf('minus') != -1)$("#tree_plus").attr('src', 'img/cms/icons/add.png');
                        $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();
                        dragDropTimer = setTimeout(function() {
                            parent.className = parent.className.replace('close', 'open');
                            $('>ul', parent).show();
                        }, 700);
                    } else if (parent.className.indexOf('folder') >= 0 && ajaxChildSize == 0) {
                        if (isrc.indexOf('minus') != -1)$("#tree_plus").attr('src', 'img/cms/icons/add.png');
                        $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();
                    }
                    else if (parent.className.indexOf('folder-close') >= 0 && ajaxChildSize > 0) {
                        mouseMoved = true;

                        if (TREE.option.docToFolderConvert) {
                            $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();
                        } else {
                            $("#tree_plus").hide();
                        }
                        /*
                         mouseMoved = false;
                         //$("#tree_plus").attr('src','images/minus.gif');
                         $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();

                         $('>ul',parent).show();

                         TREE.setAjaxNodes(ajaxChild,parent.id, function(){
                         parent.className = parent.className.replace('close','open');
                         mouseMoved = true;
                         $("#tree_plus").attr('src','img/icons/add.png');
                         $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();
                         });
                         */

                    } else {
                        if (TREE.option.docToFolderConvert) {
                            $("#tree_plus").css({"left": screenScroll.x, "top": screenScroll.y}).show();
                        } else {
                            $("#tree_plus").hide();
                        }
                    }
                } else {
                    $("#tree_plus").hide();
                }
                return false;
            }
            return true;
        };

        TREE.dragEnd = function() {
            if (dragDropTimer) clearTimeout(dragDropTimer);
            TREE.eventDestroy();
        };

        TREE.setEventLine = function(obj) {
            obj.mouseover(
                function() {
                    if (this.className.indexOf('over') < 0 && mousePressed && mouseMoved) {
                        this.className = this.className.replace('line', 'line-over');
                    }
                }).mouseout(
                function() {
                    if (this.className.indexOf('over') >= 0) {
                        this.className = this.className.replace('-over', '');
                    }
                }).mouseup(function() {
                    if (mousePressed && dragNode_source && mouseMoved) {
                        dragNode_destination = $(this).parents('li:first');
                        TREE.moveNodeToLine(this);
                        TREE.eventDestroy();
                    }
                });
        };

        TREE.checkNodeIsLast = function(node) {
            if (node.className.indexOf('last') >= 0) {
                var prev_source = dragNode_source.prev().prev();
                if (prev_source.size() > 0) {
                    prev_source[0].className += '-last';
                }
                node.className = node.className.replace('-last', '');
            }
        };

        TREE.checkLineIsLast = function(line) {
            if (line.className.indexOf('last') >= 0) {
                var prev = $(line).prev();
                if (prev.size() > 0) {
                    prev[0].className = prev[0].className.replace('-last', '');
                }
                dragNode_source[0].className += '-last';
            }
        };

        TREE.eventDestroy = function() {
            // added by Erik Dohmen (2BinBusiness.nl), the unbind mousemove TREE.dragStart action
            // like this other mousemove actions binded through other actions ain't removed (use it myself
            // to determine location for context menu)
            $(document).unbind('mousemove', TREE.dragStart).unbind('mouseup').unbind('mousedown');
            $('#drag_container, #tree_plus').remove();
            if (dragNode_source) {
                $(dragNode_source).show().prev('.line').show();
            }
            dragNode_destination = dragNode_source = mousePressed = mouseMoved = false;
            //ajaxCache = Array();
        };

        TREE.convertToFolder = function(node) {
            node[0].className = node[0].className.replace('doc', 'folder-open');
            node.append('<ul><li class="line-last"></li></ul>');
            TREE.setTrigger(node[0]);
            TREE.setEventLine($('.line, .line-last', node));
        };

        TREE.convertToDoc = function(node) {
            $('>ul', node).remove();
            //$('img', node).remove();
            node[0].className = node[0].className.replace(/folder-(open|close)/gi, 'doc');
        };

        TREE.moveNodeToFolder = function(node) {

            if (!TREE.option.docToFolderConvert && node[0].className.indexOf('doc') != -1) {
                return true;
            }
            else// if(TREE.option.docToFolderConvert && node[0].className.indexOf('doc')!=-1)
            {
                TREE.convertToFolder(node);
            }

            TREE.checkNodeIsLast(dragNode_source[0]);

            var lastLine = $('>ul >.line-last', node);
            if (lastLine.size() > 0) {
                TREE.moveNodeToLine(lastLine[0]);
            }

            $('ul:last', node).remove()
            $(node).append('<ul class="ajax" style="display:none;"></ul>');
            //console.log(node);
            TREE.nodeToggle(node[0], true);

        };

        TREE.moveNodeToLine = function(node) {
            TREE.checkNodeIsLast(dragNode_source[0]);
            TREE.checkLineIsLast(node);
            var parent = $(dragNode_source).parents('li:first');
            var line = $(dragNode_source).prev('.line');
            $(node).before(dragNode_source);
            $(dragNode_source).before(line);
            node.className = node.className.replace('-over', '');
            var nodeSize = $('>ul >li', parent).not('.line, .line-last').filter(':visible').size();
            if (TREE.option.docToFolderConvert && nodeSize == 0) {
                TREE.convertToDoc(parent);
            } else if (nodeSize == 0) {
                parent[0].className = parent[0].className.replace('open', 'close');
                $('>ul', parent).hide();
            }

            // added by Erik Dohmen (2BinBusiness.nl) select node
            if ($('span:first', dragNode_source).attr('class') == 'text') {
                $('.active', TREE).attr('class', 'text');
                $('span:first', dragNode_source).attr('class', 'active');
            }

            if (typeof(TREE.option.afterMove) == 'function') {
                var pos = $(dragNode_source).prevAll(':not(.line)').size();
                TREE.option.afterMove($(node).parents('li:first'), $(dragNode_source), pos);
            }
        };

        TREE.init(ROOT);
        return TREE;
    }
});