<h1><?php esc_html_e( 'TMS Connect Object Sync', 'tmsc' ); ?></h1>
<div class="no-key config-wrap">
	<p><?php esc_html_e( 'This utility will connect to the specified address and import TMSC objects.', 'tmsc' ); ?></p>
	<div class="activate-highlight secondary activate-option">
		<form method="post" id="tmsc-form" name="tmsc-form" class="right" action="<?php echo admin_url( 'admin-ajax.php' ); ?>">
			<p>
				<label for="tmsc-db-host"><?php esc_html_e( 'Database Server Address: ', 'tmsc' );?></label>
				<input type="text" id="tmsc-db-host" name="tmsc-db-host" value="<?php echo esc_attr( get_option( 'tmsc-db-host', '' ) ); ?>" class="regular-text code">
			</p>
			<p>
				<label for="tmsc-ids-image-url"><?php esc_html_e( 'IDS Image URL: ', 'tmsc' );?></label>
				<input type="text" id="tmsc-ids-image-url" name="tmsc-ids-image-url" value="<?php echo esc_url( get_option( 'tmsc-ids-image-url', 'http://ids.si.edu/ids/deliveryService' ) ); ?>" class="regular-text code">
			</p>
			<p>
				<label for="tmsc-full-sync"><?php esc_html_e( 'Full Sync: ', 'tmsc' );?></label>
				<input type="checkbox" id="tmsc-full-sync" name="tmsc-full-sync" value="true">
			</p>
			<p>
				<input type="submit" name="tmsc-sync-button" id="tmsc-sync-button" class="button button-primary" value="<?php esc_attr_e( 'Sync Objects', 'tpg' ); ?>">
			</p>
			<p>
				<?php wp_nonce_field( 'tmsc_object_sync', 'tmsc_nonce' ); ?>
				<input type="hidden" name="action" value="sync_tmsc_objects"/>
				<?php $last_update = get_option( 'tmsc-last-sync-date' ); ?>
				<input type="hidden" name="tmsc-last-updated-value" id="tmsc-last-updated-value" value="<?php echo esc_attr( $last_update ); ?>"/>
				<div class="text-muted">
					<?php esc_html_e( 'Last Sync: ', 'tmsc' ); ?>
					<span id="last-updated-text">
						<?php if ( ! empty( $last_update ) ) :
							echo esc_html( $last_update );
						else :
							esc_html_e( 'N/A', 'tmsc' );
						endif; ?>
					</span>
					<span id="ajax-loading"><img src="/wp-admin/images/loading.gif"/></span>
				</div>
			</p>
		</form>
	</div>
</div>
