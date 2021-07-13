<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PremiumAddons\Includes\Helper_Functions;

$support_url = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/support/', 'about-page', 'wp-dash', 'dashboard' );

?>

<div class="pa-section-content">
	<div class="row">
		<div id="pa-general-settings" class="pa-settings-tab">
			<div class="col-half">
				<div class="pa-section-info-wrap">

					<div class="pa-section-info">
					<h4>
						<i class="dashicons dashicons-info abt-icon-style"></i>
						<?php echo __( 'What is Premium Addons?', 'premium-addons-for-elementor' ); ?>
					</h4>
					<p><?php echo __( 'Premium Addons for Elementor extends Elementor Page Builder capabilities with many fully customizable widgets and addons that help you to build impressive websites with no coding required.', 'premium-addons-for-elementor' ); ?></p>
					</div>
				</div>
			</div>

			<div class="col-half">
				<div class="pa-section-info-wrap">
					<div class="pa-section-info">
						<h4>
							<i class="dashicons dashicons-universal-access-alt abt-icon-style"></i>
							<?php echo __( 'Docs and Support', 'premium-addons-for-elementor' ); ?>
						</h4>
						<p><?php echo __( 'It’s highly recommended to check out documentation and FAQ before using this plugin. ', 'premium-addons-for-elementor' ); ?><a target="_blank" href="<?php echo esc_url( $support_url ); ?>"><?php echo __( 'Click Here', 'premium-addons-for-elementor' ); ?></a><?php echo __( ' for more details. You can also join our ', 'premium-addons-for-elementor' ); ?><a href="https://www.facebook.com/groups/PremiumAddons" target="_blank"><?php echo __( 'Facebook Group', 'premium-addons-for-elementor' ); ?></a><?php echo __( ' and Our ', 'premium-addons-for-elementor' ); ?><a href="https://my.leap13.com/forums/" target="_blank"><?php echo __( 'Community Forums', 'premium-addons-for-elementor' ); ?></a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div> <!-- End Section Content -->
