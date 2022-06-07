<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use Exception;
use MangoPay\Pagination;
use MangoPay\KycDocumentType;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class to process KYC operations
 *
 * @since 3.5.0
 */
class Kyc extends Processor {

    /**
     * Retrieve info about an existing KYV document.
     *
     * @since 3.5.0
     *
     * @param int $kyc_document_id
     *
     * @return object|false
     */
    public static function get( $kyc_document_id ) {
        try {
            $response = static::config()->mangopay_api->KycDocuments->Get( $kyc_document_id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not parse document: %s. Message: %s', $kyc_document_id, $e->getMessage() ), 'KYC', 'error' );
            return false;
        }

        return $response;
    }

    /**
     * Creates KYV document.
     *
     * @since 3.5.0
     *
     * @param int|string $account_id
     * @param string     $kyc_file_type
     *
     * @return object|\WP_Error
     */
    public static function create_document( $account_id, $kyc_file_type ) {
        try {
            $response = static::config()->mangopay_api->Users->CreateKycDocument( $account_id, $kyc_file_type );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create document: %s. Message: %s', $kyc_file_type, $e->getMessage() ), 'KYC', 'error' );
            return new \WP_Error( 'dokan-mp-kyc-doc-create-error', sprintf( __( 'Could not create KYC document. Message: %s', 'dokan' ), $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Add page (file) to a document.
     *
     * @since 3.5.0
     *
     * @param int|string $account_id
     * @param int|string $kyc_document_id
     * @param resource   $file
     *
     * @return object|\WP_Error
     */
    public static function create_page( $account_id, $kyc_document_id, $file ) {
        try {
            $response = static::config()->mangopay_api->Users->CreateKycPageFromFile( $account_id, $kyc_document_id, $file );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create pages for document: %s. Message: %s', $kyc_document_id, $e->getMessage() ), 'KYC', 'error' );
            return new \WP_Error( 'dokan-mp-kyc-doc-create-error', sprintf( __( 'Could not create pages for document: %s. Message: %s', 'dokan' ), $kyc_document_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Updates a KYC document.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param object     $kyc_document
     *
     * @return object|false
     */
    public static function update( $user_id, $kyc_document ) {
        try {
            return static::config()->mangopay_api->Users->UpdateKycDocument( $user_id, $kyc_document );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update document: %s. Message: %s', $kyc_document->Id, $e->getMessage() ), 'KYC', 'error' );
            return new \WP_Error( 'dokan-mp-kyc-doc-update-error', sprintf( __( '%s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Filters KYC documents
     *
     * @since 3.5.0
     *
     * @param int|string $user_id    Mangopay ID of the user
     * @param object 	 $pagination \MangoPay\Pagination object including pagination rules
     * @param object 	 $sorting	 \MangoPay\Sorting object including sorting rules
     * @param object 	 $filter	 \MangoPay\Filter object including filtering rules
     *
     * @return array
     */
    public static function filter( $user_id, $pagination = null, $sorting = null, $filter = null ) {
        if ( empty( $pagination ) ) {
            $pagination               = new Pagination();
            $pagination->Page         = 1;
            $pagination->ItemsPerPage = 100; //100 is the maximum
        }

        return static::config()->mangopay_api->Users->GetKycDocuments( $user_id, $pagination , $sorting , $filter );
    }

    /**
     * Get the URL to upload a KYC Document for that user.
     *
     * @since 3.5.0
     *
     * @param string $mp_user_id
     *
     * @return string
     */
    public static function get_dashboard_url( $mp_user_id ) {
        return static::config()->get_dashboard_url() . "/User/$mp_user_id/Kyc";
    }

    /**
     * Tests if KYC validation is successful
     *
     * @since 3.5.0
     *
     * @param type $mp_user_id
     *
     * @return boolean|string
     */
    public static function is_valid( $mp_user_id ) {
        $user = User::get( $mp_user_id );
        if ( ! $user ) {
            return false;
        }

        // we are light or there is one not set we kill it
        if ( ! isset( $user->KYCLevel ) || 'LIGHT' === $user->KYCLevel ) {
            return false;
        }

        // Get required document types for a specific person type
        $required_docs = self::get_doc_types( $user );
        // get all documents of that user
        $submitted_docs = self::filter( $mp_user_id );

        // if we dont have the same count we have a problem
        foreach ( $required_docs as $doc_type => $doc ) {
            $found = false;
            foreach ( $submitted_docs as $doc ) {
                if ( $doc_type === $doc->Type && 'VALIDATED' === $doc->Status ) {
                    $found = true;
                    break;
                }
            }

            // If not found in the list of docs, we kick out
            if ( ! $found ) {
                return false;
            }
        }

        // If everything is fine we can return true
        return true;
    }

    /**
     * Retrieves required document types for a specific person type.
     *
     * @since 3.5.0
     *
     * @param \MangoPay\User $user
     *
     * @return array
     */
    public static function get_doc_types( \MangoPay\User $user ) {
        // Default for everyone
        $required_docs = array( KycDocumentType::IdentityProof => __( 'Identity proof', 'dokan' ), );

        // If not legal person type, we need no further execution
        if ( 'LEGAL' !== $user->PersonType ) {
            return $required_docs;
        }

        // Mandatory for all legal user
        $required_docs[ KycDocumentType::RegistrationProof ] = __( 'Registration proof', 'dokan' );

        if ( empty( $user->LegalPersonType ) ) {
            return $required_docs;
        }

        if ( 'BUSINESS' === $user->LegalPersonType || 'ORGANIZATION' === $user->LegalPersonType ) {
            $required_docs[ KycDocumentType::ArticlesOfAssociation ] = __( 'Articles of association', 'dokan' );
        }

        return $required_docs;
    }

    /**
     * Retrieves refused reasons of KYC documents
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_refused_reasons() {
        return array(
            'DOCUMENT_UNREADABLE'                => __( 'Document unreadable', 'dokan' ),
            'DOCUMENT_NOT_ACCEPTED'              => __( 'Document not acceptable', 'dokan' ),
            'DOCUMENT_HAS_EXPIRED'               => __( 'Document has expired', 'dokan' ),
            'DOCUMENT_INCOMPLETE'                => __( 'Document incomplete', 'dokan' ),
            'DOCUMENT_MISSING'                   => __( 'Document missing', 'dokan' ),
            'DOCUMENT_DO_NOT_MATCH_USER_DATA'    => __( 'Document does not match user data', 'dokan' ),
            'DOCUMENT_DO_NOT_MATCH_ACCOUNT_DATA' => __( 'Document does not match account data', 'dokan' ),
            'SPECIFIC_CASE'                      => __( 'Specific case, please contact us', 'dokan' ),
            'DOCUMENT_FALSIFIED'                 => __( 'Document has been falsified', 'dokan' ),
            'UNDERAGE_PERSON'                    => __( 'Underage person', 'dokan' ),
            'OTHER'                              => __( 'Other', 'dokan' ),
            'TRIGGER_PEPS'                       => __( 'PEPS check triggered', 'dokan' ),
            'TRIGGER_SANCTIONS_LISTS'            => __( 'Sanction lists check triggered', 'dokan' ),
            'TRIGGER_INTERPOL'                   => __( 'Interpol check triggered', 'dokan' ),
        );
    }
}
