(function() {
    $(document).ready(function() {
        $('body').on('click', '.basket-update', function(e) {
            e.preventDefault();

            var button = $(this);
            var url = button.attr('data-url');
            $.ajax(url).done(function(data) {
                button.replaceWith(data.content);
            });
        });
    });
})();
