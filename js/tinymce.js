(function() {
	tinymce.PluginManager.add( 'tinymce_duechiacchiere', function( editor, url ) {
		
		// Add Buttons to Visual Editor Toolbar
		editor.addButton( 'tinymce_abbr', {
			title: 'Abbreviation',
			image: url + '/tinymce-abbr.png',
			id: 'mce-wp-abbr',
			cmd: 'tinymce_abbr_modal',
			stateSelector: 'abbr'
		});

		// Get current editor selection and toggle class and aria-pressed attributes on abbr tinymce button
		editor.on('NodeChange', function(e){
			var node = editor.selection.getNode();
			if (node.nodeName == 'ABBR') {
				jQuery('#mce-wp-abbr').addClass('mce-active');
				jQuery('#mce-wp-abbr').attr('aria-pressed', 'true');
			} else {
				jQuery('#mce-wp-abbr').removeClass('mce-active');
				jQuery('#mce-wp-abbr').attr('aria-pressed', 'false');
			}
		});

		// Called when we click the abbreviation button
		editor.addCommand( 'tinymce_abbr_modal', function() {
			// Check we have selected some text that we want to link
			var text = editor.selection.getContent({
				'format': 'html'
			});
			if ( text.length === 0 ) {
				alert( 'No text selected' );
				return;
			}

			// Check current editor selection and fire the good behavior based on the node selected
			var node = editor.selection.getNode();
			if (node.nodeName == 'ABBR') {
				// If ABBR is already present then remove it
				editor.dom.remove(node, true);
			} else {
				// else, in means this node is not an abbr, then call abbreviation modal dialog
				editor.windowManager.open({
					// Modal settings
					title: 'Add',
					id: 'tinymce-abbr-insert-dialog',
					body: [
						{
							type : 'textbox',
							id: 'tinymce-abbr-title',
							name : 'abbrTitle',
							label : 'Title',
							tooltip: 'The meaning of your abbreviation',
            },
						{
							type : 'textbox',
							id: 'tinymce-abbr-lang',
							name : 'abbrLang',
							label : 'Language (optional)',
							tooltip: 'Example: fr, en, de, etc.',
						}
					],

					onsubmit: function(e) {
						var text = editor.selection.getContent({
							'format': 'html'
						});
						if ( jQuery('#tinymce-abbr-lang').val() ) {
							editor.execCommand('mceReplaceContent', false, '<abbr title="' + jQuery('#tinymce-abbr-title').val() + '" lang="' + jQuery('#tinymce-abbr-lang').val() + '">' + text + '</abbr>');
						} else {
							editor.execCommand('mceReplaceContent', false, '<abbr title="' + jQuery('#tinymce-abbr-title').val() + '">' + text + '</abbr>');
						}
						editor.windowManager.close();
					}
				});
			}
		});
	});
})();