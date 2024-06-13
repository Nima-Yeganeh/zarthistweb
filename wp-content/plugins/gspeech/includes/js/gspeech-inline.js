(function($) {

$(document).ready(function() {

	var wp_ajax_url = wpgsp_ajax_obj.ajax_url;
	var wp_ajax_nonce = wpgsp_ajax_obj.nonce;
    
    $("body").on("click", "#toplevel_page_gspeech .wp-submenu a", function(e) {

        var href = $(this).attr("href");

        if(href.match(/gspeech_contact_us/g)) {

            e.preventDefault();

            window.open('https://gspeech.io/contact-us', '_blank');
        }
    });

    $("body").on("click", "#deactivate-gspeech", function(e) {

    	if($("#deactivate-gspeech").hasClass("gsp_dialog_opened"))
    		return;

		e.preventDefault();

		$("#deactivate-gspeech").addClass("gsp_dialog_opened");

		var dialog_html = wpgsp_get_dialog_html();

		$(".gsp_modal_wrapper").html(dialog_html);

		$(".gsp_dialog_overlay").addClass("gsp_vis");
		$(".gsp_modal_wrapper").addClass("gsp_vis");
		
		setTimeout(function() {

			$(".gsp_dialog_overlay").addClass("gsp_op_1");
			$(".gsp_modal_wrapper").addClass("gsp_op_1");

		}, 11);
    });

    $("body").on("click", ".gsp_reason_group", function(e) {

    	if($(this).hasClass("gsp_checked"))
    		return;

    	$(".gsp_checked").removeClass("gsp_checked");

    	setTimeout(function() {

    		$(".gsp_selected_reason:checked").parents(".gsp_reason").addClass("gsp_checked");

    	}, 5);

    	setTimeout(function() {

    		$(".gsp_checked").find("input").focus();
    		$(".gsp_checked").find("textarea").focus();

    	}, 10);
    });

    $("body").on("click", ".gsp_dialog_overlay", function(e) {

    	wpgsp_hide_dialog();
    });

    $("body").on("click", ".gsp_button_skip_deactivate", function(e) {

    	var deactivate_url = $("#deactivate-gspeech").attr("href");

    	window.location.href = deactivate_url;
    });

    $("body").on("click", ".gsp_button_submit_deactivate", function(e) {

    	var deactivate_url = $("#deactivate-gspeech").attr("href");

    	var sel_v = 0;
    	var sel_d = '';
    	if($(".gsp_selected_reason:checked").length) {

    		var $sel_v = $(".gsp_selected_reason:checked");
    		sel_v = $sel_v.val();

    		if($sel_v.parents(".gsp_reason").hasClass("gsp_has_input")) {

    			var $reason_input = $sel_v.parents(".gsp_reason").find('.gsp_reason_input')
    			if($reason_input.hasClass("gsp_text"))
    				sel_d = $reason_input.find("input").val();
    			else if($reason_input.hasClass("gsp_textarea"))
    				sel_d = $reason_input.find("textarea").val();
    		}
    	}

        var feedback_callback = function() {

        	// $('.gsp_ldg_wrapper').addClass("gsp_hidden");
        	window.location.href = deactivate_url;
        };

        var gspeech_feedback_interval = setInterval(function() {

        	feedback_callback();

        }, 7000);

        var data = {
			action: 'wpgsp_apply_feedback',
			_ajax_nonce: wp_ajax_nonce,
			sel_v: sel_v,
			sel_d: sel_d
		};

		$('.gsp_ldg_wrapper').removeClass("gsp_hidden");

		$.ajax
		({
			url: wp_ajax_url,
			type: "post",
			data: data,
			dataType: "json",
			success: function(data) {
				
            	clearInterval(gspeech_feedback_interval);
            	feedback_callback();
			},
			error: function(xhr, status, error) {
				
            	clearInterval(gspeech_feedback_interval);
            	feedback_callback();
			}
		});
    });

    function wpgsp_hide_dialog() {

    	$(".gsp_dialog_overlay").removeClass("gsp_op_1");
    	$(".gsp_modal_wrapper").removeClass("gsp_op_1");

    	setTimeout(function() {

			$(".gsp_dialog_overlay").removeClass("gsp_vis");
			$(".gsp_modal_wrapper").removeClass("gsp_vis");
			$(".gsp_modal_wrapper").html('');

			$("#deactivate-gspeech").removeClass("gsp_dialog_opened");

		}, 600);
	};

    function wpgsp_prepare_dialog() {

    	var dialog_html = '<div class="gsp_modal_wrapper"></div><div class="gsp_dialog_overlay"></div>';

    	$("body").prepend(dialog_html);
    };

    function wpgsp_get_dialog_html() {

    	var dialog_html = 
    		`
    			<div class="gsp_modal">	
					<div class="gsp_modal_dialog">
						<div class="gsp_modal_header">
							<div class="gsp_modal_logo"></div>
							<h4>Quick Feedback</h4>
						</div>
						<div class="gsp_modal_body">

							<div class="gsp_modal_body_inner">

								<h3>If you have a moment, please let us know why you are deactivating GSpeech:</h3>

								<ul class="gsp_reasons_list">

									<li class="gsp_reason">
										<div class="gsp_reason_group">
								            <input id="gsp_deacticvate_1" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="1">
									        <label for="gsp_deacticvate_1">I no longer need the plugin</label>
								        </div>
									</li>

									<li class="gsp_reason gsp_has_input">
										<div class="gsp_reason_group">
								            <input id="gsp_deacticvate_2" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="2">
									        <label for="gsp_deacticvate_2">The plugin didn't work as expected</label>
								        </div>
								        <div class="gsp_reason_input gsp_textarea">
											<textarea rows="4" maxlength="128" placeholder="Please share what did you expect?"></textarea>
										</div>
									</li>

									<li class="gsp_reason gsp_has_input">
										<div class="gsp_reason_group">
								            <input id="gsp_deacticvate_3" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="3">
									        <label for="gsp_deacticvate_3">I found a better plugin</label>
								        </div>
								        <div class="gsp_reason_input gsp_text">
											<input type="text" maxlength="128" placeholder="Please share which plugin">
										</div>
									</li>

									<li class="gsp_reason">
									    <div class="gsp_reason_group">
								            <input id="gsp_deacticvate_4" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="4">
									        <label for="gsp_deacticvate_4">I couldn't understand how to make it work</label>
								        </div>
									</li>

									<li class="gsp_reason gsp_has_input">
									    <div class="gsp_reason_group">
								            <input id="gsp_deacticvate_5" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="5">
									        <label for="gsp_deacticvate_5">The plugin is great, but I need specific feature that you don't support</label>
								        </div>
									    <div class="gsp_reason_input gsp_textarea">
											<textarea rows="4" maxlength="128" placeholder="Please share what feature?"></textarea>
										</div>
									</li>
									
									<li class="gsp_reason">
									    <div class="gsp_reason_group">
								            <input id="gsp_deacticvate_6" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="6">
									        <label for="gsp_deacticvate_6">It's a temporary deactivation</label>
								        </div>
									</li>

									<li class="gsp_reason gsp_has_input">
									    <div class="gsp_reason_group">
								            <input id="gsp_deacticvate_7" type="radio" name="gsp_selected_reason" class="gsp_selected_reason" value="7">
									        <label for="gsp_deacticvate_7">Other</label>
								        </div>
									    <div class="gsp_reason_input gsp_text">
									    	<div>Kindly tell us the reason so we can improve.</div>
											<input type="text" maxlength="128" placeholder="">
										</div>
									</li>

								</ul>
							</div>
						</div>

						<div class="gsp_modal_footer">
							<button class="gsp_button gsp_button_submit_deactivate">Submit &amp; Deactivate</button>
							<button class="gsp_button gsp_button_skip_deactivate">Skip &amp; Deactivate</button>
							<div class="gsp_ldg_wrapper gsp_hidden"><span class="gsp_ldg"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M456.433 371.72l-27.79-16.045c-7.192-4.152-10.052-13.136-6.487-20.636 25.82-54.328 23.566-118.602-6.768-171.03-30.265-52.529-84.802-86.621-144.76-91.424C262.35 71.922 256 64.953 256 56.649V24.56c0-9.31 7.916-16.609 17.204-15.96 81.795 5.717 156.412 51.902 197.611 123.408 41.301 71.385 43.99 159.096 8.042 232.792-4.082 8.369-14.361 11.575-22.424 6.92z"></path></svg></span></div>
						</div>	

					</div>
				</div>
    		`;

		return dialog_html;
    };

    wpgsp_prepare_dialog();
    
});

})(jQuery);