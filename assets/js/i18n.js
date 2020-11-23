window.addEventListener('load', function () {
    var initTabs = function() {
      document.querySelectorAll('.php-form-translation-field > ul:last-child > li > a:not(.tab-handled)').forEach(function (a) {
          a.classList.add('tab-handled');
          a.addEventListener('click', function (e) {
              e.preventDefault();
              a.parentElement.parentElement.querySelectorAll(':scope > li').forEach(function (li) {
                  li.classList.remove('active');
              });
              a.parentElement.classList.add('active');
              var content = document.getElementById(a.hash.substring(1));

              if (content) {
                  content.parentElement.querySelectorAll(':scope > li').forEach(function (li) {
                      li.classList.remove('active');
                  });

                  content.classList.add('active');
              }
          });
      });
    };

    initTabs();
    document.addEventListener('DOMNodeInserted', initTabs);
});