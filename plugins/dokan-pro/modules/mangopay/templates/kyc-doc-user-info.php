<div id="dokan-mangopay-verification" class="dokan-mangopay-container hidden">
    <div class="kyc-doc-info <?php echo strtolower( $mp_user->KYCLevel ); ?>">
        <?php echo esc_html( $text_banner ); ?>
    </div>

    <table class="kyc-doc-list list-table">
        <thead>
            <tr>
                <td><?php esc_html_e( 'Status', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Document Type', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Uploaded on', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Details', 'dokan' ); ?></td>
            </tr>
        </thead>

        <tbody>
            <?php foreach ( $all_docs as $doc ) : ?>
            <tr class="kyc-doc-list-data" data-id="<?php echo esc_attr( $doc->Id ); ?>">
                <td class="kyc-doc-status-icon">
                    <mark class="kyc <?php echo esc_attr( $doc->Status ); ?>">
                        <?php echo esc_html( $doc->StatusLabel ); ?>
                    </mark>
                </td>

                <td><?php echo esc_html( $doc->TypeLabel ); ?></td>

                <td><?php printf( esc_html__( '%s', 'dokan' ), $doc->CreationDate ); ?></td>

                <td>
                    <?php
                    echo esc_html( $doc->StatusLabel );
                    echo ! empty( $doc->RefusedReasonMessage ) && 'NULL' !== $doc->RefusedReasonMessage
                        ? ': ' . esc_html__( $doc->RefusedReasonMessage, 'dokan')
                        : (
                            ! empty( $doc->RefusedReasonType )
                            ? ': ' . esc_html__( $refused_reasons[ $doc->RefusedReasonType ], 'dokan')
                            : ''
                        );
                    ?>

                    <?php if ( is_array( $doc->UserDocError ) ) : ?>
                        <?php foreach ( $data_error_file_user_meta as $key_error_message => $message_error ) : ?>
                            <br><?php esc_html_e( $message_error, 'dokan' ); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php foreach ( (array) $list_to_show as $key => $doc ) : ?>
            <tr class="kyc-doc-list-data">
                <td class="kyc-doc-status-icon">
                    <mark class="kyc not_sent">
                        <?php esc_html_e( 'NOT SENT', 'dokan' ); ?>
                    </mark>
                </td>
                <td><?php printf( esc_html__( '%s', 'dokan' ), ucfirst( str_replace( '_', ' ', strtolower( $key ) ) ) ); ?></td>

                <td>&nbsp;-&nbsp;</td>

                <td><?php esc_html_e( 'Not uploaded yet', 'dokan' ); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
