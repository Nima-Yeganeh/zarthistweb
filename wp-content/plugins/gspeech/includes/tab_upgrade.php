<?php 
// no direct access!
defined('ABSPATH') or die("No direct access");
$img_path = plugins_url( '/images/svg/' , __FILE__ );
?>

<div class="gsp_upgrade_wrapper">

	<div class="plans_holder">
		<div id="dashboard_header_2">
			<div class="gsp-title">
				<h2>Plans</h2>
			</div>
			<div class="dashboard_subtitle">You are subscribed to the <span class="plan_title">Free</span> plan</div>
		</div>

		<div class="change_plan">
			<div class="plan_title_monthly">monthly</div>
			<div class="plan_switcher">
				<div class="plan_switcher_button"></div>
				<div class="plan_switcher_bar"></div>
			</div>
			<div class="plan_title_yearly">
				<span>yearly</span>
				<span class="m11">(2 months free)</span>
			</div>
		</div>

		<div class="gspeech_packages_wrapper">

			<div class="gsp_package_wrapper gsp_package_free">
				<div class="gsp_package_wrapper_inner">
					<div class="package_header">
						<h3 class="gsp_package_title">Free</h3>
						<div class="gsp_package_price">
							<div class="gsp_package_price_inner">
								<span class="price_span_c">$</span>
								<span class="price_span_1">0</span>
							</div>
							<div class="gsp_package_price_inner gsp_package_price_inner_y">
								<span class="price_span_c">$</span>
								<span class="price_span_1">0</span>
							</div>
						</div>

						<div class="header_toggler"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 294.2v-76.4c0-13.3-10.7-24-24-24H286.2V56c0-13.3-10.7-24-24-24h-76.4c-13.3 0-24 10.7-24 24v137.8H24c-13.3 0-24 10.7-24 24v76.4c0 13.3 10.7 24 24 24h137.8V456c0 13.3 10.7 24 24 24h76.4c13.3 0 24-10.7 24-24V318.2H424c13.3 0 24-10.7 24-24z"></path></svg></div>
					</div>

					<div class="package_body">

						<div class="pricing_table_row pricing_table_row_2">
							<ul class="feature_ul">
								<li class="title_holder_vertical">
									<span class="icon_mach_voice">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M320 0c17.7 0 32 14.3 32 32V96H480c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64H288V32c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H208zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H304zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H400zM264 256c0-22.1-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40s40-17.9 40-40zm152 40c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40zM48 224H64V416H48c-26.5 0-48-21.5-48-48V272c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H576V224h16z"></path></svg>
									</span>
									<span class="feature_text">Machine Voices</span>
									<span class="questions_icon">
										<img src="<?php echo $img_path;?>info.svg" />
									</span>
									<span class="gs_title_vertical"><span class="title_v_subtitle">Machine Voices:</span> Machine Voices</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 21 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
									</span>
									
									<span class="feature_text">10 Websites</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">50k Characters/М</span>
								</li>
								<li class="pack_divider">
									<div class="pack_line"></div>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">No Server Requirements</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Personal Dashboard</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Mobile Supported</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">50 Languages</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">220+ Voices</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Voice Tuning</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Multi-lang Websites</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">TTS Aliases</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Text Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Context Player</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Play Statistics</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">No Downloads</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Custom Themes</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Voice Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Translate Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Advanced Analytics</span>
								</li>
							</ul>
						</div>

						<div class="pricing_table_row pricing_table_row_3">
							<div class="product_button" data-url="register">Active</div>
							<div class="button_info">No credit card required</div>
						</div>
					</div>
				</div>
			</div>
			<div class="gsp_package_wrapper gsp_package_personal" data-plan_id_m="" data-plan_id_y="">
				<div class="gsp_package_wrapper_inner">
					<div class="package_header">
						<h3 class="gsp_package_title">Personal</h3>
						<div class="gsp_package_price">
							<div class="gsp_package_price_inner">
								<span class="price_span_c">$</span>
								<span class="price_span_1">9</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.99</span>
									<span class="price_span_3">/month</span>
								</span>
							</div>
							<div class="gsp_package_price_inner gsp_package_price_inner_y">
								<span class="price_span_c">$</span>
								<span class="price_span_1">99</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.90</span>
									<span class="price_span_3">/year</span>
								</span>
							</div>
						</div>

						<div class="header_toggler"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 294.2v-76.4c0-13.3-10.7-24-24-24H286.2V56c0-13.3-10.7-24-24-24h-76.4c-13.3 0-24 10.7-24 24v137.8H24c-13.3 0-24 10.7-24 24v76.4c0 13.3 10.7 24 24 24h137.8V456c0 13.3 10.7 24 24 24h76.4c13.3 0 24-10.7 24-24V318.2H424c13.3 0 24-10.7 24-24z"></path></svg></div>
					</div>

					<div class="package_body">
						<div class="pricing_table_row pricing_table_row_2">
							<ul class="feature_ul">
								<li>
									<span class="icon_ai_voice icon_hidden">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg>
									</span>
									<span class="icon_mach_voice">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M320 0c17.7 0 32 14.3 32 32V96H480c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64H288V32c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H208zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H304zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H400zM264 256c0-22.1-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40s40-17.9 40-40zm152 40c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40zM48 224H64V416H48c-26.5 0-48-21.5-48-48V272c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H576V224h16z"></path></svg>
									</span>
									<span class="feature_text feature_text_ai txt_hidden">AI Voices</span>
									<span class="feature_text feature_text_machine">Machine Voices</span>

									<div class="switcher_small switcher_voice_type">
										<div class="switcher_small_button"></div>
										<div class="switcher_small_bar"></div>
									</div>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 21 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
									</span>
									
									<span class="feature_text">1 Website</span>
								</li>
								<li style="height: 13px">
									<span class="icon_holder_p icon_h_ch_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">100k</span>

									<div class="switcher_small switcher_small_button_on switcher_chars_count">
										<div class="switcher_small_button"></div>
										<div class="switcher_small_bar"></div>
									</div>

									<span class="icon_holder_p icon_h_ch_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">1M</span>
								</li>
								<li class="pack_divider">
									<div class="pack_line"></div>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">No Server Requirements</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Personal Dashboard</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Mobile Supported</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text"><span class="pers_l_67 icon_hidden">67</span><span class="pers_l_50">50</span> Languages</span>
								</li>
								
								<li>
									<span class="feature_icon_no"><img class="pers_22_v_plus icon_hidden" src="<?php echo $img_path;?>checked.svg" /><img class="pers_22_v_minus" src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">220+ Voices</span>
								</li>
								<li>
									<span class="feature_icon_no"><img class="pers_vt_ic_plus icon_hidden" src="<?php echo $img_path;?>checked.svg" /><img class="pers_vt_ic_minus" src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Voice Tuning</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Multi-lang Websites</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">TTS Aliases</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Text Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Context Player</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Play Statistics</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Unlimited Downloads</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Custom Themes</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Voice Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Translate Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Advanced Analytics</span>
								</li>
							</ul>
						</div>

						<div class="pricing_table_row pricing_table_row_3">
							<a class="product_button product_button_personal" href="https://gspeech.io/subscribe/personal/monthly" data-m_p="personal/monthly" data-y_p="personal/yearly" target="_blank">Sign Up Now</a>
							<a class="product_button product_button_personal_2 icon_hidden" href="https://gspeech.io/subscribe/personal-2/monthly" data-m_p="personal-2/monthly" data-y_p="personal-2/yearly" target="_blank">Sign Up Now</a>
						</div>
					</div>
				</div>
			</div>
			<div class="gsp_package_wrapper gsp_package_pro" data-plan_id_m="" data-plan_id_y="">
				<div class="gsp_package_wrapper_inner">
					<div class="package_header">
						<h3 class="gsp_package_title">Pro</h3>
						<div class="gsp_package_price">
							<div class="gsp_package_price_inner">
								<span class="price_span_c">$</span>
								<span class="price_span_1">39</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.99</span>
									<span class="price_span_3">/month</span>
								</span>
							</div>
							<div class="gsp_package_price_inner gsp_package_price_inner_y">
								<span class="price_span_c">$</span>
								<span class="price_span_1">399</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.90</span>
									<span class="price_span_3">/year</span>
								</span>
							</div>
						</div>

						<div class="header_toggler"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 294.2v-76.4c0-13.3-10.7-24-24-24H286.2V56c0-13.3-10.7-24-24-24h-76.4c-13.3 0-24 10.7-24 24v137.8H24c-13.3 0-24 10.7-24 24v76.4c0 13.3 10.7 24 24 24h137.8V456c0 13.3 10.7 24 24 24h76.4c13.3 0 24-10.7 24-24V318.2H424c13.3 0 24-10.7 24-24z"></path></svg></div>
					</div>

					<div class="package_body">
						<div class="pricing_table_row pricing_table_row_2">
							<ul class="feature_ul">
								<li>
									<span class="icon_ai_voice">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg>
									</span>
									<span class="feature_text">AI Voices</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 21 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
									</span>
									
									<span class="feature_text">3 Websites</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">1M Characters/M</span>
								</li>
								<li class="pack_divider">
									<div class="pack_line"></div>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">No Server Requirements</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Personal Dashboard</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Mobile Supported</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">67 Languages</span>
								</li>
								
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">220+ Voices</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Voice Tuning</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Multi-lang Websites</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">TTS Aliases</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Text Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Context Player</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Play Statistics</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Unlimited Downloads</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Custom Themes</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Voice Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Translate Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Advanced Analytics</span>
								</li>
							</ul>
						</div>

						<div class="pricing_table_row pricing_table_row_3">
							<a class="product_button" href="https://gspeech.io/subscribe/pro/monthly" data-m_p="pro/monthly" data-y_p="pro/yearly" target="_blank">Sign Up Now</a>
						</div>
					</div>
				</div>
			</div>
			<div class="gsp_package_wrapper gsp_package_business" data-plan_id_m="" data-plan_id_y="">
				<div class="gsp_package_wrapper_inner">
					<div class="package_header">
						<h3 class="gsp_package_title">Business</h3>
						<div class="gsp_package_price">
							<div class="gsp_package_price_inner">
								<span class="price_span_c">$</span>
								<span class="price_span_1">79</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.99</span>
									<span class="price_span_3">/month</span>
								</span>
							</div>
							<div class="gsp_package_price_inner gsp_package_price_inner_y">
								<span class="price_span_c">$</span>
								<span class="price_span_1">799</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.90</span>
									<span class="price_span_3">/year</span>
								</span>
							</div>
						</div>

						<div class="header_toggler"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 294.2v-76.4c0-13.3-10.7-24-24-24H286.2V56c0-13.3-10.7-24-24-24h-76.4c-13.3 0-24 10.7-24 24v137.8H24c-13.3 0-24 10.7-24 24v76.4c0 13.3 10.7 24 24 24h137.8V456c0 13.3 10.7 24 24 24h76.4c13.3 0 24-10.7 24-24V318.2H424c13.3 0 24-10.7 24-24z"></path></svg></div>
					</div>

					<div class="package_body">
						<div class="pricing_table_row pricing_table_row_2">
							<ul class="feature_ul">
								<li>
									<span class="icon_ai_voice">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg>
									</span>
									<span class="feature_text">AI Voices</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 21 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
									</span>
									
									<span class="feature_text">5 Websites</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">2M Characters/M</span>
								</li>
								<li class="pack_divider">
									<div class="pack_line"></div>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">No Server Requirements</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Personal Dashboard</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Mobile Supported</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">67 Languages</span>
								</li>
								
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">220+ Voices</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Voice Tuning</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Multi-lang Websites</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">TTS Aliases</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Text Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Context Player</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Play Statistics</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Unlimited Downloads</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Custom Themes</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Voice Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Translate Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>x.svg" /></span>
									<span class="feature_text">Advanced Analytics</span>
								</li>
							</ul>
						</div>

						<div class="pricing_table_row pricing_table_row_3">
							<a class="product_button" href="https://gspeech.io/subscribe/business/monthly" data-m_p="business/monthly" data-y_p="business/yearly" target="_blank">Sign Up Now</a>
						</div>
					</div>
				</div>
			</div>
			<div class="gsp_package_wrapper gsp_package_enterprise" data-plan_id_m="" data-plan_id_y="">
				<div class="gsp_package_wrapper_inner">
					<div class="package_header">
						<h3 class="gsp_package_title">Enterprise</h3>
						<div class="gsp_package_price">
							<div class="gsp_package_price_inner">
								<span class="price_span_c">$</span>
								<span class="price_span_1">129</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.99</span>
									<span class="price_span_3">/month</span>
								</span>
							</div>
							<div class="gsp_package_price_inner gsp_package_price_inner_y">
								<span class="price_span_c">$</span>
								<span class="price_span_1">1299</span>
								<span class="price_span_2_v">
									<span class="price_span_2">.90</span>
									<span class="price_span_3">/year</span>
								</span>
							</div>
						</div>

						<div class="header_toggler"><svg role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 294.2v-76.4c0-13.3-10.7-24-24-24H286.2V56c0-13.3-10.7-24-24-24h-76.4c-13.3 0-24 10.7-24 24v137.8H24c-13.3 0-24 10.7-24 24v76.4c0 13.3 10.7 24 24 24h137.8V456c0 13.3 10.7 24 24 24h76.4c13.3 0 24-10.7 24-24V318.2H424c13.3 0 24-10.7 24-24z"></path></svg></div>
					</div>

					<div class="package_body">
						<div class="pricing_table_row pricing_table_row_2">
							<ul class="feature_ul">
								<li>
									<span class="icon_ai_voice">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg>
									</span>
									<span class="feature_text">AI Voices</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64H348.7c2.2 20.4 3.3 41.8 3.3 64zm28.8-64H503.9c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 21 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
									</span>
									
									<span class="feature_text">10 Websites</span>
								</li>
								<li>
									<span class="icon_holder_p">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM112 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
									</span>
									<span class="feature_text">5M Characters/M</span>
								</li>
								<li class="pack_divider">
									<div class="pack_line"></div>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">No Server Requirements</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Personal Dashboard</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Mobile Supported</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">67 Languages</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">220+ Voices</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Voice Tuning</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Multi-lang Websites</span>
								</li>
								<li>
									<span class="feature_icon_yes"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">TTS Aliases</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Text Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Context Player</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Play Statistics</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Unlimited Downloads</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Custom Themes</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Voice Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Translate Panel</span>
								</li>
								<li>
									<span class="feature_icon_no"><img src="<?php echo $img_path;?>checked.svg" /></span>
									<span class="feature_text">Advanced Analytics</span>
								</li>
							</ul>
						</div>

						<div class="pricing_table_row pricing_table_row_3">
							<a class="product_button" href="https://gspeech.io/subscribe/enterprise/monthly" data-m_p="enterprise/monthly" data-y_p="enterprise/yearly" target="_blank">Sign Up Now</a>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	
</div>
