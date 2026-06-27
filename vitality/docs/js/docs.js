(function($) {
  'use strict'
  $(function() {
    // Insert copy to clipboard button before .highlight
    $('.highlight').each(function() {
      var btnHtml = '<div class="bd-clipboard"><span class="btn-clipboard" title="Copy to clipboard">Copy</span></div>'
      $(this).before(btnHtml)
      $('.btn-clipboard').tooltip()
    })
    var clipboard = new Clipboard('.btn-clipboard', {
      target: function(trigger) {
        return trigger.parentNode.nextElementSibling
      }
    })
    clipboard.on('success', function(e) {
      $(e.trigger).attr('title', 'Copied!').tooltip('_fixTitle').tooltip('show').attr('title', 'Copy to clipboard').tooltip('_fixTitle')
      e.clearSelection()
    })
    clipboard.on('error', function(e) {
      var modifierKey = /Mac/i.test(navigator.userAgent) ?
        '\u2318' :
        'Ctrl-'
      var fallbackMsg = 'Press ' + modifierKey + 'C to copy'
      $(e.trigger).attr('title', fallbackMsg).tooltip('_fixTitle').tooltip('show').attr('title', 'Copy to clipboard').tooltip('_fixTitle')
    })
  })
}(jQuery))

// Scrollspy for Sidebar on Resources Page
$('body').scrollspy({
  target: '.nav-sidebar>ul'
})
