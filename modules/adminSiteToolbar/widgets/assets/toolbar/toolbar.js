/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function Toolbar(id, params)
{
    // To simplify the targeting of this object "this" is remaned as "obj"
    var obj = this; //console.log(obj);
    
    // Get the id of the widget
    obj.id = id; //console.log('obj.id = '+obj.id);
    
    // Get the toolbar menu
    obj.toolbar = $('#'+obj.id);
    
    // Get the toolbar "show" button
    obj.showBtn = $('#'+obj.id+'-show-btn');
    
    // Get the toolbar "hide" button
    obj.hideBtn = $('#'+obj.id+'-hide-btn');
    
    // Get the activeFirstButton value
    obj.firstShown = true;
    
    // Get the activeFirstButton value
    obj.activeFirstButton = params.activeFirstButton;
    
    // On toolbar show callback
    obj.onToolbarShow = params.onToolbarShow;
    
    // On toolbar shown callback
    obj.onToolbarShown = params.onToolbarShown;
    
    // On toolbar hide callback
    obj.onToolbarHide = params.onToolbarHide;
    
    // On toolbar hidden callback
    obj.onToolbarHidden = params.onToolbarHidden;
    
    // Set listener isUpdateWindow to prevent infinite loop on window resize
    obj.isWindowUpdate = false;
    
    /**
     * Initialise and display a first time the Overlay.
     */
    obj.init = function()
    {
        // Set menu height to window height
        obj.toolbar.show();
        $(window).on('resize', function()
        {
            if(obj.toolbar.hasClass('in'))
            {
                var toolbarWidth = obj.toolbar.outerWidth();
                $('.adminSiteToolbarCanvas, .navbar-fixed-top').css({left:toolbarWidth+'px',right:'-'+toolbarWidth+'px'});
            }
        });
        
        // Offcanvas init
        obj.offcanvas = obj.toolbar.offcanvas({
            canvas: '.adminSiteToolbarCanvas',
            toggle: false,
            placement: "left",
            autohide: false,
            recalc: false,
            disableScrolling: false,
        })
        // Offcanvas events
        .on('show.bs.offcanvas', function(e)
        {
            if(obj.onToolbarShow)
            {
                eval(obj.onToolbarShow);
            }
            $("body").css({overflowX:"hidden"});
            obj.showBtn.hide();
        })
        .on('shown.bs.offcanvas', function(e)
        {
            $(this).css({zIndex:1});
            if(obj.activeFirstButton && obj.firstShown)
            {
                $(this).find('button').first().trigger('click');
            }
            if(obj.onToolbarShown)
            {
                eval(obj.onToolbarShown);
            }
            if(obj.firstShown === true)
            {
                obj.firstShown = false;
            }
            obj.updateWindow();
        })
        .on('hide.bs.offcanvas', function(e)
        {
            if(obj.onToolbarHide)
            {
                eval(obj.onToolbarHide);
            }
            $(this).css({zIndex:-1});
        })
        .on('hidden.bs.offcanvas', function(e)
        {
            $(this).css({zIndex:-1});
            $("body").css({overflowX:"normal"});
            obj.showBtn.show();
            if(obj.onToolbarHidden)
            {
                eval(obj.onToolbarHidden);
            }
            obj.updateWindow();
        });
        
        // "Show" button click behaviour
        obj.showBtn.on('click', function(e)
        {
            obj.toolbar.offcanvas('show');
        });
        
        // "Hide" button click behaviour
        obj.hideBtn.on('click', function(e)
        {
            obj.toolbar.offcanvas('hide');
        });
        
        // Set adminSiteToolbarBtn behaviour
        obj.toolbar.find('.adminSiteToolbarBtn').not(obj.hideBtn).on('click', function(e)
        {
            $('.adminSiteToolbarBtn.active').not(this).removeClass('active')
            $(this).toggleClass('active');
        });
    }
    
    obj.updateWindow = function()
    {   
        obj.isWindowUpdate = true;
        $(window).trigger('resize');
        obj.isWindowUpdate = false;
    }
}
