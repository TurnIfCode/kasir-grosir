

  <!-- jQuery Validation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

  @stack('scripts')

  <script>
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
