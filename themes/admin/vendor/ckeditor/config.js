/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
  // 不清除空的 i 标签和 span 标签
  CKEDITOR.dtd.$removeEmpty.i = 0;
  CKEDITOR.dtd.$removeEmpty.span = 0;
  CKEDITOR.dtd.$removeEmpty.a = 0;

  CKEDITOR.dtd['a']['div'] = 1;
  CKEDITOR.dtd['a']['p'] = 1;
  CKEDITOR.dtd['a']['ul'] = 1;
  CKEDITOR.dtd['a']['ol'] = 1;

  config.fillEmptyBlocks = false;
  config.allowedContent = true;
  config.image_previewText = ' ';
  // config.filebrowserImageBrowseUrl = 'media.select';
  config.coreStyles_bold = {
      element: 'b',
      overrides: 'strong',
  };

  config.toolbarGroups = [
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'document', groups: [ 'doctools', 'mode', 'document' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];

	config.removeButtons = 'Cut,Copy,Paste,Underline,Strike,Undo,Redo,searchCode,CommentSelectedRange,UncommentSelectedRange';

  config.extraPlugins = 'codemirror';

  config.codemirror = {
    // Set this to the theme you wish to use (codemirror themes)
    theme: 'material',
  };
};
