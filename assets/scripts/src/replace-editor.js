!function($, w, d) {

    $.parsedown = {

        aceObject: null,
        $wpEditor: null,

        options: {
            aceId: 'ace-editor',
            aceOptions: {
                minLines: 24,
                maxLines: 1024
            }
        },

        replaceEditor: function(editorId) {
            // Initialize Ace container
            var $aceContainer = $(d.createElement('pre')).attr('id', this.options.aceId);

            // Find WP editor components
            var $wpEditorWrap = $('#wp-content-wrap'),
                $wpEditor = $('textarea#content'),
                $statusBar = $('#post-status-info');

            // Grab editor content
            var currentContent = this.$wpEditor = $wpEditor.val();

            // Hide wrap and insert the Ace container
            // $wpEditorWrap.hide();
            $aceContainer.insertBefore($statusBar);

            // Initialize the actual Ace editor
            var _ace = this.aceObject = w.ace.edit(this.options.aceId);
            _ace.setOptions(this.options.aceOptions);
            _ace.getSession().setMode('ace/mode/markdown');
            _ace.setValue(currentContent);
        },

        syncContent: function(e) {
            var parsedown = e.data;
            parsedown.$wpEditor.val(parsedown.aceObject.getValue());
        }
    };

    $(d).ready(function() {

        // Initialize component and set editor reference
        var parsedown = $.parsedown,
            _ace = $.parsedown.aceObject;

        // Replace the editor
        parsedown.replaceEditor('ace-editor');

        // Listen on autosave and form submit
        $(w).on('before-autosave', parsedown, this.syncContent);
        $('form#post').on('submit', parsedown, this.syncContent);
    });
}(jQuery, window, document);
