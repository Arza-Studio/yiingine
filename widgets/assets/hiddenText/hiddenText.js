/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function initHiddenText(apiUrl)
{
    $("a.hiddenText").click(function()
    {
        var obj = $(this); 
        $.ajax({
            url: apiUrl,
            data:{key: obj.find(".hiddenTextKey").html()},
            beforeSend: function()
            {
                obj.find('.hiddenTextMessage').remove();
                obj.find('.hiddenTextLoader').removeClass('hidden');
            },
            success: function(data)
            {
                obj.replaceWith(data);
            }
        });

        return false;
    });
}
