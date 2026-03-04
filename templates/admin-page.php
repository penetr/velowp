<?php
/**
 * Admin page template.
 *
 * @var array $report
 * @var array $options
 * @var array $counts
 * @var array $logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap velowp-admin">
	<h1><?php echo esc_html__( 'VeloWP', 'velowp' ); ?></h1>

	<h2><?php echo esc_html__( 'Server / Environment', 'velowp' ); ?></h2>
	<ul>
		<li><?php echo esc_html( 'PHP OK: ' . ( $report['php_ok'] ? 'yes' : 'no' ) ); ?></li>
		<li><?php echo esc_html( 'WP OK: ' . ( $report['wp_ok'] ? 'yes' : 'no' ) ); ?></li>
		<li><?php echo esc_html( 'GD WebP: ' . ( $report['gd_webp'] ? 'yes' : 'no' ) ); ?></li>
		<li><?php echo esc_html( 'Imagick WebP: ' . ( $report['imagick_webp'] ? 'yes' : 'no' ) ); ?></li>
		<li><?php echo esc_html( '.htaccess writable: ' . ( $report['htaccess_writable'] ? 'yes' : 'no' ) ); ?></li>
		<li><?php echo esc_html( 'Derivatives writable: ' . ( $report['derivatives_writable'] ? 'yes' : 'no' ) ); ?></li>
	</ul>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'velowp_action' ); ?>
		<input type="hidden" name="action" value="velowp_save" />
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php echo esc_html__( 'Delivery method', 'velowp' ); ?></th>
				<td>
					<select name="delivery_method">
						<option value="off" <?php selected( $options['delivery_method'], 'off' ); ?>>OFF</option>
						<option value="apache" <?php selected( $options['delivery_method'], 'apache' ); ?>>Apache</option>
						<option value="php_safe" <?php selected( $options['delivery_method'], 'php_safe' ); ?>>PHP Safe</option>
						<option value="php_wide" <?php selected( $options['delivery_method'], 'php_wide' ); ?>>PHP Wide</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'JPEG Quality', 'velowp' ); ?></th>
				<td><input type="number" min="1" max="100" name="jpeg_quality" value="<?php echo esc_attr( (string) $options['jpeg_quality'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'PNG mode', 'velowp' ); ?></th>
				<td>
					<select name="png_mode">
						<option value="lossless" <?php selected( $options['png_mode'], 'lossless' ); ?>>lossless</option>
						<option value="lossy" <?php selected( $options['png_mode'], 'lossy' ); ?>>lossy</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Bypass query parameter', 'velowp' ); ?></th>
				<td><input type="text" name="bypass_query_param" value="<?php echo esc_attr( (string) $options['bypass_query_param'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Auto convert media', 'velowp' ); ?></th>
				<td><label><input type="checkbox" name="auto_convert_media" value="1" <?php checked( 1, (int) $options['auto_convert_media'] ); ?> /> <?php echo esc_html__( 'Enable', 'velowp' ); ?></label></td>
			</tr>
		</table>
		<?php submit_button( __( 'Save settings', 'velowp' ) ); ?>
	</form>

	<h2><?php echo esc_html__( 'Queue & log', 'velowp' ); ?></h2>
	<p><?php echo esc_html( sprintf( 'Pending: %d, Processing: %d, Done: %d, Error: %d, Skipped: %d', $counts['pending'], $counts['processing'], $counts['done'], $counts['error'], $counts['skipped'] ) ); ?></p>

	<p>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=velowp_scan' ), 'velowp_action' ) ); ?>"><?php echo esc_html__( 'Scan and enqueue', 'velowp' ); ?></a>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=velowp_resume' ), 'velowp_action' ) ); ?>"><?php echo esc_html__( 'Continue', 'velowp' ); ?></a>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=velowp_pause' ), 'velowp_action' ) ); ?>"><?php echo esc_html__( 'Pause', 'velowp' ); ?></a>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=velowp_report' ), 'velowp_action' ) ); ?>"><?php echo esc_html__( 'Download report', 'velowp' ); ?></a>
	</p>

	<table class="widefat">
		<thead><tr><th>Time</th><th>Level</th><th>Category</th><th>Path</th><th>Message</th></tr></thead>
		<tbody>
			<?php foreach ( $logs as $log ) : ?>
			<tr>
				<td><?php echo esc_html( $log['time'] ); ?></td>
				<td><?php echo esc_html( $log['level'] ); ?></td>
				<td><?php echo esc_html( $log['category'] ); ?></td>
				<td><?php echo esc_html( $log['path'] ); ?></td>
				<td><?php echo esc_html( $log['message'] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
