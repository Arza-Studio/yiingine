<?php
/**
 * @link https://github.com/Arza-Studio/yiingine
 * @copyright Copyright (c) 2016 ARZA Studio
 * @license https://github.com/Arza-Studio/yiingine/blob/master/LICENSE.md
 */

$this->beginContent('@yiingine/views/layouts/admin.php');

# CSS
// Dynamic colorization
$p = Yii::$app->adminPalette;
// css/views/layouts/main.css
$css = '
body { background-color: '.$p->get('Gray', 75).'; color: '.$p->get('Gray', -60).'; }
#header { background-color: '.$p->get('Gray', -90).'; color: '.$p->get('Gray', 40).'; }
@media (min-width: 768px) {
#header {
background: -moz-linear-gradient(left, '.$p->get('Gray', -90).' 20%, '.$p->get('Gray', -65).' 50%, '.$p->get('Gray', -90).' 80%);
background: -webkit-linear-gradient(left, '.$p->get('Gray', -90).' 20%, '.$p->get('Gray', -65).' 50%, '.$p->get('Gray', -90).' 80%);
background: linear-gradient(to right, '.$p->get('Gray', -90).' 20%, '.$p->get('Gray', -65).' 50%, '.$p->get('Gray', -90).' 80%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#00'.str_replace('#', '', $p->get('Gray', -90)).'", endColorstr="'.$p->get('Gray', -65, 0).'",GradientType=1);
}
}
#header .btn { color: '.$p->get('Gray', 40).'; border-color: '.$p->get('Gray', 40).'; background: '.$p->get('Gray', -90).'; }
#header .btn:hover { color: '.$p->get('Gray', 80).'; border-color: '.$p->get('Gray', 70).'; }
#header .btn:active { color: '.$p->get('AdminDefault').'; border-color: '.$p->get('AdminDefault').'; }
#navigation { background-color: '.$p->get('Gray', -70).'; }
#adminMenu .list-group-item.depth0 { background-color: '.$p->get('Gray', -70).'; color: '.$p->get('Gray', 30).'; border-color: '.$p->get('Gray', 30).'; }
#adminMenu .list-group-item.depth0:hover { background-color: '.$p->get('Gray', -55).'; color: '.$p->get('Gray', 50).'; border-color: '.$p->get('Gray', 50).'; }
#adminMenu .list-group-item.depth0.active { background-color: '.$p->get('AdminDefault', -70).'; color: '.$p->get('AdminDefault').'; border-color: '.$p->get('AdminDefault').'; }
#adminMenu .list-group-item.depth0.locked:before { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault').'" points="0,18 0,2 8,10 "/></svg>\');}
#adminMenu .list-group-item.depth0.locked:after { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault', -70).'" points="0,16.5 0,3.5 6.5,10 "/></svg>\'); }
#adminMenu .list-group-item.depth1 { background-color: '.$p->get('Gray', -80).'; color: '.$p->get('Gray').'; border-color: '.$p->get('Gray').'; }
#adminMenu .list-group-item.depth1:hover { background-color: '.$p->get('Gray', -65).'; color: '.$p->get('Gray', 20).'; border-color: '.$p->get('Gray', 20).'; }
#adminMenu .list-group-item.depth1.active { background-color: '.$p->get('AdminDefault', -80).'; color: '.$p->get('AdminDefault', -20).'; border-color: '.$p->get('AdminDefault', -20).'; }
#adminMenu .list-group-item.depth1.locked:before { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault').'" points="0,18 0,2 8,10 "/></svg>\');}
#adminMenu .list-group-item.depth1.locked:after { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault', -80).'" points="0,16.5 0,3.5 6.5,10 "/></svg>\'); }
#adminMenu .list-group-item.depth2 { background-color: '.$p->get('Gray', -90).'; color: '.$p->get('Gray', -30).'; border-color: '.$p->get('Gray', -30).'; }
#adminMenu .list-group-item.depth2:hover { background-color: '.$p->get('Gray', -75).'; color: '.$p->get('Gray', -10).'; border-color: '.$p->get('Gray', -10).'; }
#adminMenu .list-group-item.depth2.active { background-color: '.$p->get('AdminDefault', -90).'; color: '.$p->get('AdminDefault', -40).'; border-color: '.$p->get('AdminDefault', -40).'; }
#adminMenu .list-group-item.depth2.locked:before { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault').'" points="0,18 0,2 8,10 "/></svg>\');}
#adminMenu .list-group-item.depth2.locked:after { background-image: url(\'data:image/svg+xml;utf8,<svg x="0px" y="0px" viewBox="0 0 10 20" xmlns="http://www.w3.org/2000/svg"><polygon  fill="'.$p->rgb('AdminDefault', -90).'" points="0,16.5 0,3.5 6.5,10 "/></svg>\'); }
#adminMenu .list-group-item:active,
#adminMenu .list-group-item.open,
#adminMenu .list-group-item.open:hover { color: '.$p->get('AdminDefault').'; border-color: '.$p->get('AdminDefault').'; }
.adminInfos .label { color: '.$p->get('Gray', -30).'; }
#actionsGradient {
background: -moz-linear-gradient(top, '.$p->rgba('Gray', 75, 0).' 0%, '.$p->rgba('Gray', 75).' 95%);
background: -webkit-linear-gradient(top, '.$p->rgba('Gray', 75, 0).' 0%, '.$p->rgba('Gray', 75).' 95%);
background: linear-gradient(to bottom, '.$p->rgba('Gray', 75, 0).' 0%, '.$p->rgba('Gray', 75).' 95%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#00'.str_replace('#', '', $p->get('Gray', 75, 0)).'", endColorstr="'.$p->get('Gray', 75, 0).'",GradientType=0);
}
#actions .container-fluid { color: '.$p->get('Gray', -60).'; background-color: '.$p->get('Gray', 75).'; border-color: '.$p->get('Gray', -60).'; }
';

// css/layouts/form.css
$css .= '
.form fieldset { background-color: '.$p->get('Gray', 90).'; border-color: '.$p->get('Gray', -20).'; }
.form legend { color: '.$p->get('Gray', -80).'; }
.form input[type=text],
.form input[type=password],
.grid-view table tr.filters td input[type=text],
.form textarea { color: '.$p->get('Gray', -50).'; background-color: '.$p->get('Gray', 80).'; border-color: '.$p->get('Gray', -50).'; }
.form input[readonly=readonly] { color: '.$p->get('Gray',-10).'; background-color: '.$p->get('Gray',40).'; border-color: '.$p->get('Gray',-10).'; }
.form .group { border-color: '.$p->get('Gray', -30).'; }
.form .groupTitle,
.form .childTitle { color: '.$p->get('Gray',50).'; background-color: '.$p->get('Gray', -55).'; border-color: '.$p->get('Gray', -85).'; }
.form .group.translation { border-color: '.$p->get('Gray').'; }
.form .group.translation .groupTitle { border-color: '.$p->get('Gray').'; }
.form .group.translation .groupTitle label,
.form .group.translation .groupTitle .openCloseBtn { color: '.$p->get('Gray').'; }
.form .group.translation .groupTitle:hover label,
.form .group.translation .groupTitle:hover .openCloseBtn { color: '.$p->get('Gray',-30).'; }
.form .group.translation.focused .groupTitle { border-color: black; }
.form .group.translation.focused .groupTitle label { color: black; }
.form label,
.form label span.required { color: '.$p->get('Gray', -70).'; }
.form p { color: '.$p->get('Gray',-70).'; }
.form .form-group { color: '.$p->get('Gray').'; border-color: '.$p->get('Gray').'; }
.form .form-group .caption { color: '.$p->get('Gray', -20).'; }
.form .form-group.focused .caption { color: '.$p->get('Gray', -85).'; }
.form .form-group.captcha img { border-color: '.$p->get('Gray', 70).'; }
.form .form-group.captcha a { color: '.$p->get('Gray', 70).'; }
.form .form-group.captcha .hint { color: '.$p->get('Gray', 70).'; }
.form .form-group.captcha a:hover { color: '.$p->get('Gray', 50).'; }
.form .fileListUploaderBlock { border-color: '.$p->get('Gray', -30).'; }
.form .fileListUploaderBlock a img { border-color: '.$p->get('Gray', -30).'; }
.form .fileListUploaderBlock a:hover img { border-color: '.$p->get('Gray', -85).'; }
.form .fileListUploaderBlock a:active img { border-color:white; }
.form .fileListUploaderBlock .FLUBcolumn2 { border-color: '.$p->get('Gray', -10).'; }
.form .fileListUploaderBlock .fileNamePath a.filePath { color: '.$p->get('Gray' ).'; }
.form .fileListUploaderBlock .fileNamePath a.filePath:hover { color: black; }
.form .fileListUploaderBlock .fileNamePath a.filePath:active { color: white; }
.form .errorSummary { background-color: '.$p->get('AdminError',80).'; }
.form .errorSummary,
.form .errorSummary p,
.form .errorMessage { color: '.$p->get('AdminError', -10).'; }
';

// css/components/gridView.css
$css .= '
.grid-view table { background-color: '.$p->get('Gray', 50).'; border-color: '.$p->get('Gray', -85).'; }
.grid-view table th { color: '.$p->get('Gray',40).'; background-color: '.$p->get('Gray', -55).'; border-color: '.$p->get('Gray', -85).'; }
.grid-view table th:hover { color: '.$p->get('Gray', 80).'; background-color: '.$p->get('Gray', -75).'; }
.grid-view table tr:nth-of-type(even) { background-color: '.$p->get('Gray', 85).'; }
.grid-view table tr:nth-of-type(even) td.masked { color: '.$p->get('Gray', 80).'; }
.grid-view table tr:nth-of-type(odd) { background-color: '.$p->get('Gray', 90).'; }
.grid-view table tr:nth-of-type(odd) td.masked { color: '.$p->get('Gray', 90).'; }
.grid-view table tr.selected { background-color: '.$p->get('AdminDefault', 20).'; }
.grid-view table tr:hover { background-color: '.$p->get('AdminDefault', 50).'; }
.grid-view table tr.filters td { border-color: '.$p->get('Gray', -85).'; background-color: '.$p->get('Gray', -20).'; }
.grid-view table td { color: '.$p->get('Gray', -85).'; border-color: '.$p->get('Gray', 75).'; }
.grid-view table td.button-column a { border-color: '.$p->get('Gray', -40).'; background-color: '.$p->get('Gray', 40).'; }
.grid-view table td.button-column a:hover { border-color: '.$p->get('Gray', -80).'; background-color: '.$p->get('Gray', 60).'; }
.grid-view table td.button-column a:active { border-color: '.$p->get('Gray', -95).'; background-color: '.$p->get('Gray', -50).'; }
.grid-view table td.timestamp { color: '.$p->get('Gray', -40).'; }
.grid-view table td.timestamp { color: '.$p->get('Gray', -40).'; }
.grid-view .colorBox { border-color: '.$p->get('Gray', -50).'; }
.grid-view .summary,
.pageResizer { color: '.$p->get('Gray', 85).'; }';

$this->registerCss($css, ['media' => 'screen']);

echo $content;

$theme = $this->theme;
$this->theme = null; // Momentarily deactivate theming to prevent recursive rendering of this file.
$this->endContent();
$this->theme = $theme;
