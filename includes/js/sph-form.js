jQuery.noConflict();
(function( $ ) {
$(function() {
$('.sph-text').hide();
$('.sph-checkbox input[type="checkbox"]').on('click', function () {
         $('.sph-text').slideToggle();
});
});
})(jQuery);