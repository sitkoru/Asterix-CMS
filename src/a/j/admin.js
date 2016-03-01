var w_editor = 'ck';
var req;
var reqTimeout;

if ( w_editor == 'ck' ) {

	document.write('<script src="https://src.sitko.ru/a/ckeditor/ckeditor.js" type="text/javascript"></script>');
	document.write('<script src="https://src.sitko.ru/a/ckeditor/adapters/jquery.js" type="text/javascript"></script>');
}

else {

	document.write('<script src="https://src.sitko.ru/a/j/jquery.FCKeditor.pack.js" type="text/javascript"></script>');
	document.write('<script src="/t/adm/fckeditor/fckeditor.js" type="text/javascript"></script>');
}

var $j = jQuery.noConflict();

var init_ckeditor = function() {

	$j('textarea.html_editor').ckeditor({

		toolbar:
		[
			[
			'Source','-','FontSize','-','Bold','Italic','Underline','-',
			'Subscript','Superscript','SpecialChar','-',
			'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-',
			'NumberedList','BulletedList','-',
			'RemoveFormat','-',
			'Link','Unlink','Anchor','-',
			'Table','Flash','Image','-',
			],
		],
		language:'ru',
		removePlugins:'menu,menubutton,contextmenu',
		filebrowserUploadUrl:'/admin/upload.php'
	});
}

function ask(url,method){

	$j.ajax({
		type: method,
		dataType: "html",
		url: url,
		cache: false,
		success: function(html){
			$j("#bar_content").html(html);

//FCKeditor
			if ( w_editor == 'ck' ) {

				init_ckeditor();
			}
			else {

				$j.fck.config = {path: '/t/adm/fckeditor/'};
				$j('textarea.html_editor').fck({ toolbar:'Sitko', height:300 }	);
			}

			//Maps
			$j('#admin_map_canvas').each(function(){
				gmap_initializeMap('admin_map_canvas',55.161226,61.433507,12,true)
			});

			$j("#bar").fadeIn('fast');
		}
	});
}

function call_admin_interface(method,action,url){
	ask(url+'?method_marker='+method+'&action='+action,'GET',null);
}

function call_put_interface(data,url){
	ask(url,'PUT',data);
}

function getFormValues(form_name){
var result='';
result+='method_marker=PUT';
for(var i=0;i<form_name.length;i++)
	if(form_name[i].name)
	result+='&'+form_name[i].name+'='+form_name[i].value;
return result;
}


//Google Maps Initialize
function initialize() {
  if (GBrowserIsCompatible()) {
	var map = new GMap2(document.getElementById("map_canvas"));

	var x=$j('#map_canvas_x').val();
	var y=$j('#map_canvas_y').val();
	var z=$j('#map_canvas_z').val();

	var center = new GLatLng(x, y);
	map.setCenter(center, parseInt(z));

	var customUI = map.getDefaultUI();
	map.setUI(customUI);

	var marker = new GMarker(center, {draggable: true});

	GEvent.addListener(marker, "dragend", function() {
		var p=marker.getPoint();
		var x=p.lat();
		var y=p.lng();
		$j('#map_canvas_x').val(x);
		$j('#map_canvas_y').val(y);
	});

	GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
		$j('#map_canvas_z').val(newLevel);
	});

	map.addOverlay(marker);

  }
}

jQuery(document).ready(function(){

	$j('#bar .cancel').click(function(){$j('#bar').fadeOut('fast')});
/*
	$j('#admin_bar a').mouseover(function(){$j('#admin_bar').fadeTo("fast", 1)});
	$j('#admin_bar a').mouseout(function(){$j('#admin_bar').fadeTo("fast", 0.33)});
*/
	$j('*').keypress(function(e){
		if(e.altKey){
			if(e.which==69){
				alert('manage');
			}else{
				alert(e.which);
			}
		}
    });
});

