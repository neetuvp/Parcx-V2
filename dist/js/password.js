/* === password strength === */

$(document).ready(function () {            
    "use strict";   
    var options = {};
    options.ui = {
        container: "#pwd-container",
        viewports: {
            progress: ".pwstrength_viewport_progress"
        },
        showPopover: true,
        showErrors: true
    };
    options.common = {
        debug: false, // check password strength in console
        onKeyUp: function (evt, data) {             
            if ($(".error-list li").length > 0) 
                {
                $(".popover-body div").show();
                $("input[type='submit']").prop('disabled', true);                      
                } 
            else 
                {
                $(".popover-body div").hide();
                $("input[type='submit']").prop('disabled', false); 
                }                                     
        },
    };    
    $('#password').pwstrength(options); 
    
    
    var options2 = {};
    options2.ui = {
        container: "#reset-pwd-container",
        viewports: {
            progress: ".pwstrength_viewport_progress"
        },
        showPopover: true,
        showErrors: true
    };
    options2.common = {
        debug: false, // check password strength in console
        onKeyUp: function (evt, data) {             
            if ($(".error-list li").length > 0) 
                {
                $(".popover-body div").show();
                $("input[type='submit']").prop('disabled', true);                      
                } 
            else 
                {
                $(".popover-body div").hide();
                $("input[type='submit']").prop('disabled', false); 
                }                                     
        },
    };    
    $('#new_password').pwstrength(options2); 
    
});