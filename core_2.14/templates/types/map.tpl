
	<label for="admin_map_canvas_{$field_sid}">{$field.title}:</label>
	
	<div id="admin_map_canvas_{$field_sid}" style="width: 600px; height: 300px; position:relative;"></div>
	<script type="text/javascript">
		function fid_134672689744697311565(ymaps) {
			
			// Создаём объект карты
			var map = new ymaps.Map(
				"admin_map_canvas_{$field_sid}", 
				{
					center: [{$field.value.x}, {$field.value.y}], 
					zoom: {$field.value.z}, 
					type: "{$field.value.type}"
				}
			);

			// Создаём коллекцию объектов
			myCollection = new ymaps.GeoObjectCollection({}, {
				preset: 'twirl#redIcon', //все метки красные
				draggable: true // и их можно перемещать
			});

			// Добавляем туда маркер
			myCollection.add(
				new ymaps.Placemark( [{$field.value.x}, {$field.value.y}] )
			);

			// Добавляем коллекцию на карту
			myMap.geoObjects.add(myCollection);

			// Добавляем контроллер действия "тащить"
			myCollection.getMap().events.add('drag', function() {
				alert('drag');
			});

			// Добавляем элементы управления
			map.controls
				.add("zoomControl")
				.add("mapTools")
				.add(new ymaps.control.TypeSelector(["yandex#map", "yandex#satellite", "yandex#hybrid", "yandex#publicMap"]));
		};
	</script>
	<script type="text/javascript" src="http://api-maps.yandex.ru/2.0/?coordorder=longlat&load=package.full&wizard=constructor&lang=ru-RU&onload=fid_134672689744697311565"></script>

	<input type="hidden" id="map_canvas_x" name="{$field.sid}[x]" value="{$field.value.x}" />
	<input type="hidden" id="map_canvas_y" name="{$field.sid}[y]" value="{$field.value.y}" />
	<input type="hidden" id="map_canvas_z" name="{$field.sid}[z]" value="{$field.value.z}" />
	<input type="hidden" id="map_canvas_type" name="{$field.sid}[type]" value="{$field.value.type}" />
	<script>
		var map=gmap_initializeMap('admin_map_canvas_{$field_sid}',{$field.value.x},{$field.value.y},{$field.value.z},true,'map_canvas');
		gmap_setMarker(map,{$field.value.x},{$field.value.y},'http://google-maps-icons.googlecode.com/files/music-rock.png',true,false,'map_canvas');
	</script>
