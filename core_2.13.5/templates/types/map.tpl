	<label for="id_{$field_sid}">{$field.title}:</label>
	<div id="admin_map_canvas_{$field_sid}" style="width: 600px; height: 300px; position:relative; "></div>
	<input type="hidden" id="map_canvas_x" name="{$field.sid}[x]" value="{$field.value.x}" />
	<input type="hidden" id="map_canvas_y" name="{$field.sid}[y]" value="{$field.value.y}" />
	<input type="hidden" id="map_canvas_z" name="{$field.sid}[z]" value="{$field.value.z}" />
	<script>
		var map=gmap_initializeMap('admin_map_canvas_{$field_sid}',{$field.value.x},{$field.value.y},{$field.value.z},true,'map_canvas');
		gmap_setMarker(map,{$field.value.x},{$field.value.y},'http://google-maps-icons.googlecode.com/files/music-rock.png',true,false,'map_canvas');
	</script>
