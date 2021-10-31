<?php

global $post;

if (isset($post)) :
	$log_messages = get_post_meta( $post->ID, 'suc_log_messages_json', true );
	?>
		<p><?php echo esc_html( $post->post_date ); ?></p>
		<p>
			<?php echo esc_html( $log_messages ); ?>
		</p>
	<?php
endif;