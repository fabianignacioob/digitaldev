(function() {
  'use strict';

  $(document).ready(function() {
    // to change modal content of each item (learn more https://getbootstrap.com/docs/4.0/components/modal/#varying-modal-content)
    // you'll probably get rid of this in real life application
    $('#classDetails').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var title = button.data('title');
      var img = button.data('img');
      var modal = $(this);
      modal.find('.modal-title').text(title);
      modal.find('.modal-img').attr('src', img);
    });
  });
})();
