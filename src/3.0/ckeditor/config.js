/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbar = [
        'Source', '-', 'FontSize', 'Format', '-', 'Bold', 'Italic', 'Underline', '-',
        'Subscript', 'Superscript', 'SpecialChar', '-',
        'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull', '-',
        'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-',
        'Link', 'Unlink', 'Anchor', '-',
        'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField', '-',
        'NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'BidiLtr', 'BidiRtl', '-',
        'TextColor', 'BGColor', '-',
        'UIColor', 'Maximize', 'ShowBlocks', '-',
        'Table', 'Flash', 'Image', '-', 'PasteFromWord', 'RemoveFormat', 'Blockquote', 'typograf'
    ];
    config.toolbarGroups = [
        {name: 'clipboard', groups: [ 'clipboard', 'undo', 'paste', 'pastefromword', 'copy' ]},
        {name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ]},
        {name: 'links'},
        {name: 'insert'},
        {name: 'forms'},
        {name: 'tools'},
        {name: 'document', groups: [ 'mode', 'document', 'doctools' ]},
        {name: 'others'},
        '/',
        {name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ]},
        {name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ]},
        {name: 'styles'},
        {name: 'colors'},
        {name: 'about'}
    ];

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';
};
