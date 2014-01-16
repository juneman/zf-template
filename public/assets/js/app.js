/**
 *
 * @auth: db, created at 2013.6.7
 *        www.cafread.com
 *  
 */
$(function () {
	$("ul.navigation-bar-top li").hover(
		function() {
			//$(this).find("a span").css("border-bottom"," 1px solid #0088cc");
			//$(this).find("a span.nav-text").css("color","#0088cc");
		},
		
		function() {
			var kz = $(this).attr("class");
			if (kz == "currently") 
			{return;}
			//$(this).find("a span").css("border-bottom","0px");
			//$(this).find("a span.nav-text").css("color","#777777");
		}
	);
});

function closeWnd()
{
	window.opener=null;
	window.open('','_self');
	window.close();
}

$(function () {
	 
	var url = $("input#hidden-article-url").val();
	var parentObj = $("div.completed-action-btns");
	var obj = $("button#not-need-to-read-btn");
	var obj2 = $("button#next-time-to-read-again-btn");
	var obj3 = $("button#rt-close-wnd-btn");
	obj.click(function(){
			if (obj3.css("display") != "none")
			{return false;}

			var data = {
			'url' : url, 
			'next' : '0',
			}

			$.post('/historyarticle/add', data, function(rsp){
				obj.attr("class", "btn disabled");	
				obj.html("已阅");
				obj2.remove();
				obj3.css("display", "inline-block");
			}, "json");
	});
	
	obj2.click(function(){
			if (obj3.css("display") != "none")
			{return false;}

			var data = {
			'url' : url, 
			'next' : '1',
			}

			$.post('/historyarticle/add', data, function(rsp){
				obj2.attr("class", "btn disabled");
				obj2.html("自动推荐系统会在适当时候再次推荐该文");
				obj.remove();
				obj3.css("display", "inline-block");
			}, "json");
	});
	
	obj3.click(function(){
		closeWnd();		
	});
});

// Reading Tool -SEARCH---
function createAfterAddNewWordTips(tips)
{
	var obj = $("#CAFR-ADD-NEW-WORD-TIPS");
	obj.css({
			'display' : 'none',
			'position' : 'absolute',
			'zindex' : '10000',
			});
	obj.html("<label class='info'>" + tips + "</label>");
	obj.fadeIn('slow', function(){
			obj.css("display", 'block');		
	});

	setTimeout(function(){obj.slideUp('slow')}, 1000);
}

function onAddNewWord() {
	  var word = $("#caf-bm-word").html();
		
		var data = {"word" : word, "host" : "caf-web"}

		$.post('/words/add/w', data, function(rsp){ 
			if (rsp.code == "ok")
			{
				createAfterAddNewWordTips("添加成功");
			}
			else
			{
				createAfterAddNewWordTips("添加失败");
			}
				
		},"json");
}

var CAFR = {};
CAFR.startObj = null;
CAFR.isdb   = false;
CAFR.ismove = false;
CAFR.allow  = true;

function getPointerX(event) {
	return event.pageX || (event.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft));
}

function getPointerY(event) {
	return event.pageY || (event.clientY + (document.documentElement.scrollTop || document.body.scrollTop));
}

CAFR.dblclick = function() {
	//GLS.isdb = true;
};
CAFR.mousemove = function() {
//	GLS.ismove = true;
};
CAFR.mousedown = function(evt) {
	evt = (evt) ? evt : ((window.event) ? window.event : "");
	if (evt) {
		CAFR.startObj = (evt.target) ? evt.target : evt.srcElement;
	}
	CAFR.ismove = false;
};
CAFR.mouseup = function(evt) {
	var obj;
	var strlen;
	evt = (evt) ? evt : ((window.event) ? window.event : "");
	if (evt) {
		obj = (evt.target) ? evt.target : evt.srcElement;
		strlen = window.getSelection ? window.getSelection().toString() : document.selection.createRange().text;
	}

	var str = "";
	if (obj.tagName != "A" && obj.tagName != "INPUT" && obj == CAFR.startObj && !CAFR.isdb && !CAFR.ismove && CAFR.allow) {
		if (strlen.length > 0) {
			str = strlen;
		}
	}
	if (str.toString().length > 0) {
		CAFR.show(str, evt);
	}
	else
	{
		CAFR.hide();
	}

	CAFR.isdb = false;
	CAFR.ismove = false;
};

CAFR.createMiniDictWnd = function(html, posx, posy){
	var obj = $("#CAFR-BOOKMARKLET");
	obj.css({
			'display' : 'block',
			'position' : 'absolute',
			'zindex' : '10000',
			});
	obj.css("left", posx);
	obj.css("top", posy);
	
	obj.html(html);
	
	var obj2= $("#CAFR-ADD-NEW-WORD-TIPS");
	obj2.css('left', posx - 10);
	obj2.css('top', posy - 45);
	obj2.css('display', 'none');
};

CAFR.hide = function()
{
	var obj = $("#CAFR-BOOKMARKLET");
	obj.css("display", "none");
}

CAFR.show = function(str, evt) {
	var posx = getPointerX(evt);
	var posy = getPointerY(evt);
	
	// search 
	$.get('/hfdict/pull/' + str , function(data) {
		//alert(data);
		if (data != '') {
			CAFR.createMiniDictWnd(data, posx, posy);
		}
	});

};

//页面加载
$(document).ready(function() {
		$(document.body).append("<div id='CAFR-BOOKMARKLET' style='display:none'></div>");
		$(document.body).append("<div id='CAFR-ADD-NEW-WORD-TIPS' style='display:none'></div>");
		$(document).mousedown(CAFR.mousedown).dblclick(CAFR.dblclick).mouseup(CAFR.mouseup).mousemove(CAFR.mousemove);
		});




/*
$(function() {
	$("div.content-area span").hover(
		function() {
			$(this).css("background-color","#f2f2f2");
			$(this).css("border-radius","4px");
			
		},
		
		function() {
			$(this).css("background-color","#ffffff");
		}
	);
	
	$("div.content-area p span").click(
		function(){
			var word = $(this).text();
			word = word.toLowerCase();
			
			$("div#rd-tool-side-dics").css("display", "block");
			$("div#rd-tool-side-dics #rd-tool-side-dics-wd").html(word);
			
		}
	);
	
	$("#rd-tool-side-close").click(
		function() {
			var obj = $("div#rd-tool-side-dics");
			obj.fadeOut("500", function(){
  				obj.css("display", "none");
  			});
		}
	);
});
*/


/** 
	For test -----
$(function() {
		$("#add-dict").click(function() {
			var data = {
			"word" : "two",
			"b_pr" : "[kad]",
			"a_pr" : "[ked]",
			"sm" : "n,一个\nv,大个",
			"synonym" : "word-s",
			"antonym" : "word-a",
			"usage" : "a word\n big word\nwords like",
			"tense_1" : "worded",
			"tense_2" : "",
			"tense_3" : "wording",
			"tense_4" : "",
			"tense_5" : "",
			}

			// Post
			$.post('/dict/add/one', data, function(response) {
					alert(response);
					$("#add-dict-result").html(response.code);

				});

		});
});
*/




/*
$(function () {
	$(".arch-section").hover(
		function() {
			//$(this).find("small.summary-statics-pane").css("display", "inline");
			
			var ob = $(this).find("small.summary-statics-pane");
			ob.fadeIn("500", function(){
  				ob.css("display", "inline");
  			});
		},
		function() {
			//$(this).find("small.summary-statics-pane").css("display", "none");
			var ob = $(this).find("small.summary-statics-pane");
			ob.fadeOut("500", function(){
  				ob.css("display", "none");
  			});
		}
	);
	
	$(".books-section").hover(
		function() {
			//$(this).find("small.book-summary-statics-pane").css("display", "inline");
			//$(this).find("p.book-action-btn-group").css("display", "inline");
			var ob = $(this).find("small.book-summary-statics-pane");
			ob.fadeIn("200", function(){
  				ob.css("display", "inline");
  			});
			
			var ob2 = $(this).find("p.book-action-btn-group");
			ob2.fadeIn("200", function(){
  				ob.css("display", "inline");
  			});
		},
		function() {
			//$(this).find("small.book-summary-statics-pane").css("display", "none");
			//$(this).find("p.book-action-btn-group").css("display", "none");
	
			var ob = $(this).find("small.book-summary-statics-pane");
			ob.fadeOut("200", function(){
  				ob.css("display", "none");
  			});
			
			var ob2 = $(this).find("p.book-action-btn-group");
			ob2.fadeOut("200", function(){
  				ob.css("display", "none");
  			});
		}
	);
});
*/
