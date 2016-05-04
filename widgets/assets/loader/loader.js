/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

/**
 * This function initialize the behaviour for a loader
 * @param {int} id the widget id
 * @param {array} params the loader parameters
 */
function loader(id,params)
{
    loaderObject = this;
    
    loaderObject.params = params;  //console.log(params);
    
    loaderObject.onFinishDone = false;
    
    loaderObject.init = function()
    {   
        loaderObject.elements = $('.'+loaderObject.params.prefix+'LoaderElement');
        
        // FADE OUT ON CLICK
        loaderObject.elements.click(function()
        {
            loaderObject.elements.fadeOut(300);
        });

        // LOAD WINDOW
        // Warning : Bar and Statu are not available with this mode
        if(loaderObject.params.objectsToLoad == window)
        {            
            //console.log('Loader : Load Window');
            addEvent(window, "load", function(){
                                
                // If there is no onFinish option
                if(loaderObject.params.onFinish === '')
                {
                    loaderObject.elements.fadeOut(300,function(){
                        if(!loaderObject.onFinishDone)
                        {
                            loaderObject.onFinishDone = true;
                        }
                    });
                }
                // If there is a onFinish option
                else
                {
                    eval(loaderObject.params.onFinish);
                }
            });
        }

        // LOAD URLS
        else if($.isArray(loaderObject.params.objectsToLoad))
        { 
            //console.log('Loader : Load urls');

            $.preload(loaderObject.params.objectsToLoad,{
                
                onRequest:function(data)
                {
                    //console.log('onRequest() !');
                    if(loaderObject.params.displayStatu)
                    {
                        console.log(data);
                        $('#'+loaderObject.params.prefix+'LoaderStatu').html(data.image+' ('+data.loaded+'/'+data.total+')');
                    }
                },

                onComplete:function(data)
                {
                    //console.log('onComplete() !');
                    if(loaderObject.params.displayStatu)
                    {
                        console.log(data);
                        $('#'+loaderObject.params.prefix+'LoaderStatu').html(data.image+' ('+data.loaded+'/'+data.total+')');
                    }
                    if(loaderObject.params.displayBar)
                    {
                        $('#'+loaderObject.params.prefix+'LoaderBar').css('width',parseInt(data.loaded/data.total*100)+'%');
                    }
                },

                onFinish:function()
                {
                    //console.log('onFinish() !');
                    if(loaderObject.params.onFinish == '')
                    {
                        loaderObject.elements.fadeOut(300);
                    }
                    else
                    {
                        eval(loaderObject.params.onFinish);
                    }
                }
            });
        }
        
        // LOAD SELECTOR
        else
        { 
            //console.log('Loader : Load Selector');
            $(loaderObject.params.objectsToLoad).load(function()
            {
                loaderObject.elements.fadeOut(300);
            });
        }
    }
}
