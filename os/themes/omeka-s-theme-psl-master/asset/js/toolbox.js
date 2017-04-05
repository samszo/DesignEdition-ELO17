$(document).ready(function() {
    $('#psl-toolbox li').on('mouseenter', function() {
        $('#psl-toolbox > div').hide();
        $('#psl-toolbox div' + $(this).find('a').attr('href')).show();
    });
    $('#psl-toolbox').on('mouseleave', function(e) {
        $('#psl-toolbox > div').hide();
    });
});
