/*jshint esversion: 6 */

import SimpleMDE from 'simplemde';
require('./markdown-wp.js');

export class ReplaceEditor {

    constructor() {
        this._$wpEditor = null;
        this._simplemde = null;
    }

    initEditor() {

        // Initialize Markdown editor container
        let $markdownEditorContainer = $(document.createElement('div')).attr('id', 'markdown-editor-container');
        let $markdownEditor = $(document.createElement('div')).attr('id', 'markdown-editor');
        let $simplemdeTextarea = $(document.createElement('textarea')).attr('id', 'simplemde-textarea');
        $markdownEditor.appendTo($markdownEditorContainer);
        $simplemdeTextarea.appendTo($markdownEditor);

        // Find WP editor components
        let $wpEditorWrap = $('#wp-content-wrap'),
            $wpEditor = $('textarea#content'),
            $statusBar = $('#post-status-info');

        if ($wpEditorWrap.length === 0) {
            return false;
        }

        // Grab editor content
        this.$wpEditor = $wpEditor;
        let currentContent = $wpEditor.val();

        // Insert the Markdown container
        $markdownEditorContainer.insertBefore($statusBar);

        // Create the SimpleMDE object
        let simplemdeOptions = {
            element: document.getElementById('simplemde-textarea'),
            spellChecker: false,
            hideIcons: ["side-by-side", "fullscreen"],
            indentWithTabs: false
        };
        this.simplemde = new SimpleMDE(simplemdeOptions);

        // Initialize editor content
        this.simplemde.value(currentContent);

        // Register sync handler
        this.simplemde.codemirror.on('changes', (e) => this.syncContent(e));

    }

    syncContent() {
        this.$wpEditor.val(this.simplemde.value());
    }

    init() {

        // Replace the WP editor with a SimpleMDE
        this.initEditor();
    }

    // Getters and setters ---------------------------------------------------------------------------------------------

    get $wpEditor() {
        return this._$wpEditor;
    }

    set $wpEditor(value) {
        this._$wpEditor = value;
    }

    get simplemde() {
        return this._simplemde;
    }

    set simplemde(value) {
        this._simplemde = value;
    }
}

// Initialize
let replaceEditor = new ReplaceEditor();
replaceEditor.init();
