(function($) {

window.gspeechFront = function(options) {

    var $this = this,
        thisPage = this;

    this.initVars = function() {

        // get options
        this.options = options;
        this.is_touch_devise = 'ontouchstart' in window ? true : false;
        this.options.lazy_load_timeout = 400;

    };

    this.init = function() {

        this.initVars();

        this.runFunctions();
    };

    // Start ///////////////////////////////////////////////////////////////////////

    this.runFunctions = function() {

        this.applyCookies();

        this.applyFunctions();
    };

    this.applyFunctions = function() {

        if($("#gsp_data_html").length) {

            var $gsp_data_html = $("#gsp_data_html");

            var lazy_load = $gsp_data_html.data("lazy_load");
            var widget_id = $gsp_data_html.data("w_id");
            var v_ind = $gsp_data_html.data("vv_index");
            var s_enc = $gsp_data_html.data("s_enc");
            var h_enc = $gsp_data_html.data("h_enc");
            var hh_enc = $gsp_data_html.data("hh_enc");
        }
        else {

            if($("#wpgs-script777").length) {

                $("#wpgs-script777").attr("id", "wpgsp_front_script");
            }
            else { // for old wordpress versions, which does not set ID to scripts

                $("script").each(function(i) {

                    var src = $(this).attr("src");

                    if(src == undefined || src == '')
                        return;

                    if(src.match(/gspeech_front/g)) {

                        $(this).attr("id", "wpgsp_front_script");

                        return false;
                    }
                });
            }

            var $gspeech_cloud_data = $("#wpgsp_front_script");

            if(!$gspeech_cloud_data.length)
                return;

            var src = $gspeech_cloud_data.attr("src");

            src = src.replace(/gsp___del/gi, '\&');
            src = src.replace(/gsp___eq/gi, '\=');
            src = src.replace(/\?/gi, '\&');

            // create options array
            var gsp_opts = {};
            var gsp_opts_array = src.split("&");
            gsp_opts_array.forEach(function(option_item, i) {

                var opt_data = option_item.split("=");
                gsp_opts[opt_data[0]] = opt_data[1];
            });

            var lazy_load = gsp_opts["lazy_load"];
            var widget_id = gsp_opts["w_id"];
            var v_ind = gsp_opts["vv_index"];
            var s_enc = gsp_opts["s_enc"];
            var h_enc = gsp_opts["h_enc"];
            var hh_enc = gsp_opts["hh_enc"];
        }

        var load_timeout = lazy_load == 1 ? thisPage.options.lazy_load_timeout : 0;

        var $gspeech_widget_code = '<script id="gspeech_cloud_widget" defer src="https://gspeech.io/widget/'+widget_id+'?v_ind='+v_ind+'" data-widget_id="'+widget_id+'" data-s="'+s_enc+'" data-h="'+h_enc+'" data-hh="'+hh_enc+'"></script>';

        setTimeout(function() {

            $("body").append($gspeech_widget_code);

        }, load_timeout);
    };

    // Inner methods ///////////////////////////////////////////////////////////////////////

    this.bytesToHex = function(bytes) {

        var hexstring='', h;
        for(var i=0; i<bytes.length; i++) {
            h=bytes[i].toString(16);
            if(h.length==1) { h='0'+h; }
            hexstring+=h;
        }   
        return hexstring;        
    };

    this.applyCookies = function() {

        this.setCookie = function(key, value, expiry) {

            var cookie_val = key + '=' + value + ';path=/';
            if(expiry != -1) {
                var expires = new Date();
                expires.setTime(expires.getTime() + (expiry * 60 * 60 * 1000)); // in hours
                cookie_val += ';expires=' + expires.toUTCString();
            }
            document.cookie = cookie_val;
        };

        this.getCookie = function(key) {

            var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : '';
        };

        this.eraseCookie = function(key) {

            var keyValue = this.getCookie(key);
            this.setCookie(key, keyValue, '-2');
        };
    };

    // Call init ///////////////////////////////////////////////////////////////////////

    this.init();
};

$(document).ready(function() {

    // gspeech 3.x
    var gsp_options = {};
    window.gspeech_front = new gspeechFront(gsp_options);
});
})(jQuery);