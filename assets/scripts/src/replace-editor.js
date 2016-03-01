!function($, w, d) {

    $.parsedown = {

        aceObject: null,
        aceSession: null,
        syncTimer: null,
        $wpEditor: null,

        options: {
            aceId: 'ace-editor',
            aceOptions: {
                minLines: 24,
                maxLines: 1024,
                showInvisibles: true,
            },
            aceTheme: 'ace/theme/clouds',
            syncDelay: 500
        },

        initEditor: function(editorId) {
            // Initialize Ace container
            var $aceContainer = $(d.createElement('pre')).attr('id', this.options.aceId);

            // Find WP editor components
            var $wpEditorWrap = $('#wp-content-wrap'),
                $wpEditor = $('textarea#content'),
                $statusBar = $('#post-status-info');

            // Grab editor content
            this.$wpEditor = $wpEditor;
            var currentContent = $wpEditor.val();

            // Hide wrap and insert the Ace container
            $wpEditorWrap.hide();
            $aceContainer.insertBefore($statusBar);

            // Initialize the actual Ace editor
            var _ace = this.aceObject = w.ace.edit(this.options.aceId),
                _aceSession = this.aceSession = _ace.getSession();
            _ace.setOptions(this.options.aceOptions);
            _ace.setTheme(this.options.aceTheme);

            // Set session options
            _aceSession.setMode('ace/mode/markdown');
            _aceSession.setUseWrapMode(true);

            // Initialize editor content
            _ace.setValue(currentContent, -1);

            // Register sync handler
            $(w).on('sync.parsedown', this.syncContent);

            // Set key listener and submit listener
            $aceContainer.on('keyup', this.armTrigger);
            $(w).on('submit.parsedown', this.fireTrigger);
        },

        armTrigger: function() {
            var _p = $.parsedown;
            if (_p.syncTimer) {
                clearTimeout(_p.syncTimer);
            }
            _p.syncTimer = setTimeout(_p.fireTrigger, _p.options.syncDelay);
        },

        fireTrigger: function() {
            $(w).trigger('sync.parsedown');

            return true;
        },

        syncContent: function() {
            var _p = $.parsedown;
            _p.$wpEditor.val(_p.aceObject.getValue());
        }
    };

    $(d).ready(function() {

        // Initialize component and set editor reference
        var parsedown = $.parsedown,
            _ace = $.parsedown.aceObject;

        // Replace the editor
        parsedown.initEditor('ace-editor');

        // Listen on autosave and form submit
        // $(w).on('before-autosave', parsedown, this.syncContent);
        $(w).on('submit submit.autosave-local', parsedown, this.syncContent);
    });
}(jQuery, window, document);
