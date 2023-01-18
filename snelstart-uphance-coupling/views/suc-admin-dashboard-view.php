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
	<form action='/wp-admin/admin.php?page=suc_admin_menu' method='post'>
		<?php
		settings_fields( 'suc_settings' );
		do_settings_sections( 'suc_admin_menu' );
		submit_button();
		?>
		<a class="button button-secondary" href="/wp-admin/admin.php?page=suc_admin_menu&do_cron=1">
			<?php echo esc_html( __( 'Synchronize now', 'snelstart-uphance-coupling' ) ); ?>
		</a>
	</form>
	<style>
		label {
			width: 100%;
		}
	</style>
</div>
