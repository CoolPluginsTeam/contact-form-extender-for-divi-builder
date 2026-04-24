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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$cfefd_support_url   = 'https://coolplugins.net/support/?utm_source=cfefd_plugin&utm_medium=inside&utm_campaign=support&utm_content=license_dashboard';
?>

<div class="cfefd-license-layout">
	<div class="cfefd-license-box">
		<div class="wrapper-header">
			<h3><?php esc_html_e( 'Divi Contact Form Extender — Pro licensing', 'contact-form-extender-for-divi-builder' ); ?></h3>
		</div>

		<div class="wrapper-body">
			<p class="cfefd-license-promo-intro">
				<?php esc_html_e( 'Automatic updates and priority support for the full Pro feature set are unlocked with a license in the premium plugin.', 'contact-form-extender-for-divi-builder' ); ?>
			</p>

			<div class="cfefd-license-field">
				<label for="cfefd-license-key-demo"><?php esc_html_e( 'License key', 'contact-form-extender-for-divi-builder' ); ?></label>
				<input type="text" id="cfefd-license-key-demo" value="" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" disabled="disabled" autocomplete="off" />
			</div>

			<div class="cfefd-license-field">
				<label for="cfefd-license-email-demo"><?php esc_html_e( 'Email', 'contact-form-extender-for-divi-builder' ); ?></label>
				<input type="email" id="cfefd-license-email-demo" value="" size="50" placeholder="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>" disabled="disabled" autocomplete="off" />
			</div>

			<p class="cfefd-license-promo-note">
				<?php esc_html_e( 'After you purchase Pro, install the premium plugin and enter your license key on its License screen to activate updates and support.', 'contact-form-extender-for-divi-builder' ); ?>
			</p>

			<div class="cfefd-license-promo-cta">
				<a class="button button-primary button-large" href="<?php echo esc_url( $cfefd_pro_pricing_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get Pro & license', 'contact-form-extender-for-divi-builder' ); ?>
				</a>
			</div>

			<p><?php esc_html_e( 'The free plugin does not require a license key.', 'contact-form-extender-for-divi-builder' ); ?></p>

			<div class="cfefd-license-support">
				<p><?php esc_html_e( 'Already purchased? Manage your license in your Cool Plugins account.', 'contact-form-extender-for-divi-builder' ); ?></p>
				<div class="cfefd-support-buttons">
					<a href="https://my.coolplugins.net/account/" class="button button-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'My account', 'contact-form-extender-for-divi-builder' ); ?></a>
					<a href="<?php echo esc_url( $cfefd_support_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact support', 'contact-form-extender-for-divi-builder' ); ?></a>
				</div>
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
