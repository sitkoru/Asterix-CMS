/*
 ### jQuery CKEditor Plugin v0.31 - 2010-01-21 ###
 * http://www.fyneworks.com/ - diego@fyneworks.com
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 ###
 Project: http://jquery.com/plugins/project/CKEditor/
 Website: http://www.fyneworks.com/jquery/CKEditor/
*/
/*
 USAGE: $j('textarea').ckeditor({ path:'/a/ckeditor/' }); // initialize CKEditor
 ADVANCED USAGE: $j.ckeditor.update(); // update value in textareas of each CKEditor instance
*/

/*# AVOID COLLISIONS #*/
;if(window.jQuery) (function($j){
/*# AVOID COLLISIONS #*/

$j.extend($j, {
 ckeditor:{
  waitFor: 10,// in seconds, how long should we wait for the script to load?
  config: { }, // default configuration
  path: '/ckeditor/', // default path to CKEditor directory
  selector: 'textarea.ckeditor', // jQuery selector for automatic replacements
		editors: [], // array of element ids pointing to CKEditor instances
  loaded: false, // flag indicating whether CKEditor script is loaded
  intercepted: null, // variable to store intercepted method(s)
  
  // utility method to load instance of CKEditor
  instance: function(i){
			var x = CKEDITOR.instances[i];
			//console.log(['ckeditor.instance','x',x]);
			// Look for textare with matching name for backward compatibility
			if(!x){
				x = $j('#'+i.replace(/\./gi,'\\\.')+'')[0];
				//console.log(['ckeditor.instance','ele',x]);
				if(x) x = CKEDITOR.instances[x.id];
			};
			//console.log(['ckeditor.instance',i,x]);
			return x;
		},
		
  // utility method to read contents of CKEditor
  content: function(i, v){
			//console.log(['ckeditor.content',arguments]);
			var x = this.instance(i);
			if(!x){
				alert('CKEditor instance "'+i+'" could not be found!');
				return '';
			};
			if(v!=undefined){
 			//console.log(['ckeditor.content',x,'x.setData',v]);
				x.setData(v);
			};
			//console.log(['ckeditor.content','getData',x.getData(true)]);
   return x.getData(true);
  }, // ckeditor.content function
  
  // inspired by Sebastián Barrozo <sbarrozo@b-soft.com.ar>
  setHTML: function(i, v){
			//console.log(['ckeditor.setHTML',arguments]);
   if(typeof i=='object'){
    v = i.html;
    i = i.name || i.instance;
   };
   return $j.ckeditor.content(i, v);
  },
  
  // utility method to update textarea contents before ajax submission
  update: function(){
			// Remove old non-existing editors from memory
			$j.ckeditor.clean();
			// loop through editors
			//console.log(['ckeditor.update','before',$j.ckeditor.editors/*,CKEDITOR.instances*/]);
			//$j.each($j.ckeditor.editors,function(i,name){
			for(var i=0;i<$j.ckeditor.editors.length;i++){
				var name = $j.ckeditor.editors[i];
				//console.log(['ckeditor.update',name,CKEDITOR.instances[name]]);
				var area = $j('#'+name.replace(/\./g,'\\.'));
				if(area.length>0){
 				var data = this.content(name);
  			//console.log(['ckeditor.update','-->',area,data.length]);
	 			area.val( data ).text( data );
				};
   //});
			};
			//console.log(['ckeditor.update','done',$j.ckeditor.editors/*,CKEDITOR.instances*/]);
  }, // ckeditor.update
  
  // utility method to clear orphan instances from memory
  clean: function(){
			if(!window.CKEDITOR) return;
			//console.log(['ckeditor.clean','before',$j.ckeditor.editors]);
			//console.log(['ckeditor.clean','before(B)',CKEDITOR.instances]);
			for(var i=0;i<$j.ckeditor.editors.length;i++){
				var name = $j.ckeditor.editors[i];
 			//console.log(['ckeditor.clean',name,CKEDITOR.instances[name]]);
				var area = $j('#'+name.replace(/\./g,'\\.'));
 			//console.log(['ckeditor.clean',name,'textarea:',area]);
 			//console.log(['ckeditor.clean',name,'CKEDITOR:',CKEDITOR.instances[name]]);
 			//console.log(['ckeditor.clean',name,'CKEDITOR.textarea:',CKEDITOR.instances[name]?CKEDITOR.instances[name].textarea:null]);
				var inst = CKEDITOR.instances[name];
				if(area.length==0 || !inst || inst.textarea!=area[0]){
					//console.log(['ckeditor.clean',name,'DOES NOT EXIST']);
					//console.log(['ckeditor.clean',name,'editors.splice('+i+')']);
				 $j.ckeditor.editors.splice(i);
					////console.log(['ckeditor.clean',name,'delete CKEDITOR.instances['+name+']']);
				 delete CKEDITOR.instances[name];
					////console.log(['ckeditor.clean',name,'CKEDITOR.instances['+name+'].destroy()']);
					//inst.destroy();
				};
   //});
			};
			//console.log(['ckeditor.clean','after',$j.ckeditor.editors]);
			//console.log(['ckeditor.clean','after(B)',CKEDITOR.instances]);
  }, // ckeditor.clean
  
  // utility method to create instances of CKEditor (if any)
  create: function(options){
			// Create a new options object
   var o = $j.extend({}/* new object */, $j.ckeditor.config || {}, options || {});
   // Normalize plugin options
   $j.extend(o, {
    selector: o.selector || $j.ckeditor.selector,
    basePath: o.path || o.basePath || (window.CKEDITOR_BASEPATH ? CKEDITOR_BASEPATH : $j.ckeditor.path)
   });
			//console.log(['ckeditor.create','o',o]);
   // Find ckeditor.editor-instance 'wannabes'
   var e = o.e ? $j(o.e) : undefined;
   if(!e || !e.length>0) e = $j(o.selector);
			//console.log(['ckeditor.create','e',e]);
   if(!e || !e.length>0) return;
			//console.log(['ckeditor.create','loaded?',$j.ckeditor.loaded]);
			//console.log(['ckeditor.create','loading?',$j.ckeditor.loading]);
   // Load script and create instances
   if(!$j.ckeditor.loading && !$j.ckeditor.loaded){
 			//console.log(['ckeditor.create','load script']);
    $j.ckeditor.loading = true;
    $j.getScript(
     o.basePath+'CKEditor.js',
     function(){ $j.ckeditor.loaded = true; }
    );
   };
   // Start editor
   var start = function(){//e){
				//console.log(['ckeditor.create','start','loaded?',$j.ckeditor.loaded]);
    if($j.ckeditor.loaded){
     //console.log(['ckeditor.create','start','loaded!',e,o]);
     $j.ckeditor.editor(e,o);
    }
    else{
     //console.log(['ckeditor.create','start','waiting:',e,o]);
     if($j.ckeditor.waited<=0){
      alert('jQuery.CKEditor plugin error: The CKEditor script did not load.');
     }
     else{
      $j.ckeditor.waitFor--;
      window.setTimeout(start,1000);
     };
    }
   };
   start(e);
   // Return matched elements...
   return e;
  },
  
  // utility method to integrate this plugin with others...
  intercept: function(){
   if($j.ckeditor.intercepted) return;
   // This method intercepts other known methods which
   // require up-to-date code from CKEditor
   $j.ckeditor.intercepted = {
    ajaxSubmit: $j.fn.ajaxSubmit || function(){}
   };
   $j.fn.ajaxSubmit = function(){
				//console.log(['ckeditor.intercepted','$j.fn.ajaxSubmit',CKEDITOR.instances]);
    $j.ckeditor.update(); // update html
    return $j.ckeditor.intercepted.ajaxSubmit.apply( this, arguments );
   };
			// Also attach to conventional form submission
			//$j('form').submit(function(){
   // $j.ckeditor.update(); // update html
   //});
  },
  
  // utility method to create an instance of CKEditor
  editor: function(e /* elements */, o /* options */){
   // Create a local over-loaded copy of the default configuration
			o = $j.extend({}, $j.ckeditor.config || {}, o || {});
   // Make sure we have a jQuery object
   e = $j(e);
   //console.log(['ckeditor.editor','E',e,'o',o]);
   if(e.size()>0){
    // Go through objects and initialize ckeditor.editor
    e.each(
     function(i,t){
      //console.log(['ckeditor.editor','each','t',i,t]);
						if((t.tagName||'').toLowerCase()!='textarea')
							return alert(['An invalid parameter has been passed to the $j.CKEditor.editor function','tagName:'+t.tagName,'name:'+t.name,'id:'+t.id].join('\n'));
      
      //console.log(['ckeditor.editor','each','t.ckeditor',t.ckeditor]);
      var T = $j(t);// t = element, T = jQuery
      if(!t.ckeditor/* not already installed */){
							// make sure the element has an id
							t.id = t.id || 'ckeditor'+($j.ckeditor.editors.length+1);
							$j.ckeditor.editors[$j.ckeditor.editors.length] = t.id;
							// make sure the element has a name
							t.name = t.name || t.id;
       //console.log(['ckeditor.editor','metadata',T.metadata()]);
							// Accept settings from metadata plugin
							var config = $j.extend({}, o,
								($j.meta ? T.data()/*NEW metadata plugin*/ :
								($j.metadata ? T.metadata()/*OLD metadata plugin*/ : 
								null/*metadata plugin not available*/)) || {}
							);
							// normalize configuration one last time...
							config = $j.extend(config, {
								width: (o.width || o.Width || T.width() || '100%'),
								height: (o.height || o.Height || T.height() || '500px'),
								basePath: (o.path || o.basePath),
								toolbar: (o.toolbar || o.ToolbarSet || undefined)// 'Default')
							});
       //console.log(['ckeditor.editor','make','t',t]);
       //console.log(['ckeditor.editor','make','t.id',t.id]);
       //console.log(['ckeditor.editor','make','config',config]);
							// create CKEditor instance
       var editor = CKEDITOR.replace(t.id, config);
							// Store reference to element in CKEditor object
       editor.textarea = t;
							// Store reference to CKEditor object in element
       t.ckeditor = editor;
							// Mark this editor so we know if a new editor
							// with the same id has taken its place
       T.addClass('is-ckeditor');
      };
     }
    );
				// Remove old non-existing editors from memory
				$j.ckeditor.clean();
   };
   // return jQuery array of elements
   return e;
  }, // ckeditor.editor function
  
  // start-up method
  start: function(o/* options */){
			// Drop dead instances
			//console.log(['ckeditor.start','clean']);
			$j.ckeditor.clean();
   // Attach itself to known plugins...
			//console.log(['ckeditor.start','intercept']);
			$j.ckeditor.intercept();
			// Create CKEDITOR
   return $j.ckeditor.create(o);
  } // ckeditor.start
  
 } // ckeditor object
 //##############################
 
});
// extend $j
//##############################


$j.extend($j.fn, {
 ckeditor: function(o){
		//console.log(['ckeditor',this]);
		
		// Provide quick access to CKEditor Instance Object
		if(this.length==1 && this[0].id && window.CKEDITOR){
   var e = CKEDITOR.instances[this[0].id];
			if(e==this[0]){
 		 //console.log(['ckeditor','already exists:',CKEDITOR.instances[this[0].id]]);
 			return CKEDITOR.instances[this[0].id];
			}
			else{
 		 //console.log(['ckeditor','edit not created for:',this[0]]);
			 $j.ckeditor.clean();
			};
		};
		
		//console.log(['ckeditor','make editors for:',this]);
		
		// Let's make some editors! :-)
		return $j(this).each(function(){
			//console.log(['ckeditor','each','t',this]);
   $j.ckeditor.start(
				$j.extend(
					{}, // create a new options object
					o || {}, // overload with this call's options parameter
					{e: this} // store reference to self
				) // $j.extend
			); // $j.ckeditor.start
  }); // each element
		
		//console.log(['ckeditor','done','editors:',$j.editor.editors]);
		
 } //$j.fn.ckeditor
});
// extend $j.fn
//##############################

/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/
