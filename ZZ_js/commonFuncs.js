  'use strict';
  
  function alertParentForIFrameSize( width, height ) {
    var width = (typeof width !== 'undefined') ?  width : -1;    // iOS doesn't seem to handle default parameters
    var height = (typeof height !== 'undefined') ?  height : -1; // iOS doesn't seem to handle default parameters
console.log( 'In alertParentForIFrameSize: width=['+width+'], height=['+height+']');
    if( width < 0 ) { width = document.body.scrollWidth; }
    if( height < 0 ) { height = document.body.scrollHeight; }  
    if (typeof window.top.alertsize == 'function') {
      window.top.alertsize( width, height ); 
    } else {
      console.log( 'In alertParentForIFrameSize: NO PARENT PRESENT width=['+width+'], height=['+height+']');
    }  
  }  

// Google Analytics
/*
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-52217632-1', 'ntom.vn');
  ga('send', 'pageview');
*/