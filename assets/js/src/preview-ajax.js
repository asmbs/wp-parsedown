/**
 * The AJAX setup for the Markdown Preview meta box.
 */

(function($, d, w){

  $.fn.getParsedContent = function(){
    return this.each(function(){

      // Set refs to this container and editor content
      var $this = $(this);
      var $content = $('#content');

      if ($content.val().length > (16 * 1024)) { // 16kb
        $this.html('<p>Unable to load preview; content exceeds maximum length of 16kb (16,000 characters).</p>');
        return false;
      }

      $this.css({
        opacity: '0.5'
      });

      var xhr = $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'html',
        data: {
          action: 'update_preview',
          content: $content.val()
        },
        success: function(d){
          $this.html(d);
        },
        error: function(xhr, status, httpError){
          var msg = '<p>Unable to load preview. (';
          msg += status ? status : 'unknown';
          msg += ')</p>';
          $this.html(msg);
        },
        complete: function(xhr, status) {
          $this.removeAttr('style');
        }
      });

    });
  };

  $(d).ready(function(){
    
    // Set refs to editor content and preview container
    var $content = $('#content');
    var $container = $('#parsedown-preview-content');

    // Set timeout identifier
    var delay;

    // Run the parser once when the page loads
    $container.getParsedContent();

    // Set a timeout to update the preview whenever the user stops typing,
    // defocuses the editor or pastes something in
    $content.on('keyup change paste', function(e){
      clearTimeout(delay);
      delay = setTimeout(function(){
        $container.getParsedContent();
      }, 2000);
    });

    // Clear the timeout if the user starts typing again before the
    // timeout is up.
    $content.on('keydown', function(e){
      clearTimeout(delay);
    });
  });

})(jQuery, document, window);
