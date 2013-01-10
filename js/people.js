/*
 * Modernizr 1.7 - csstransforms & csstransitions
 * Checks to do pretty animations in modern browsers
 */
window.Modernizr=function(a,b,c){function G(){}function F(a,b){var c=a.charAt(0).toUpperCase()+a.substr(1),d=(a+" "+p.join(c+" ")+c).split(" ");return!!E(d,b)}function E(a,b){for(var d in a)if(k[a[d]]!==c&&(!b||b(a[d],j)))return!0}function D(a,b){return(""+a).indexOf(b)!==-1}function C(a,b){return typeof a===b}function B(a,b){return A(o.join(a+";")+(b||""))}function A(a){k.cssText=a}var d="1.7",e={},f=!0,g=b.documentElement,h=b.head||b.getElementsByTagName("head")[0],i="modernizr",j=b.createElement(i),k=j.style,l=b.createElement("input"),m=":)",n=Object.prototype.toString,o=" -webkit- -moz- -o- -ms- -khtml- ".split(" "),p="Webkit Moz O ms Khtml".split(" "),q={svg:"http://www.w3.org/2000/svg"},r={},s={},t={},u=[],v,w=function(a){var c=b.createElement("style"),d=b.createElement("div"),e;c.textContent=a+"{#modernizr{height:3px}}",h.appendChild(c),d.id="modernizr",g.appendChild(d),e=d.offsetHeight===3,c.parentNode.removeChild(c),d.parentNode.removeChild(d);return!!e},x=function(){function d(d,e){e=e||b.createElement(a[d]||"div");var f=(d="on"+d)in e;f||(e.setAttribute||(e=b.createElement("div")),e.setAttribute&&e.removeAttribute&&(e.setAttribute(d,""),f=C(e[d],"function"),C(e[d],c)||(e[d]=c),e.removeAttribute(d))),e=null;return f}var a={select:"input",change:"input",submit:"form",reset:"form",error:"img",load:"img",abort:"img"};return d}(),y=({}).hasOwnProperty,z;C(y,c)||C(y.call,c)?z=function(a,b){return b in a&&C(a.constructor.prototype[b],c)}:z=function(a,b){return y.call(a,b)},r.csstransforms=function(){return!!E(["transformProperty","WebkitTransform","MozTransform","OTransform","msTransform"])},r.csstransitions=function(){return F("transitionProperty")};for(var H in r)z(r,H)&&(v=H.toLowerCase(),e[v]=r[H](),u.push((e[v]?"":"no-")+v));e.input||G(),e.crosswindowmessaging=e.postmessage,e.historymanagement=e.history,e.addTest=function(a,b){a=a.toLowerCase();if(!e[a]){b=!!b(),g.className+=" "+(b?"":"no-")+a,e[a]=b;return e}},A(""),j=l=null,e._enableHTML5=f,e._version=d,g.className=g.className.replace(/\bno-js\b/,"")+" js "+u.join(" ");return e}(this,this.document)


jQuery(function($)
{
   
    /*
     * Javascript for the Staff Profile Pages
     * Creates an accordion on some of the fields
     * makes top tab active
     */
    if ($('body').is('.post-type-archive-people, .single-people')) {
        $('.nav-main li').each(function(){
    		if($.trim($(this).text().toLowerCase()) == "people") {
    			$(this).addClass('current-menu-item');
    		}
    	});
    }

    $('.abstract').before('<a href="#" class="abstractlink">&#9660; Read abstract</a>').hide();
    $('.abstractlink').click(function(){
    	var link = this;
		if ($(this).next(':visible').length) {
			$(this).next().slideUp('slow', function(){
				$(link).html('&#9660; Read abstract');
			});
		} else {
			$(this).next().slideDown('slow', function(){
				$(link).html('&#9650; Hide abstract');
			});
		}
		return false;
	});

     
    /* If the accordion class exists */
	if($('.accordion').length)
	{
		/* Speed for the animation */
		var speed = 250;
		
		/* get the divs to hide */
		var section = $('.accordion').children('div');
		
		/* get the headers to click on */
		var header = $('.accordion').children('h3');
		
		/* hide the divs */
		section.hide();
		
		/* append the down arrow to headers */
		//header.append(' <span class="arrow">&#8615;</span>');
		header.append(' <span class="arrow">&#9660;</span>');
		
		/* set up the transform transition animation in compatible browsers */
		if(Modernizr.csstransforms && Modernizr.csstransitions)
		{
			/* hurrah for different styles to declare the same speed! */
			var speedInMS = speed/1000;
			
			/* hurrah for vendor prefixes! */
			$('span.arrow').css({'-webkit-transition':'-webkit-transform '+speedInMS+'s linear','-moz-transition':'-moz-transform '+speedInMS+'s linear','-o-transition':'-o-transform '+speedInMS+'s linear','-ms-transition':'-ms-transform '+speedInMS+'s linear','transition':'transform '+speedInMS+'s linear'});
		}
		
		/* let's have a hand icon when you hover over the headers */
		header.css({'cursor':'hand', 'cursor':'pointer'});
		
		/* click on the header... */
		header.click(function()
		{
			/* get the div under the header that was clicked on */
			var section = $(this).next('div');
			
			/* if the section is down */
			if(section.is(':visible'))
			{
				/* Hide the abstracts */
				$('.abstract').hide();
				$('.abstractlink').html('&#9660; Read abstract');

				/* slide up the div */
				section.slideUp(speed);
				
				/* if the browser supports transitions and transform do a neat animation on the arrow icon */
				if(Modernizr.csstransforms && Modernizr.csstransitions)
				{
					$(this).find('span.arrow').css({'-webkit-transform':'rotate(0deg)','-moz-transform':'rotate(0deg)','-o-transform':'rotate(0deg)','-ms-transform':'rotate(0deg)','transform':'rotate(0deg)'});
				}
				/* otherwise swap with a down arrow */
				else
				{
					//$(this).find('span.arrow').html('&#8615;');
					$(this).find('span.arrow').html('&#9660;');
				}
			}
			/* if the section isn't down */
			else
			{
				/* slide down the div */
				section.slideDown(speed);
				
				/* if the browser supports transitions and transform do a neat animation on the arrow icon */
				if(Modernizr.csstransforms && Modernizr.csstransitions)
				{
					$(this).find('span.arrow').css({'-webkit-transform':'rotate(180deg)','-moz-transform':'rotate(180deg)','-o-transform':'rotate(180deg)','-ms-transform':'rotate(180deg)','transform':'rotate(180deg)'});
				}
				/* otherwise swap with an up arrow */
				else
				{
					//$(this).find('span.arrow').html('&#8613;');
					$(this).find('span.arrow').html('&#9650;');
				}
			}
		});
	}
});