!function($, w, d) {
    $(d).ready(function() {
        var $aceEditor = $(d.createElement('pre')).attr('id', 'ace-editor');
        $('#postdivrich').append($aceEditor);

        var aceEditor = ace.edit('ace-editor');
        aceEditor.getSession().setMode('ace/mode/markdown');
    });
}(jQuery, window, document);
