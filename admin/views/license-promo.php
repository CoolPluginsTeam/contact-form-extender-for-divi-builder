<?php
/**
 * License tab — free plugin: explains Pro licensing and links to purchase.
 *
 * @package Contact_Form_Extender_For_Divi_Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$cfefd_pro_pricing_url = 'https://coolplugins.net/product/contact-form-extender-for-divi-builder/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license_tab#pricing';
?>

<div class="cfefd-license-layout">
	<div class="cfefd-license-box">
		<div class="wrapper-header">
			<div class="cfefd-license-header-row">
				<div class="cfefd-title-desc">
					<h2><?php esc_html_e( 'License Key', 'contact-form-extender-for-divi-builder' ); ?></h2>
				</div>
				<div class="cfefd-save-controls">
					<span><?php esc_html_e( 'Free', 'contact-form-extender-for-divi-builder' ); ?></span>
					<a class="button button-primary upgrade-pro-btn" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $cfefd_pro_pricing_url ); ?>">
						<img class="crown-diamond-pro" src="<?php echo esc_url(CFEFD_PLUGIN_URL . 'admin/assets/images/crown-diamond-pro.png'); ?>" alt="Contact form extender">
						<?php esc_html_e( 'Upgrade To Pro', 'contact-form-extender-for-divi-builder' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="wrapper-body">
			<p class="cfefd-license-promo-intro">
				<?php esc_html_e( 'Your license key provides access to Pro version updates and support.', 'contact-form-extender-for-divi-builder' ); ?>
			</p>

			<p>
				<?php esc_html_e( "You're using ", 'contact-form-extender-for-divi-builder' ); ?>
				<strong><?php esc_html_e( 'Contact Form Extender for Divi (Free)', 'contact-form-extender-for-divi-builder' ); ?></strong>
				<?php esc_html_e( '- no license needed. Enjoy!😊', 'contact-form-extender-for-divi-builder' ); ?>
			</p>

			<div class="cfefd-license-upgrade-box">
				<p>
					<?php esc_html_e( 'To unlock more features, consider ', 'contact-form-extender-for-divi-builder' ); ?>
					<a href="<?php echo esc_url( $cfefd_pro_pricing_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'upgrading to Pro', 'contact-form-extender-for-divi-builder' ); ?></a>.
				</p>
				<em><?php esc_html_e( 'As a valued user, you automatically receive an exclusive discount on the Annual License and an even greater discount on the POPULAR Lifetime License at checkout!', 'contact-form-extender-for-divi-builder' ); ?></em>
			</div>
		</div>
	</div>

	<div class="cfefd-license-side">
		<div class="cfefd-sidebar-block">
			<h3><?php esc_html_e( 'Enjoying our plugin?', 'contact-form-extender-for-divi-builder' ); ?></h3>
			<p><?php esc_html_e( 'Please consider leaving us a review. It helps us a lot!', 'contact-form-extender-for-divi-builder' ); ?></p>
			<div class="cfefd-sidebar-link-group">
				<div class="cfefd-review-right">
					<div class="cfefd-stars" aria-hidden="true">★★★★★</div>
					<a href="https://wordpress.org/support/plugin/contact-form-extender-for-divi-builder/reviews/" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'contact-form-extender-for-divi-builder' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
