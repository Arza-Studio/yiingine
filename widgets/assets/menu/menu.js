/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**This javascript routine attemps to lock the menu hierarchy that matches the current url.
 * It does so by checking all nodes of all menus. If there is one that contains the url,
 * it saves it as a match. If later on it finds on that is a better match, it replaces it.
 * Afterwards, it goes up the hierarchy and locks that parent menus and finally
 * locks all menus with the same url.
 * 
 * Matches need to be kept to account for this situation:
 * - /blog
 * - /blog/category
 * 
 * If the url /blog is requested, it would match those two menus. If the menu with the shorter href
 * is kept as a better match, it will not.
 * 
 * For the special case where the home page was requested without a language specified,
 * the url is changed to what it would be if a language had been specified. Otherwise
 * it would not match for the Home page.
 * */

function lockMenuItems(className, url, baseUrl, language, siteIndex, callback)
{   //console.log(url);
    var better = null;
    var lockedMenuItem = null;
    
    url = url.replace(document.domain, "").replace("http://", "").replace("https://", "");
    
    if(url.indexOf("#") == url.length - 1)
    {
        url = url.slice(0, url.indexOf("#"));
    }
    
    if(url == baseUrl || url == baseUrl + "/" || url == baseUrl + "/" + language + "/")
    {
        url = siteIndex;
    }

    $("." + className).find("a").each(function()
    {
        var href = $(this).prop("href");
        
        href = href.replace(document.domain, "").replace("http://", "").replace("https://", "");
        
        if(url == href || url + "/" == href)
        {
            better = this; 
        }
        else if(url.slice(0, url.indexOf("?")) == href)
        {
            better = this;
        }
        else if(url.slice(0, url.indexOf("#")) == href)
        {
            better = this;
        }
        else if(url.indexOf(href + "/") == 0)
        {
            if(lockedMenuItem && $(lockedMenuItem).prop("href").length > href.length)
            {
                return;
            }
            better = this;
        }
    });
    
    if(better == null)
    {
        return;
    }
    else if(lockedMenuItem != null)
    {
        $(lockedMenuItem).removeClass("active").removeClass("locked");
        $(lockedMenuItem).parent().removeClass("active");
        var parent = $(lockedMenuItem).parent();
        while(parent.hasClass("active"))
        {
            parent.removeClass("active");
            parent = $(parent).parent();
        }
    }

    lockedMenuItem = better;
    $(lockedMenuItem).addClass("active").addClass("locked");
    $(lockedMenuItem).parent().addClass("active");
    var parent = $(lockedMenuItem).parent();
    while(!parent.hasClass(className))
    {
        parent.addClass("active");
        parent = $(parent).parent();
    }

    $("."+className).find("a[href=\'"+url+"\']").parent().addClass("active").addClass("locked");
    
    if(callback)
    {
        callback();
    }
};

/** Unlock all menu items in a menu. */
function unlockMenuItems(className, callback)
{
    $("."+className).find(".active").each(function(){ $(this).removeClass("active").removeClass("locked"); });
    
    if(callback)
    {
        callback();
    }
}
