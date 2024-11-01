/*
* @author Motopress
* @license GPLv2 or later
*/
jQuery(function(){function e(){if(jQuery(".vrg-main-scene").length>0){var e=jQuery(".vrg-main-scene").attr("height");if(i(e,"%")){n(parseInt(e))}}}function n(e){vph=jQuery(window).height(),recounted_height=vph/100*e,jQuery(".vrg-main-scene").css({height:recounted_height+"px"})}function i(e,n,i){var r=0;return e+="",-1!==(r=e.indexOf(n))&&(i?e.substr(0,r):e.slice(r))}e(),window.onresize=function(n){e()}});