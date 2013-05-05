(function(){
    var special = jQuery.event.special,
        uid1 = 'D' + (+new Date()),
        uid2 = 'D' + (+new Date() + 1);
        
    special.scrollstart = {
        setup: function() {
            
            var timer,
                handler =  function(evt) {
                    
                    var _self = this,
                        _args = arguments;
                    
                    if (timer) {
                        clearTimeout(timer);
                    } else {
                        evt.type = 'scrollstart';
                        jQuery.event.handle.apply(_self, _args);
                    }
                    
                    timer = setTimeout( function(){
                        timer = null;
                    }, special.scrollstop.latency);
                    
                };
            
            jQuery(this).bind('scroll', handler).data(uid1, handler);
            
        },
        teardown: function(){
            jQuery(this).unbind( 'scroll', jQuery(this).data(uid1) );
        }
    };
    
    special.scrollstop = {
        latency: 300,
        setup: function() {
            
            var timer,
                    handler = function(evt) {
                    
                    var _self = this,
                        _args = arguments;
                    
                    if (timer) {
                        clearTimeout(timer);
                    }
                    
                    timer = setTimeout( function(){
                        
                        timer = null;
                        evt.type = 'scrollstop';
                        jQuery.event.handle.apply(_self, _args);
                        
                    }, special.scrollstop.latency);
                    
                };
            
            jQuery(this).bind('scroll', handler).data(uid2, handler);
            
        },
        teardown: function() {
            jQuery(this).unbind( 'scroll', jQuery(this).data(uid2) );
        }
    };
    
})();


(function($) {

    $.fn.elipsesAnimation = function(options) {
        
        var settings = $.extend({}, { //defaults
            text: 'Loading',
            numDots: 3,
            delay: 300
        }, options);
        var currentText = "";
        var currentDots = 0;
        return this.each(function() {
            var $this = $(this);
            currentText = settings.text;
            $this.html(currentText);
            setTimeout(function() {
                addDots($this);
            }, settings.delay);
        });

        function addDots($elem) {
            if (currentDots >= settings.numDots) {
                currentDots = 0;
                currentText = settings.text;
            }
            currentText += ".";
            currentDots++;
            $elem.html(currentText);
            setTimeout(function() {
                addDots($elem);
            }, settings.delay);
        }
    }

})(jQuery);



