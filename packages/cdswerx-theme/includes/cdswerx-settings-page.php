<?php
/**
 * CDSWerx Theme Settings Page
 * 
 * White-label settings functionality equivalent to Hello Elementor's advanced settings
 * 
 * @package CDSWerxElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get current page to determine if we're on settings
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
$is_settings_page = ( $current_page === 'cdswerx-theme-settings' );

if ( $is_settings_page ) {
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'CDSWerx Theme Settings', 'cdswerx-theme' ); ?></h1>
	
	<div class="cdswerx-settings-container">
		<div class="cdswerx-settings-header">
			<h2><?php echo esc_html__( 'Advanced theme settings', 'cdswerx-theme' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Advanced settings are available for experienced users and developers. If you\'re unsure about a setting, we recommend keeping the default option.', 'cdswerx-theme' ); ?>
			</p>
		</div>

		<div class="cdswerx-settings-tabs">
			<nav class="nav-tab-wrapper">
				<a href="#seo-accessibility" class="nav-tab nav-tab-active" data-tab="seo-accessibility">
					<?php echo esc_html__( 'SEO and accessibility', 'cdswerx-theme' ); ?>
				</a>
				<a href="#structure-layout" class="nav-tab" data-tab="structure-layout">
					<?php echo esc_html__( 'Structure and layout', 'cdswerx-theme' ); ?>
				</a>
				<a href="#css-styling" class="nav-tab" data-tab="css-styling">
					<?php echo esc_html__( 'CSS and styling control', 'cdswerx-theme' ); ?>
				</a>
			</nav>
		</div>

		<form method="post" action="options.php" class="cdswerx-settings-form">
			<?php
			settings_fields( 'cdswerx_theme_settings' );
			do_settings_sections( 'cdswerx_theme_settings' );
			?>

			<!-- Structure and Layout Tab -->
			<div id="structure-layout" class="cdswerx-tab-content" style="display: none;">
				<div class="cdswerx-setting-group">
					<h3><?php echo esc_html__( 'These settings relate to the structure of your pages.', 'cdswerx-theme' ); ?></h3>
					
					<!-- Disable theme header and footer -->
					<div class="cdswerx-setting-item">
						<label class="cdswerx-toggle">
							<input type="checkbox" name="cdswerx_disable_header_footer" value="1" <?php checked( get_option( 'cdswerx_disable_header_footer', false ) ); ?>>
							<span class="cdswerx-toggle-slider"></span>
						</label>
						<div class="cdswerx-setting-content">
							<h4><?php echo esc_html__( 'Disable theme header and footer', 'cdswerx-theme' ); ?></h4>
							<p class="description">
								<?php echo esc_html__( 'What it does: Removes the theme\'s default header and footer sections from every page, along with their associated CSS/JavaScript.', 'cdswerx-theme' ); ?>
							</p>
							<p class="description">
								<strong><?php echo esc_html__( 'Tip:', 'cdswerx-theme' ); ?></strong>
								<?php echo esc_html__( 'If you use a plugin like Elementor Pro for your headers and footers, disable the theme header and footer to improve performance.', 'cdswerx-theme' ); ?>
							</p>
							<div class="cdswerx-code-example">
								<code>&lt;header id="site-header" class="site-header"&gt; ... &lt;/header&gt; &lt;footer id="site-footer" class="site-footer"&gt; ... &lt;/footer&gt;</code>
							</div>
						</div>
					</div>

					<!-- Hide page title -->
					<div class="cdswerx-setting-item">
						<label class="cdswerx-toggle">
							<input type="checkbox" name="cdswerx_hide_page_title" value="1" <?php checked( get_option( 'cdswerx_hide_page_title', false ) ); ?>>
							<span class="cdswerx-toggle-slider"></span>
						</label>
						<div class="cdswerx-setting-content">
							<h4><?php echo esc_html__( 'Hide page title', 'cdswerx-theme' ); ?></h4>
							<p class="description">
								<?php echo esc_html__( 'What it does: Removes the main page title above your page content.', 'cdswerx-theme' ); ?>
							</p>
							<p class="description">
								<strong><?php echo esc_html__( 'Tip:', 'cdswerx-theme' ); ?></strong>
								<?php echo esc_html__( 'If you do not want to display page titles or are using Elementor widgets to display your page titles, hide the page title.', 'cdswerx-theme' ); ?>
							</p>
							<div class="cdswerx-code-example">
								<code>&lt;div class="page-header"&gt;&lt;h1 class="entry-title"&gt;Post title&lt;/h1&gt;&lt;/div&gt;</code>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- SEO and Accessibility Tab -->
			<div id="seo-accessibility" class="cdswerx-tab-content cdswerx-tab-active">
				<div class="cdswerx-setting-group">
					<h3><?php echo esc_html__( 'These settings help improve your site\'s SEO and accessibility.', 'cdswerx-theme' ); ?></h3>
					
					<!-- Skip link -->
					<div class="cdswerx-setting-item">
						<label class="cdswerx-toggle">
							<input type="checkbox" name="cdswerx_skip_link" value="1" <?php checked( get_option( 'cdswerx_skip_link', true ) ); ?>>
							<span class="cdswerx-toggle-slider"></span>
						</label>
						<div class="cdswerx-setting-content">
							<h4><?php echo esc_html__( 'Skip link', 'cdswerx-theme' ); ?></h4>
							<p class="description">
								<?php echo esc_html__( 'What it does: Adds a "Skip to content" link that appears when users navigate using the Tab key.', 'cdswerx-theme' ); ?>
							</p>
							<p class="description">
								<strong><?php echo esc_html__( 'Tip:', 'cdswerx-theme' ); ?></strong>
								<?php echo esc_html__( 'This improves accessibility by allowing keyboard users to quickly jump to the main content.', 'cdswerx-theme' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- CSS and Styling Tab -->
			<div id="css-styling" class="cdswerx-tab-content" style="display: none;">
				<div class="cdswerx-setting-group">
					<h3><?php echo esc_html__( 'These settings control CSS output and styling behavior.', 'cdswerx-theme' ); ?></h3>
					
					<!-- Custom CSS optimization -->
					<div class="cdswerx-setting-item">
						<label class="cdswerx-toggle">
							<input type="checkbox" name="cdswerx_css_optimization" value="1" <?php checked( get_option( 'cdswerx_css_optimization', true ) ); ?>>
							<span class="cdswerx-toggle-slider"></span>
						</label>
						<div class="cdswerx-setting-content">
							<h4><?php echo esc_html__( 'Enable CSS optimization', 'cdswerx-theme' ); ?></h4>
							<p class="description">
								<?php echo esc_html__( 'What it does: Optimizes CSS loading and reduces render-blocking resources.', 'cdswerx-theme' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<?php submit_button( __( 'Save Changes', 'cdswerx-theme' ), 'primary', 'submit', true, array( 'id' => 'cdswerx-settings-save' ) ); ?>
		</form>
	</div>
</div>

<style>
.cdswerx-settings-container {
	max-width: 1000px;
	margin: 20px 0;
}

.cdswerx-settings-header {
	margin-bottom: 30px;
}

.cdswerx-settings-header h2 {
	color: #1d2327;
	font-size: 24px;
	margin: 0 0 10px 0;
}

.cdswerx-settings-tabs .nav-tab-wrapper {
	margin-bottom: 20px;
}

.cdswerx-tab-content {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 20px;
	display: none;
}

.cdswerx-tab-content.cdswerx-tab-active {
	display: block;
}

.cdswerx-setting-group h3 {
	color: #1d2327;
	font-size: 16px;
	margin: 0 0 20px 0;
}

.cdswerx-setting-item {
	display: flex;
	align-items: flex-start;
	gap: 15px;
	margin-bottom: 30px;
	padding-bottom: 30px;
	border-bottom: 1px solid #f0f0f1;
}

.cdswerx-setting-item:last-child {
	border-bottom: none;
	margin-bottom: 0;
	padding-bottom: 0;
}

.cdswerx-toggle {
	position: relative;
	display: inline-block;
	width: 50px;
	height: 24px;
	flex-shrink: 0;
	margin-top: 3px;
}

.cdswerx-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}

.cdswerx-toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .4s;
	border-radius: 24px;
}

.cdswerx-toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .4s;
	border-radius: 50%;
}

.cdswerx-toggle input:checked + .cdswerx-toggle-slider {
	background-color: #2196F3;
}

.cdswerx-toggle input:checked + .cdswerx-toggle-slider:before {
	transform: translateX(26px);
}

.cdswerx-setting-content h4 {
	color: #1d2327;
	font-size: 16px;
	margin: 0 0 10px 0;
}

.cdswerx-setting-content .description {
	margin: 5px 0;
	color: #646970;
}

.cdswerx-code-example {
	background: #f6f7f7;
	border: 1px solid #dcdcde;
	border-radius: 4px;
	padding: 10px;
	margin-top: 10px;
}

.cdswerx-code-example code {
	font-family: Consolas, Monaco, 'Courier New', monospace;
	font-size: 12px;
	color: #646970;
}

#cdswerx-settings-save {
	margin-top: 30px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab switching functionality
	$('.nav-tab').on('click', function(e) {
		e.preventDefault();
		
		// Remove active class from all tabs and content
		$('.nav-tab').removeClass('nav-tab-active');
		$('.cdswerx-tab-content').removeClass('cdswerx-tab-active').hide();
		
		// Add active class to clicked tab and show content
		$(this).addClass('nav-tab-active');
		var tabId = $(this).data('tab');
		$('#' + tabId).addClass('cdswerx-tab-active').show();
	});

	// Ensure first tab is visible on page load
	if ($('.cdswerx-tab-content:visible').length === 0) {
		$('#seo-accessibility').addClass('cdswerx-tab-active').show();
	}
});
</script>

<?php
}
?>