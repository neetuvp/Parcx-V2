/**
 * Minified by jsDelivr using UglifyJS v3.3.20.
 * Original file: /npm/canvas2image@1.0.5/canvas2image.js
 * 
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
var Canvas2Image=function(){var t,n,o=(t=document.createElement("canvas"),{canvas:!!(n=t.getContext("2d")),imageData:!!n.getImageData,dataURL:!!t.toDataURL,btoa:!!window.btoa}),i="image/octet-stream";function u(t,n,e){var r=t.width,a=t.height;null==n&&(n=r),null==e&&(e=a);var o=document.createElement("canvas"),i=o.getContext("2d");return o.width=n,o.height=e,i.drawImage(t,0,0,r,a,0,0,n,e),o}function c(t,n,e,r){return(t=u(t,e,r)).toDataURL(n)}function g(t){document.location.href=t}function f(t){var n=document.createElement("img");return n.src=t,n}function m(t){return"image/"+(t=t.toLowerCase().replace(/jpg/i,"jpeg")).match(/png|jpeg|bmp|gif/)[0]}function b(t){if(!window.btoa)throw"btoa undefined";var n="";if("string"==typeof t)n=t;else for(var e=0;e<t.length;e++)n+=String.fromCharCode(t[e]);return btoa(n)}function v(t){var n=t.width,e=t.height;return t.getContext("2d").getImageData(0,0,n,e)}function d(t,n){return"data:"+n+";base64,"+t}var s=function(t){var n=t.width,e=t.height,r=n*e*3,a=r+54,o=[66,77,255&a,a>>8&255,a>>16&255,a>>24&255,0,0,0,0,54,0,0,0],i=[40,0,0,0,255&n,n>>8&255,n>>16&255,n>>24&255,255&e,e>>8&255,e>>16&255,e>>24&255,1,0,24,0,0,0,0,0,255&r,r>>8&255,r>>16&255,r>>24&255,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],u=(4-3*n%4)%4,c=t.data,g="",f=n<<2,m=e,v=String.fromCharCode;do{for(var d=f*(m-1),s="",p=0;p<n;p++){var h=p<<2;s+=v(c[d+h+2])+v(c[d+h+1])+v(c[d+h])}for(var l=0;l<u;l++)s+=String.fromCharCode(0);g+=s}while(--m);return b(o.concat(i))+b(g)},r=function(t,n,e,r){if(o.canvas&&o.dataURL)if("string"==typeof t&&(t=document.getElementById(t)),null==r&&(r="png"),r=m(r),/bmp/.test(r)){var a=v(u(t,n,e));g(d(s(a),i))}else{g(c(t,r,n,e).replace(r,i))}},a=function(t,n,e,r){if(o.canvas&&o.dataURL){if("string"==typeof t&&(t=document.getElementById(t)),null==r&&(r="png"),r=m(r),/bmp/.test(r)){var a=v(u(t,n,e));return f(d(s(a),"image/bmp"))}return f(c(t,r,n,e))}};return{saveAsImage:r,saveAsPNG:function(t,n,e){return r(t,n,e,"png")},saveAsJPEG:function(t,n,e){return r(t,n,e,"jpeg")},saveAsGIF:function(t,n,e){return r(t,n,e,"gif")},saveAsBMP:function(t,n,e){return r(t,n,e,"bmp")},convertToImage:a,convertToPNG:function(t,n,e){return a(t,n,e,"png")},convertToJPEG:function(t,n,e){return a(t,n,e,"jpeg")},convertToGIF:function(t,n,e){return a(t,n,e,"gif")},convertToBMP:function(t,n,e){return a(t,n,e,"bmp")}}}();
//# sourceMappingURL=/sm/1ead90f6df38bedcda00274bbad95d5f2ed96a8baa6cd675cad8209caab8be54.map