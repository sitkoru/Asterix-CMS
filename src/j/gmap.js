//Maps
function gmap_initializeMap(map_elemend_id,lat,lng,zoom,ui,put_zoom_to){if (GBrowserIsCompatible()){

	//Инициализация карты в указанном элементе
	var map = new GMap2(document.getElementById(map_elemend_id));
	//Центральная точка
	var center = new GLatLng(lat,lng);

	//Устанавливаем центр карты
	map.setCenter(center, zoom);
	
	//Показываем элементы управления
	if(ui){
		var customUI = map.getDefaultUI();
		customUI.zoom.scrollwheel = false;
		map.setUI(customUI);

//		map.setUIToDefault();
	}
	
	//Если нужно - по окончании движения будем передавать zoom в указанные поля
	if(put_zoom_to)GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
		$j('#'+put_zoom_to+'_z').val(newLevel);
	});

	//готово
	return map;
}}

function gmap_setMarker(map,lat,lng,image,drag,show_window,put_coords_to){
/*
	map : object
	lat/ong : point
	image : url
	drag : false/function_name
	show_window : false/html
*/

	//Настраиваем иконку
	var tinyIcon = new GIcon();
	tinyIcon.image = image;
	tinyIcon.shadow = "http://google-maps-icons.googlecode.com/files/shadow.png";
	tinyIcon.iconSize = new GSize(32, 37);
	tinyIcon.shadowSize = new GSize(32, 37);
	tinyIcon.iconAnchor = new GPoint(15, 35);
	tinyIcon.infoWindowAnchor = new GPoint(5, 1);

	markerOptions = { icon:tinyIcon, draggable: drag };
	
	//Центральная точка
	var center = new GLatLng(lat,lng);
	//Создаём маркер
	var marker = new GMarker(center, markerOptions);
	
	//Вешаем события
	if(drag){
		GEvent.addListener(marker, "dragstart", function(){map.closeInfoWindow();});
		//Если нужно - по окончании движения будем передавать координаты в указанные поля
		if(put_coords_to)GEvent.addListener(marker, "dragend", function(){
			var p=marker.getPoint();
			var x=p.lat();
			var y=p.lng();
			$j('#'+put_coords_to+'_x').val(x);
			$j('#'+put_coords_to+'_y').val(y);
		});
	}
	
	//Окно при клике
	if(show_window){
		//Опции открываемого окна
		html_window_options = { maxWidth:400 };
		//Открываем окно
		GEvent.addListener(marker, "click", function(){marker.openInfoWindowHtml(show_window, html_window_options);});
	}
	
	//Отображаем маркер
	map.addOverlay(marker);
}

//Говорим о том, что библиотека загружена
gmap_loaded=true;
