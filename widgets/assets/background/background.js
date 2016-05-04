/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

function fadeGradientToImage(gradientSelector, gradientCss, imageWidth, imageHeight)
{
    var gradient = $(gradientSelector);
    var backgroundImageHeight = gradient.outerWidth()*imageHeight/imageWidth;
    if(gradientCss.match(/\{imageTop\}/))
    {
        var imageTop = gradient.outerHeight()-backgroundImageHeight;
        var gradientCss = gradientCss.replace('{imageTop}', imageTop+'px');
    }
    if(gradientCss.match(/\{imageBottom\}/))
    {
        var imageBottom = backgroundImageHeight;
        var gradientCss = gradientCss.replace('{imageBottom}', imageBottom+'px'); 
    }
    gradient.css({background:gradientCss});
}
