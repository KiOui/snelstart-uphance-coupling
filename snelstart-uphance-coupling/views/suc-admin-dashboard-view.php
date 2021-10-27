<?php
/**
 * Admin Dashboard View.
 *
 * @package snelstart-uphance-coupling
 */

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Snelstart Uphance Coupling Dashboard', 'snelstart-uphance-coupling' ); ?></h1>
	<hr class="wp-header-end">
	<p><?php esc_html_e( 'Snelstart Uphance Coupling settings', 'snelstart-uphance-coupling' ); ?></p>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'suc_settings' );
		do_settings_sections( 'suc_settings' );
		submit_button();
		?>
	</form>
    <p>
        <input disabled value="<?php echo get_option('suc_token_info')['access_token'] ?>"/>
        <input disabled value="<?php echo get_option('suc_token_info')['expires_at'] ?>"/>
    </p>
</div>