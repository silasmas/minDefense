 <script src="{{asset('js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('js/bootstrap.bundle.min.js')}}"></script>

    <script>
      $(document).ready(function () {
        $(this).scroll(function () {
          if ($(this).scrollTop() > 40) {
            $(".header-nav").addClass("boxShadow");
          } else {
            $(".header-nav").removeClass("boxShadow");
          }
        });
      });
    </script>
  </body>
</html>
