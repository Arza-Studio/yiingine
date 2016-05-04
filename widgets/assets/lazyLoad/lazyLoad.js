/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function lazyLoadInit()
{
    $("[data-src]").each(function()
    {
        var dom = $(this);
        var parent = dom.parent();
        // In case og img tag : we can get width and height.
        if(dom.attr('data-width') && dom.attr('data-height'))
        {
            // If the parent is not already managing the height (forced ratio)
            if(!parent.hasClass('embed-responsive'))
            {
                // Calculate the proportial size.
                var parentHeight = parent.width()*dom.attr('data-height')/dom.attr('data-width');
                // Apply the height resulted to the lazy-loader (parent).
                parent.css({height:parentHeight});
            }
        }
        dom.css({opacity:0}).load(function()
        {
            // Fading in
            dom.animate({opacity:1});
            // Change the parent class and remove svg loader
            dom.parent().removeClass("lazy-loading").addClass("lazy-loaded").removeAttr('style').find('svg').first().remove(); 
        });
        parent.addClass("lazy-loader"); 
    });
    $(window).load(function(){ lazyLoadLoading(); }).scroll(function(){ lazyLoadLoading(); });
}

function lazyLoadLoading()
{
    $("[data-src]").each(function()
    {
        // If the iframe is visible in the scrolled screen.
        if(isScrolledIntoView(this)) // See : basic.js
        {   
            var dom = $(this);
            var parent = dom.parent();
            parent.addClass("lazy-loading").prepend(lazyLoadLoader); // see : LazyLoad.php
            dom.attr("src", function(){ return dom.data("src"); }).removeAttr("data-src");
        }
    });
}
