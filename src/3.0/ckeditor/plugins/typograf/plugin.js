CKEDITOR.plugins.add( 'typograf', {
	requires: ['iframedialog'],

	init:function(ed) {
		var pluginName = 'typograf';
		var cmd = ed.addCommand(pluginName, {exec:typograf_exec});

		var me = this;
		var editor = ed;
		var iframeWindow = null;
		CKEDITOR.dialog.add( 'typograf_dialog', function() {
			return {
				title: 'Типограф',
				minWidth: 800,
				minHeight: 535,
				contents:
						[
							{
								id: 'typograf_iframe',
								label: 'Результат',
								expand: true,
								elements:
										[
											{
												type: 'iframe',
												src: '/admin/typograf.php',
												width: '100%',
												height: '100%',
												onContentLoad: function() {

													if ( iframeWindow == null ) {

														var iframe = document.getElementById( this._.frameId );
														iframeWindow = iframe.contentDocument;
														iframeWindow.getElementById('typograf-dialog-area').value = editor.getData();
														iframeWindow.getElementById('typograf-dialog-form').submit();
													}

													else {

														var iframe = document.getElementById( this._.frameId );
														iframeWindow = iframe.contentDocument;
													}
												}
											}
										]
							}
						],
				onOk: function() {
					this._.editor.setData(iframeWindow.getElementById('typograf-dialog-area').value);
					iframeWindow = null;
				},
				onCancel: function() { iframeWindow = null; }
			};
		});

		cmd.modes = {wysiwyg:1,source:1};
		cmd.canUndo = true;
		ed.ui.addButton( pluginName, {label:'Типограф', command:pluginName, icon:this.path+'images/typograf.png'} );
	}
})

function typograf_exec( ed ) {

	ed.openDialog( 'typograf_dialog' );
}
