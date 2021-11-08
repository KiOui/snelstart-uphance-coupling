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
        <a class="button button-secondary" href="/wp-admin/admin.php?page=suc_admin_menu&do_cron=1">
            <?php echo __( 'Synchronize invoices now', 'snelstart-uphance-coupling' ); ?>
        </a>
        <a class="button button-secondary" href="/wp-admin/admin.php?page=suc_admin_menu&clear_logs=1">
			<?php echo __( 'Clear all log messages', 'snelstart-uphance-coupling' ); ?>
        </a>
	</form>
</div>