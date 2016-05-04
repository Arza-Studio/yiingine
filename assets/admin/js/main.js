/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function initializeAdminMenu()
{
    // Disabled menu items click behaviour
    $('.adminMenu .disabled a').on('click', function(e)
    {
        e.preventDefault();
    });
    
    $('.adminMenu .collapse')
    .on('show.bs.collapse', function()
    {
        $(this).prev().addClass('open');
    })
    .on('hide.bs.collapse', function()
    {
        $(this).prev().removeClass('open');
    })
    .each(function()
    {
        if($(this).hasClass('active'))
        {
            $(this).prev().addClass('active');
        }
    });
    
    $('.adminMenu .active.collapse').attr('aria-expanded','true').addClass('in').prev().attr('aria-expanded','true');
}
