/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function Overlay(id, params)
{
    // To simplify the targeting of this object "this" is remaned as "obj"
    var obj = this; //console.log(obj);

    // List it in instances
    Overlay.instances.push(obj);
    
    // Get the id of the widget
    obj.id = id; //console.log('obj.id = '+obj.id);
    
    // Is initialized
    obj.isInitialisazed = false;
    
    // Is displayed
    obj.isDisplayed = false;
    
    // Layer
    obj.layer = params.layer;
    
    // Selector
    obj.selector = $(params.selector);
    
    // Url
    obj.url = params.url;
    
    // Content
    obj.content = params.content;
    
    // Options
    obj.options = params.options;
    
    // Forced offsets
    obj.offsetTop = params.offsetTop;
    obj.offsetLeft = params.offsetLeft;
    
    // Display
    obj.displayRule = params.displayRule;
    
    // On click action
    obj.onClick = params.onClick;
    
    // On before display action
    obj.beforeDisplay = params.beforeDisplay;
    
    // On after display action
    obj.afterDisplay = params.afterDisplay;
    
    // On before hide action
    obj.beforeHide = params.beforeHide;
    
    // On after hide action
    obj.afterHide = params.afterHide;
    
    /**
     * Calculate and set styles to the Overlay.
     */
    obj.stylize = function()
    {
        // Calculate styles with the selector ones
        obj.css = {
            width: obj.selector.innerWidth() + parseInt(obj.selector.css('border-left-width')) + parseInt(obj.selector.css('border-right-width')),
            height: obj.selector.innerHeight() + parseInt(obj.selector.css('border-top-width')) + parseInt(obj.selector.css('border-bottom-width')),
            borderTopLeftRadius: obj.selector.css('border-top-left-radius'),
            borderTopRightRadius: obj.selector.css('border-top-right-radius'),
            borderBottomLeftRadius: obj.selector.css('border-bottom-left-radius'),
            borderBottomRightRadius: obj.selector.css('border-bottom-right-radius'),
            top: obj.offsetTop ? eval(obj.offsetTop) : obj.selector.offset().top,
            left: obj.offsetLeft ? eval(obj.offsetLeft) : obj.selector.offset().left,
        };

        // Set Overlay styles
        obj.overlay.css(obj.css);
    }
    
    /**
     * Initialise and display a first time the Overlay.
     */
    obj.init = function(display, hide)
    {
        // If the selector doesn't exist we stop init here.
        if(!obj.selector.length)
        {
            return false;
        }
        
        // If the overlayLayer has not been created already
        if(!$('#'+obj.layer).length)
        {
            // Create a div on top of everything and with entire document size
            $('body').append('<div id="'+obj.layer+'" class="overlayLayer"></div>');
        }
        
        // If the overlay has been moved inside overlayLayer already we remove it
        // Note : it can be in case of re-init
        $('#'+obj.id).remove();

        // Creating overlay
        //obj.overlay = $('<div id="'+obj.id+'" class="overlay" '+((obj.url) ? 'data-url="'+obj.url+'"' : '')+'></div>');
        obj.overlay = $('<div id="'+obj.id+'" class="overlay"></div>');

        // Build overlay options (attributes)        
        if(obj.options)
        {
            for(var key in obj.options)
            {
                if(key == 'id' || key == 'href') continue; // "id" and "href" attributes are not editable by this way.
                if(key == 'class') obj.overlay.addClass(obj.options[key]); // "overlay" class is not removable.
                else obj.overlay.attr(key,obj.options[key]);
            }
        }
        
        // Insert overlay content
        if(obj.content)
        {
            obj.overlay.html('<div class="overlay-content">'+obj.content+'</div>');
        }
        
        // Stylize the overlay
        obj.stylize();
        
        // If the current overlay parent if not already overlayLayer (in case of resizing for example)
        if(obj.overlay.parent() != $('#'+obj.layer))
        {
            // Sorting rule : the larger behind 
            // If there is no overlay inside overlayLayer yet
            if(!$('#'+obj.layer+' .overlay').length)
            {
                // Just move the overlay into overlayLayer
                obj.overlay.appendTo('#'+obj.layer);
            }
            // If there are overlays inside overlayLayer already
            else
            {
                var smallerOverlay = false;
                // Look for the first smaller overlay already placed in overlayLayer.
                $('#'+obj.layer+' .overlay').each(function()
                {
                    var instance = Overlay.getInstance($(this).prop('id'));

                    if(obj.selectorWidth > instance.parentWidth || obj.selectorHeight > instance.parentHeight)
                    {
                        // Save the first smaller overlay found.
                        smallerOverlay = instance.dom;
                        // Break the each loop
                        return false;
                    }
                });
                // If there is no smaller overlay found.
                if(!smallerOverlay)
                {
                    // Move the overlay into overlayLayer and the end
                    obj.overlay.appendTo('#'+obj.layer);
                }
                // If there is a smaller overlay found.
                else
                {
                    // Insert the overlay before it
                    obj.overlay.insertBefore("#"+smallerOverlay.prop('id'));
                }
            }
        }

        // Mouse behaviours
        // Mobile
        obj.overlay.on('touchend', function(e)
        {
            e.preventDefault();
        });
        obj.overlay.bind('tap', function(e) // see : jquery-hammerjs
        {
            if(!obj.overlay.is(':hover') && !obj.overlay.hasClass('active'))
            {
                // One active overlay only at a time.
                $('.overlay').removeClass('active');
                obj.overlay.addClass('active');
            }
            else
            {
                if(obj.onClick)
                {
                    eval(obj.onClick);
                }
                else if(obj.url)
                {
                    window.location.href = obj.url;
                }
            }
        });
        // Desktop
        obj.overlay.bind('click', function(e)
        {   
            if(obj.onClick)
            {console.log(obj.onClick);
                e.preventDefault();
                eval(obj.onClick);
            }
            else if(obj.url)
            {
                window.location.href = obj.url;
            }
        });
                
        // Reinit overlay behaviour on window resize.
        $(window).resize(function()
        {
            obj.overlay.hide();
            waitForFinalEvent(function()
            {
                obj.reinit();
            }, 250, obj.id);
        });

        obj.isInitialisazed = true;

        if(display)
        {
            obj.display();
        }

        if(hide)
        {
            obj.hide();
        }
    }
        
    /**
     * Re-init the Overlay.
     */
    obj.reinit = function()
    {
        if(obj.isDisplayed)
        {
            obj.hide();
            obj.stylize();
            obj.display();
        }
        else
        {
            obj.stylize();
        }
    }
    
    /**
     * Display the Overlay.
     */
    obj.display = function()
    {
        obj.isDisplayed = true;

        if(!obj.isInitialisazed)
        {
            // Initialized the Overlay
            obj.init(true);
        }
        else
        {
            if(eval(obj.displayRule))
            {
                if(obj.beforeDisplay)
                {
                    eval(obj.beforeDisplay);
                }
                // We don't use fadeIn case it display:inline
                obj.overlay.css({display:'block',opacity:0}).stop().animate({opacity:1}, 250, function()
                {
                    if(obj.afterDisplay)
                    {
                        eval(obj.afterDisplay);
                    }
                });
            }
        }
    }
    
    /**
     * Hide the Overlay.
     */
    obj.hide = function()
    {
        obj.isDisplayed = false;
        if(obj.beforeHide)
        {
            eval(obj.beforeHide);
        }
        obj.overlay.hide();
        if(obj.afterHide)
        {
            eval(obj.afterHide);
        }
    }
    
};

// Instances recorder
Overlay.instances = [];

// Instances destroyer
Overlay.destroy = function(inst)
{
    if(Overlay.instances.length)
    {
        for(var i=0; i<Overlay.instances.length; i++)
        {
            if(Overlay.instances[i] === inst)
            {
                Overlay.instances.splice(i, 1);
            }
        }
    }
};

Overlay.initAll = function(layer)
{
    for(var i= 0; i < Overlay.instances.length; i++)
    {
        if(layer && Overlay.instances[i].layer != layer)
        {
            continue;
        }
        Overlay.instances[i].init(true);
    }
};
Overlay.displayAll = function(layer)
{
    for(var i= 0; i < Overlay.instances.length; i++)
    {
        if(layer && Overlay.instances[i].layer != layer)
        {
            continue;
        }
        Overlay.instances[i].display();
    }
};
Overlay.displayEnabledOnly = function()
{
    for(var i=0; i<Overlay.instances.length; i++)
    {
        if(!Overlay.instances[i].isInitialisazed)
        {
            Overlay.instances[i].init();
        }
        if(!Overlay.instances[i].dom.hasClass('disabled'))
        {
            Overlay.instances[i].display();
        }
        else
        {
            Overlay.instances[i].hide();
        }
    }
};
Overlay.getAllDisplayed = function(layer)
{
    var displayed = [];
    for(var i= 0; i < Overlay.instances.length; i++)
    {
        if(layer && Overlay.instances[i].layer != layer)
        {
            continue;
        }
        if(Overlay.instances[i].isDisplayed) displayed.push(Overlay.instances[i]);
    }
    return displayed;
}
Overlay.hideAll = function(layer)
{
    for(var i= 0; i < Overlay.instances.length; i++)
    {
        if(layer && Overlay.instances[i].layer != layer)
        {
            continue;
        }
        Overlay.instances[i].hide();
    }
};
Overlay.reinitAll = function(layer)
{
    for(var i= 0; i < Overlay.instances.length; i++)
    {
        if(layer && Overlay.instances[i].layer != layer)
        {
            continue;
        }
        Overlay.instances[i].reinit();
    }
};
Overlay.getInstance = function(id)
{
    return eval(id);
}
