<?php
/**
 * Admin View: Product import form
 *
 * @since 3.3.7
 *
 * @package WeDevs\DokanPro
 * @sub-package WeDevs\DokanPro\Modules\ExIm\Importers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="wc-progress-form-content woocommerce-importer" enctype="multipart/form-data" method="post">
	<header>
		<h2><?php esc_html_e( 'Import products from a CSV file', 'dokan' ); ?></h2>
        <p>
            <?php
            esc_html_e( 'This tool allows you to import (or merge) product data to your store from a CSV or TXT file.', 'dokan' );
            if ( $has_file ) :
                ?>
                <a href="<?php echo esc_url( $file_url ); ?>" target="_blank"><?php esc_html_e( 'Download sample file.', 'dokan' ); ?></a>
            <?php endif; ?>
        </p>
	</header>
	<section>
		<table class="form-table woocommerce-importer-options">
			<tbody>
				<tr>
					<th scope="row">
						<label for="upload">
							<?php esc_html_e( 'Choose a CSV file from your computer:', 'dokan' ); ?>
						</label>
					</th>
					<td>
						<?php
						if ( ! empty( $upload_dir['error'] ) ) {
							?>
							<div class="inline error">
								<p><?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:', 'dokan' ); ?></p>
								<p><strong><?php echo esc_html( $upload_dir['error'] ); ?></strong></p>
							</div>
							<?php
						} else {
							?>
							<input type="file" id="upload" name="import" size="25" />
							<input type="hidden" name="action" value="save" />
							<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $bytes ); ?>" />
							<br>
							<small>
								<?php
								printf(
									/* translators: %s: maximum upload size */
									esc_html__( 'Maximum size: %s', 'dokan' ),
									esc_html( $size )
								);
								?>
							</small>
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><label for="woocommerce-importer-update-existing"><?php esc_html_e( 'Update existing products', 'dokan' ); ?></label><br/></th>
					<td>
						<input type="hidden" name="update_existing" value="0" />
						<input type="checkbox" id="woocommerce-importer-update-existing" name="update_existing" value="1" />
						<label for="woocommerce-importer-update-existing"><?php esc_html_e( 'Existing products that match by ID or SKU will be updated. Products that do not exist will be skipped.', 'dokan' ); ?></label>
					</td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th>
						<label for="woocommerce-importer-file-url"><?php esc_html_e( 'Alternatively, enter the path to a CSV file on your server:', 'dokan' ); ?></label>
					</th>
					<td>
						<label for="woocommerce-importer-file-url" class="woocommerce-importer-file-url-field-wrapper">
							<code><?php echo esc_html( ABSPATH ) . ' '; ?></code><input type="text" id="woocommerce-importer-file-url" name="file_url" />
						</label>
					</td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th><label><?php esc_html_e( 'CSV Delimiter', 'dokan' ); ?></label><br/></th>
					<td><input type="text" name="delimiter" placeholder="," size="2" /></td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th><label><?php esc_html_e( 'Use previous column mapping preferences?', 'dokan' ); ?></label><br/></th>
					<td><input type="checkbox" id="woocommerce-importer-map-preferences" name="map_preferences" value="1" /></td>
				</tr>
			</tbody>
		</table>
	</section>
	<script type="text/javascript">
        ;(function($, document){
            $(document).ready( function($) {
                $( '.woocommerce-importer-toggle-advanced-options' ).on( 'click', function() {
                    let elements = $( '.woocommerce-importer-advanced' );
                    if ( elements.is( '.hidden' ) ) {
                        elements.removeClass( 'hidden' );
                        $( this ).text( $( this ).data( 'hidetext' ) );
                    } else {
                        elements.addClass( 'hidden' );
                        $( this ).text( $( this ).data( 'showtext' ) );
                    }
                    return false;
                } );
            } );
        })(jQuery, document);
	</script>
	<div class="wc-actions">
		<a href="#" class="woocommerce-importer-toggle-advanced-options" data-hidetext="<?php esc_attr_e( 'Hide advanced options', 'dokan' ); ?>" data-showtext="<?php esc_attr_e( 'Show advanced options', 'dokan' ); ?>"><?php esc_html_e( 'Show advanced options', 'dokan' ); ?></a>
		<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Continue', 'dokan' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'dokan' ); ?></button>
		<?php wp_nonce_field( 'woocommerce-csv-importer' ); ?>
	</div>
</form>
