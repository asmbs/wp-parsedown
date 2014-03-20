/**
 * The AJAX setup for the Markdown Preview meta box.
 */

(function($, d, w){

  $.fn.getParsedContent = function(){
    return this.each(function(){

      // Set refs to this container and editor content
      var $this = $(this);
      var $content = $('#content');

      $this.css({
        opacity: '0.5'
      });
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'html',
        data: {
          action: 'update_preview',
          content: $content.val()
        },
        success: function(d){
          $this.removeAttr('style');
          $this.html(d);
        },
        error: function(e){
          console.log(e);
        }
      });

      console.log('updated!');
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
