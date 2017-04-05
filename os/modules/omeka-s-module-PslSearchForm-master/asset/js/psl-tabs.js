(function($) {
    $.fn.pslTabs = function() {
        this.each(function() {
            var container = $(this);
            container.children('ul').find('a').on('click', function(e) {
                e.preventDefault();
                var tab = $(this).parents('li').first();
                var isActive = tab.hasClass('psl-tab-active');

                container.children('div').hide();
                container.find('.psl-tab-active').removeClass('psl-tab-active');

                if (!isActive) {
                    container.children('div' + $(this).attr('href')).show();
                    tab.addClass('psl-tab-active');
                }
            });

            container.children('div').hide();
        });
    };
})(jQuery);
