	// ---------------------------------------------------------------
	// Copyright 2014, YouKaShi. All rights reserved.
	// Author: Joming
	// Email: hecity@163.com
	// ---------------------------------------------------------------
	
	$(function() { App.init(); })
	
	// 项目类
	App = {
		
		width: 0,
		hegiht: 0,
		maximized: false,

		init: function() {
			App.resize();
			$.ajaxSetup({ cache:false });
			$(window).resize(function(event) { App.resize(); });
			$(window).click(function(event) { App.click(); });
			$(window).scroll(function(event) { App.scroll(); });				
			$('button').button();
			
			// 左侧菜单
			$('.nav>ul').mouseenter(function() {
				var on = $(this).hasClass('on');
				if(on) return false;
				$e = $(this).find('li')
				var stime = setTimeout(function() { $e.slideDown(); }, 500);
				$('.nav>ul').mouseleave(function() {
					clearTimeout(stime);
					$e.slideUp('fast');
				});
			});
			
			// 中间菜单
			$('.by').find('a').click(function(){
				if($(this).attr('href')=="javascript:;" && !$(this).hasClass('on') && !$(this).hasClass('disabled')) {
					$('.by').find('a').removeClass('on');
					$(this).addClass('on');
					var show = $(this).attr('show');
					if(typeof(show)=="undefined") return false;					
					$('.page').hide();
					$(show).fadeIn();
				}
			});
			
			// 弹窗链接
			$('a.window').click(function(){
				var $this = $(this);
				var para = {};
				para.href = $this.attr('href');
				if($this.attr('title')!='') para.title = $this.attr('title');
				if($this.attr('width')!='') para.width = $this.attr('width');
				if($this.attr('height')!='') para.height = $this.attr('height');
				App.window(para);
				return false;
			});
					
		},		
		
		resize: function() {
			this.width = $(window).width();
			this.height = $(window).height();
		},
		
		scroll: function() {
		},
		
		click: function() {
		},
				
		chart: {
			chart:{ renderTo:'chart', defaultSeriesType:'line', marginTop:40 },
			credits:{ enabled:false },
			title:{ text:'' },
			xAxis:{ categories:[] },
			yAxis: { title:{ text:'' }, gridLineColor:'#f9f9f9' },
			legend:{ borderWidth:0 },
			plotOptions: { line:{ lineWidth:1, shadow:false, states:{ hover:{ lineWidth:1 } }, marker:{ enabled:true, radius:2 } }, series:{ } },
			series:[]	
		},
		
		refer: function(url) {
			if(typeof(url)!='undefined' && url!='') location.href = url
			else location.reload();
		},
		
		back: function() {
			history.back();
		},

		getcookie: function(name){
			var cs = document.cookie.split("; ");
			for(var i=0;i<cs.length;i++) {
				var data = cs[i].split("=");
				if(data[0]==name) return decodeURIComponent(data[1]).replace(/\+/g, ' ');
			}
			return false;
		},
		
		setcookie: function(name, value, expire) {
			if(typeof(expire)=='undefined') expire = 240;	// 默认时长十天
			var date = new Date();
			var string = name +'='+ encodeURIComponent(value);
			date.setTime(date.getTime() + expire*1000*3600);
			string = string + ";path=/;expires=" + date.toGMTString();
			document.cookie = string;
		},
		
		// 页内窗口
		window: function(attr) {
			if(!$("#window").length) $("body").prepend('<div id="window" class="window hidden"></div>')
			var para = {	// 设定窗口默认值
				width: 600,
				height: 400,
				modal: true,
				resizable: false,
				open: function(event, ui) {
					if(typeof(attr.iframe)!='undefined') { $(this).html('<iframe width="100%" height="100%" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" src="'+attr.iframe+'"></iframe>'); }
					else if(typeof(attr.text)!='undefined') $(this).text(attr.text);
					else if(typeof(attr.html)!='undefined') $(this).html(attr.html);
					else if(typeof(attr.href)!='undefined') $(this).load(attr.href);
				},
				close: function(event, ui) { $('#window').empty(); }
			};
			for(var i in attr) para[i] = attr[i];
			$("#window").dialog(para);
			$window = $('#window');
		},
		
		alert: function(str) {
			if(!$("#alert").length) $("body").append('<div id="alert" class="alert radius shadow"></div>')
			$('#alert').html(str);
			var w = $(window).width();
			var h = $(window).height();
			var sl = $(document).scrollLeft();
			var st = $(document).scrollTop();
			var iw = $('#alert').width();
			var ih = $('#alert').height();
			var l = (w-iw)*0.5+sl;
			var t = (h-ih)*0.5+st;
			$('#alert').css('left', l).css('top', t).show();
			if(typeof(timer_alert)!='undefined') clearTimeout(timer_alert);
			timer_alert = setTimeout(function(){ $('#alert').fadeOut('slow'); }, 2000);
		},		
		
		time: function(timestamp, para) {
			
			var date = new Date(1000*timestamp);
			var y = date.getFullYear();
			var m = date.getMonth()+1;	if(m<10) m = '0'+m;
			var d = date.getDate();			if(d<10) d = '0'+d;
			var h = date.getHours();		if(h<10) h = '0'+h;
			var i = date.getMinutes();	if(i<10) i = '0'+i;
			var s = date.getSeconds();	if(s<10) s = '0'+s;
			var para = typeof(para)=='undefined'?'':para;
			switch(para) {
				case 'y-m-d h:i:s': var ret = y+'-'+m+'-'+d+' '+h+':'+i+':'+s; break;
				case 'y-m-d h:i': var ret = y+'-'+m+'-'+d+' '+h+':'+i; break;
				case 'y-m-d': var ret = y+'-'+m+'-'+d; break;
				default: var ret = y+''+m+''+d;	break;
			}
			return ret;			
				
		}
		
	
	};
	
	// 扩展函数
	$.fn.extend({
		
		water: function(water) {
			var $e = $(this);
			if($e.val()=='') $e.val(water).css('color', '#ccc');
			else $e.css('color', '#333');
			$e.click(function() { if($e.val()==water) $e.val('').css('color', '#333'); })
			$e.focusout(function() { if($e.val()=='') $e.val(water).css('color', '#ccc'); });
			$e.parents('form').submit(function(){ if($e.val()==water) $e.val(''); })
		},
		
		autodate: function(para) {
			$(this).attr('readonly', true);
			var ps = {
				dateFormat: 'yy-mm-dd',
				maxDate: '+0',
				showMonthAfterYear: true,
				changeYear: true,
				monthNames: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
				dayNamesMin: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
			};
			if(typeof(para)!="undefined") for(var i in para) ps[i] = para[i];
			$(this).datepicker(ps);
		},
		
		automonth: function(para) {
			var d = new Date();
			var y = d.getFullYear();
			var m = d.getMonth()+1;
			var month = y+''+(m<10?'0':'')+m;
			var para = typeof(para)=='undefined' ? {} : para;
			var maxDate = typeof(para.maxDate)=='undefined' ? month : para.maxDate;
			$(this).addClass('spinner');
			$(this).click(function(){ $(this).select(); });
			$(this).focusout(function(){
				var val = $(this).val();
				var reg = /(^[0-9]{6}$)/gi;
				if(!reg.test(val)) $(this).val('');
			});
			$(this).spinner({
				start: function( event, ui ) {
					if( $.trim($(this).val())!='' ) return;
					$(this).val(month);
				},
				spin: function(event, ui) {					
					var date = String(ui.value);
					var y = parseInt( date.substr(0,4) );
					var m = parseInt( date.substr(4,5) );
					if(m==0) date = (y-1)+'12';
					if(m>12) date = (y+1)+'01';
					if(date>maxDate) return false;
					$(this).spinner('value', date);
					return false;
				}
			});
		}
					
	})

	
	Number.prototype.formatMoney = function (places, symbol, thousand, decimal) {
		var places = !isNaN(places = Math.abs(places)) ? places : 2;
		var symbol = symbol !== undefined ? symbol : "KSh";
		var thousand = thousand || ",";
		var decimal = decimal || ".";
		var number = this, negative = number < 0 ? "-" : "", 
		i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
		j = (j = i.length) > 3 ? j % 3 : 0;
		var ret = symbol +' '+ negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
		return ret;
	};
