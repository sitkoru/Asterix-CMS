document.write('<link href="http://src.sitko.ru/c/stop6ie.css" rel="stylesheet" type="text/css" />');
(function($){
	$().ready( function() {
		$('.header').before('<div id="stop6ie"><div id="stop6ie-layout"></div><div id="stop6ie-con"><a class="close" href="#"></a><h2>Вы используете устаревшую версию браузера Internet Explorer</h2><p>Большинство современных сайтов, в том числе и наш, могут отображаться некорректно в текущей версии вашего браузера. Настоятельно рекомендуем установить последнюю стабильную версию браузера Internet Explorer либо другой современный браузер.</p><p>Новую версию Internet Explorer вы можете скачать <a href="http://www.microsoft.com/rus/windows/internet-explorer" class="out">здесь</a>. Также вы можете установить быстрые и удобные браузеры:</p><div class="browsers"><a href="http://google.ru/chrome" class="out chrome">Google Chrome</a><a href="http://www.mozilla.ru" class="out firefox">Mozilla Firefox</a><a href="http://ru.opera.com" class="out opera">Opera</a><a href="http://www.apple.com/safari/download/" class="out safari">Apple Safari</a></div></div></div>');
		var st = $('#stop6ie');
		st.parent().css('position','relative');
		l = ( st.parent().width() - st.width() ) / 2;
		st.css({left:l}).animate({top:100});
		$('#stop6ie a.close').click(function(){ $('#stop6ie').animate({top:-1000}); return false; });
	});
})(jQuery)
