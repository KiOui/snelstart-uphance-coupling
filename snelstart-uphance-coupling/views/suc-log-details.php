<?php
/**
 * Single SUCLogMessage view.
 *
 * @package snelstart-uphance-coupling
 */

global $post;

if ( isset( $post ) ) :
	$log_messages = get_post_meta( $post->ID, 'suc_log_messages_json', true );
	?>
		<p><?php echo esc_html( $post->post_date ); ?></p>
		<code>
			<?php foreach ( $log_messages as $log_message ) : ?>
				<?php echo esc_html( $log_message ) . '<br>'; ?>
			<?php endforeach; ?>
		</code>
	<?php
endif;
