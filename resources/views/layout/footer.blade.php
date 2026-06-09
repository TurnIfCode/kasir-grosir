


  @stack('scripts')

  <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }

    $(window).on('load', function() {
      var currentPath = window.location.pathname;
      $('.nav-link').each(function() {
        var href = $(this).attr('href');
        if (href) {
          var hrefPath = new URL(href, window.location.origin).pathname;
          if (hrefPath === currentPath) {
            $(this).addClass('active');
            // If it's inside a collapse, show the collapse and add active to toggle
            var collapse = $(this).closest('.collapse');
            if (collapse.length) {
              collapse.addClass('show');
              var toggle = $('[data-bs-target="#' + collapse.attr('id') + '"]');
              toggle.addClass('active').attr('aria-expanded', 'true');
            }
          }
        }
      });
    });
  </script>
