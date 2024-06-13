<?php 
// no direct access!
defined('ABSPATH') or die("No direct access");
?>

<div class="gsp_faq_wrapper">

	<div class="gsp_faq_inner">
		<div class="gsp-title">
			<h2>Frequently asked questions!</h2>
		</div>

		<ul class="gsp_faq_items_wrapper">

			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">What is Cloud Console?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						GSpeech Cloud Cosnole is the cloud based reliable system, which stores all your data and configuration.
						<ul class="gsp_faq_inner_ul">
							<li><span>It is integrated into your WP-Admin, and is being activated just by single click. Just go to <a href="<?php echo $console_url; ?>">Cloud Console</a> to get started.</span></li>
							<li><span>Also you can access it online, on your <a href="https://gspeech.io/dashboard" target="_blank">Online Dashboard</a>.</span></li>
							<li><span>You have access to detailed analytics, and many settings.</span></li>
						</ul>
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">What Player Types are supported?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						GSpeech supports four main player types!
						<ul class="gsp_faq_inner_ul">
							<li><span>Full Page Player.</li>
							<li><span>Button Player.</li>
							<li><span>Circle Player.</li>
							<li><span>RHT(Read Higlighted Text) Player.</li>
						</ul>
						Please check the <a href="https://gspeech.io/demos" target="_blank">GSpeech Demo</a> page, to see all player types in action.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">What is Read Higlighted Text(RHT) Player, how to use it?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						The RHT Player is a very nice accessibility feature for any website to improve website accessibility, which allows all your website visitors to listen to any selected text on the webiste.

						<ul class="gsp_faq_inner_ul">
							<li><span>It is being activated automatically after you activate the Cloud Console!</span></li>
							<li><span>It also shows animated banners, which informs your website visitors about that(TTS) feature active.</span></li>
							<li><span>You can adjust one of <b>16 different templates</b>, change banner position, texts, and much more.</span></li>
							<li><span>It has three possible views! Please check the following link, to see it in action: <a href="https://gspeech.io/blog/gspeech-read-highlighted-text-player" target="_blank">RHT Player Demo</a>.</span></li>
						</ul>
						To make the configuration, please go to: <b>Cloud Console->Widgets->RHT Player.</b> You will see the list of all options there.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">How to use Welcome Messages?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						Welcome Message is the audio greeting, which is being played once user visits your page.

						<ul class="gsp_faq_inner_ul">
							<li><span>The widget <b>Welcome Message</b> is being created automatically on Cloud Console activation, but it is <u>unpublished by default</u>!</span></li>
							<li><span>To activate it, please go to: <b><a href="<?php echo $console_url; ?>">Cloud Console</a>->Widgets->Welcome Message</b> and set <u>Status</u> to <u>Published</u>.</span></li>
							<li><span>To change the welcome message: in the same page go to <b>Content Tab</b> and change the <u>Content Text</u>.</span></li>
							<li><span>To play only once: Go to <b>Advanced Tab</b>, and set <u>Play Type</u> to <u>Once per session</u>.</span></li>
							<li><span>To set up only on some pages, use <u>Allowed Urls</u>(under <u>Content Tab</u>). Set one url per row. If you need only on homepage, place only url of homepage.</span></li>
						</ul>
						Please check the <a href="https://gspeech.io/demos" target="_blank">GSpeech Demo</a> page, to see Welcome Message in action.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">Which languages are supported?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						Here is the list: Arabic, Basque, Bengali, Bosnian, Bulgarian, Catalan, Chinese (Simplified), Chinese (Traditional), Croatian, Czech, Danish, Dutch, English (Australia), English (UK), English (India), English (United States), Estonian, Filipino, Finnish, French (Canada), French (France), Galician, German, Greek, Gujarati, Hindi, Hungarian, Icelandic, Indonesian, Italian, Japanese, Javanese, Kannada, Khmer, Korean, Latin, Latvian, Lithuanian, Malay, Malayalam, Marathi, Myanmar, Nepali, Norwegian, Polish, Portuguese (Brazil), Portuguese (Portugal), Romanian, Russian, Serbian, Sinhala, Slovak, Spanish (Spain), Spanish (United States), Sundanese (Indonesia), Swahili, Swedish, Tamil, Telugu, Thai, Turkish, Ukrainian, Urdu, Vietnamese, Yue Chinese.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">How to insert a player in a post or page or any article?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						The easiest way to insert players anywhere in content, is using shortcodes!

						<div class="gsp_faq_subtitle">Shortcode Structrue</div>
						<ul class="gsp_faq_inner_ul">
							<li><span>To insert the Full Page Player anywhere in content, use <b>[gspeech]</b> shortcode.</span></li>
							<li><span>To insert the Button Player anywhere in content, use <b>[gspeech-button]</b> shortcode.</span></li>
							<li><span>To insert the Circle Player anywhere in content, use <b>[gspeech-circle]</b> shortcode.</span></li>
						</ul>
						Please <a href="https://gspeech.io/contact-us" target="_blank">Contact Us</a> if you have any issues, and we will help to fix it!.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">How to automatically add players on multiple pages?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						To automatically add player on multiple pages, please go to: <b><a href="<?php echo $console_url; ?>">Cloud Console</a>->Widgets</b> and publish the <b>“multi-page”</b> audio widget(set <u>Status</u> to <u>Published</u>).
						<br />It usually works on most types of wordpress sites.
						<br /><br />Please <a href="https://gspeech.io/contact-us" target="_blank">Contact Us</a> and we will help to confgure it on your website.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">How to edit player settings, styles?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						To edit players settings, please go to: <b><a href="<?php echo $console_url; ?>">Cloud Console</a>->Widgets</b>.<br />
						Each of player has its widget there! Clicking on it will open it's settings.
					</div>
				</div>
			</li>
			<li class="gsp_faq_item">
				<div class="gsp_faq_header">
					<div class="gsp_faq_title">Do I need to pay for Google TTS and Translate API keys usage?</div>
					<div class="gsp_faq_icon"><span class="gsp_faq_icon_bar_1"></span><span class="gsp_faq_icon_bar_2"></span></div>
				</div>
				<div class="gsp_faq_body">
					<div class="gsp_faq_body_inner">
						No, all the <b>TTS Keys</b> and <b>Translation Keys</b> are being created automatically. For commercial plans all prices are included in the plan price!
					</div>
				</div>
			</li>
		</ul>
	</div>

	
</div>
