<?php

namespace WeDevs\DokanPro\Modules\MangoPay\WithdrawMethod;

use MangoPay\Address;
use MangoPay\Sorting;
use MangoPay\Birthplace;
use MangoPay\Pagination;
use MangoPay\KycDocument;
use MangoPay\KycDocumentType;
use MangoPay\Ubo as MangoUbo;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Kyc;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Ubo;
use WeDevs\DokanPro\Modules\MangoPay\Processor\User;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\Processor\BankAccount;
use WeDevs\DokanPro\Modules\MangoPay\Support\Validation;

/**
 * Class to handle all ajax actions for MangoPay withdraw method.
 *
 * @since 3.5.0
 */
class Ajax {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    function __construct() {
        // Hooks for handling mangopay account
        add_action( 'wp_ajax_dokan_mangopay_signup', array( $this, 'sign_up' ) );
        add_action( 'wp_ajax_dokan_mangopay_disconnect_vendor', array( $this, 'disconnect_vendor' ) );
        add_action( 'wp_ajax_dokan_mangopay_get_country_wise_states', array( $this, 'get_country_wise_states' ) );

        // Hooks for handling mangopay bank account
        add_action( 'wp_ajax_dokan_mangopay_create_bank_account', array( $this, 'create_bank_account' ) );
        add_action( 'wp_ajax_dokan_mangopay_update_active_bank_account', array( $this, 'update_active_bank_account' ) );
        add_action( 'wp_ajax_dokan_mangopay_get_bank_accounts', array( $this, 'render_bank_accounts' ) );

        // Hooks for handling UBO
        add_action( 'wp_ajax_dokan_mangopay_create_ubo', array( $this, 'create_ubo' ) );
        add_action( 'wp_ajax_dokan_mangopay_add_ubo_element', array( $this, 'add_ubo_element' ) );
        add_action( 'wp_ajax_dokan_mangopay_render_ubo_form', array( $this, 'render_ubo_form' ) );
        add_action( 'wp_ajax_dokan_mangopay_ask_ubo_declaration', array( $this, 'ask_ubo_declaration' ) );

        // Hooks for handling KYC
        add_action( 'wp_ajax_dokan_mangopay_submit_kyc', array( $this, 'submit_kyc' ) );
    }

    /**
     * retrieves states for a given country.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function get_country_wise_states() {
        if ( empty( $_REQUEST['country'] ) ) {
            wp_send_json_error( array() );
        }

        $for_country = sanitize_text_field( wp_unslash( $_REQUEST['country'] ) );
        $states      = WC()->countries->get_states( $for_country );

        wp_send_json_success( $states );
    }

    /**
     * Creates a bank account for Mangopay.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function create_bank_account() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['settings']['mangopay'] ) ) {
            wp_send_json_error( __( 'No data provided!', 'dokan' ) );
        }

        $mangopay_data = wc_clean( wp_unslash( $_POST['settings']['mangopay'] ) );
        $account_types = Helper::get_bank_account_types_fields();

        if ( empty( $mangopay_data['bank_account']['type'] ) || empty( $account_types[ $mangopay_data['bank_account']['type'] ] ) ) {
            wp_send_json_error( __( 'Please provide a valid bank account type', 'dokan' ) );
        }

        $user_id      = get_current_user_id();
        $mp_user_id   = Meta::get_mangopay_account_id( $user_id );
        $bank_account = BankAccount::create( $mp_user_id, $user_id, $mangopay_data['bank_account'] );

        if ( is_wp_error( $bank_account ) ) {
            wp_send_json_error( $bank_account->get_error_message() );
        }

        wp_send_json_success( __( 'Bank account created successfully', 'dokan' ) );
    }

    /**
     * Updates active bank account.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function update_active_bank_account() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error( __( 'No user found', 'dokan' ) );
        }

        if ( empty( $_POST['bank_account_id'] ) ) {
            wp_send_json_error( __( 'No bank account found', 'dokan' ) );
        }

        $user_id         = intval( wp_unslash( $_POST['user_id'] ) );
        $bank_account_id = sanitize_text_field( wp_unslash( $_POST['bank_account_id'] ) );
        $mp_user_id      = Meta::get_mangopay_account_id( $user_id );

        if ( empty( $mp_user_id ) ) {
            wp_send_json_error( __( 'No Mangopay account found!', 'dokan' ) );
        }

        $bank_account = BankAccount::get( $mp_user_id, $bank_account_id );
        if ( empty( $bank_account ) || ! is_object( $bank_account ) ) {
            wp_send_json_error( __( 'No valid bank account found!', 'dokan' ) );
        }

        Meta::update_active_bank_account( $user_id, $bank_account->Id );
        Meta::update_bank_account_id( $user_id, $bank_account->Id, $bank_account->Type );

        wp_send_json_success( __( 'Bank account made active successfully', 'dokan' ) );
    }

    /**
     * Renders bank account list.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function render_bank_accounts() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error( __( 'No user found', 'dokan' ) );
        }

        $user_id    = intval( wp_unslash( $_POST['user_id'] ) );
        $mp_user_id = Meta::get_mangopay_account_id( $user_id );

        if ( empty( $mp_user_id ) ) {
            wp_send_json_error( __( 'No Mangopay account found!', 'dokan' ) );
        }

        ob_start();

        Helper::get_template(
            'bank-account-list',
            array(
                'user_id'        => $user_id,
                'bank_accounts'  => BankAccount::all( $mp_user_id ),
                'active_account' => Meta::get_active_bank_account( $user_id ),
            )
        );

        wp_send_json_success( ob_get_clean() );
    }

    /**
     * Submits KYC document
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function submit_kyc() {
        if ( empty( $_FILES['files'] ) ) {
            wp_send_json_error( __( 'No files selected', 'dokan' ) );
        }

        if ( empty( $_REQUEST['doc_type'] ) ) {
            wp_send_json_error( __( 'No file type selected', 'dokan' ) );
        }

        $files       = $_FILES['files']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $doc_type    = sanitize_text_field( wp_unslash( $_REQUEST['doc_type'] ) );
        $total_files = count( $files['name'] );

        for ( $index = 0; $index < $total_files; ++$index ) {
            $file_type = $files['type'][ $index ];

            if ( ! preg_match( '#pdf|application/pdf|image\/jpeg|image\/jpg|image\/gif|image\/png#i', $file_type ) ) {
                wp_send_json_error( sprintf( __( 'Unsupported file type: %s. Supported types: .pdf, .jpeg, .jpg, .gif and .png only', 'dokan' ), $file_type ) );
            }

            $file_size = (int) $files['size'][ $index ];

            if ( $file_size < 32000 && $doc_type === KycDocumentType::IdentityProof ) {
                wp_send_json_error( sprintf( __( 'This file is too small. Minimum 32KB required for %s', 'dokan' ), KycDocumentType::IdentityProof ) );
            }

            if ( $file_size < 1000 ) {
                wp_send_json_error( sprintf( __( 'This file is too small. Minimum 1KB required for %s', 'dokan' ), $file_type ) );
            }

            if ( $file_size > 10000000 ) {
                wp_send_json_error( sprintf( __( 'This file is too big. Maximum 10MB required for %s', 'dokan' ), $file_type ) );
            }
        }

        // Step-1: Create a document
        $user_id             = get_current_user_id();
        $existing_account_id = Meta::get_mangopay_account_id( $user_id );
        $kyc_document        = new KycDocument();
        $kyc_document->Tag   = "wp_user_id: {$user_id}";
        $kyc_document->Type  = $doc_type;
        $document            = Kyc::create_document( $existing_account_id, $kyc_document );

        if ( is_wp_error( $document ) ) {
            Helper::log( $document->get_error_message(), 'KYC', 'error' );
            wp_send_json_error( $document->get_error_message() );
        }

        $document_id = $document->Id;

        if ( ! $document_id ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ) );
        }

        // Step-2: Create pages for the created document
        for ( $index = 0; $index < $total_files; ++$index ) {
            $kyc_page = $files['tmp_name'][ $index ];
            $doc_page = Kyc::create_page( $existing_account_id, $document_id, $kyc_page );

            if ( is_wp_error( $doc_page ) ) {
                wp_send_json_error( $doc_page->get_error_message() );
            }

            $data_user_meta[ "name_$index" ] = $files['name'][ $index ];
        }

        // Step-3: Submit a document ask for VALIDATION_ASKED
        $kyc_doc = Kyc::get( $document_id );

        if ( ! $kyc_doc ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ) );
        }

        $kyc_doc->Status = 'VALIDATION_ASKED';
        $response        = Kyc::update( $existing_account_id, $kyc_doc );

        if ( is_wp_error( $response ) ) {
            update_user_meta( $user_id, '_dokan_mangopay_kyc_error', $document_id );
            wp_send_json_error( $response->get_error_message() );
        }

        $metadata['type']          = $doc_type;
        $metadata['id_mp_doc']     = $document_id;
        $metadata['creation_date'] = $response->CreationDate;
        $metadata['document_name'] = $files['name'];
        update_user_meta( $user_id, '_dokan_mangopay_kyc_document_' . $document_id, $metadata );

        wp_send_json_success( __( 'KYC submitted successfully and asked for validation', 'dokan' ) );
    }

    /**
     * Disconnects vendors' account from MangoPay
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function disconnect_vendor() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        $seller_id = intval( wp_unslash( $_POST['user_id'] ) );

        if ( empty( $seller_id ) ) {
            wp_send_json_error( __( 'No vendor found!', 'dokan' ) );
        }

        Meta::delete_mangopay_account_id( $seller_id, false );

        wp_send_json_success( __( 'Account disconnected successfully.', 'dokan' ) );
    }

    /**
     * Signs up for a Mangopay account.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function sign_up() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error( __( 'No vendor found!', 'dokan' ) );
        }

        $user_id = intval( wp_unslash( $_POST['user_id'] ) );

        $data = array(
            'date_of_birth'  => ! empty( $_POST['date_of_birth'] )  ? sanitize_text_field( wp_unslash( $_POST['date_of_birth'] ) )  : '',
            'nationality'    => ! empty( $_POST['nationality'] )    ? sanitize_text_field( wp_unslash( $_POST['nationality'] ) )    : '',
            'person_type'    => ! empty( $_POST['person_type'] )    ? sanitize_text_field( wp_unslash( $_POST['person_type'] ) )    : '',
            'business_type'  => ! empty( $_POST['business_type'] )  ? sanitize_text_field( wp_unslash( $_POST['business_type'] ) )  : '',
            'company_number' => ! empty( $_POST['company_number'] ) ? sanitize_text_field( wp_unslash( $_POST['company_number'] ) ) : '',
            'address1'       => ! empty( $_POST['address1'] )       ? sanitize_text_field( wp_unslash( $_POST['address1'] ) )       : '',
            'address2'       => ! empty( $_POST['address2'] )       ? sanitize_text_field( wp_unslash( $_POST['address2'] ) )       : '',
            'city'           => ! empty( $_POST['city'] )           ? sanitize_text_field( wp_unslash( $_POST['city'] ) )           : '',
            'postcode'       => ! empty( $_POST['postcode'] )       ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) )       : '',
            'state'          => ! empty( $_POST['state'] )          ? sanitize_text_field( wp_unslash( $_POST['state'] ) )          : '',
            'country'        => ! empty( $_POST['country'] )        ? sanitize_text_field( wp_unslash( $_POST['country'] ) )        : '',
            'company_name'   => get_user_meta( $user_id, 'dokan_store_name', true ),
        );

        $existing_account_id = Meta::get_trashed_mangopay_account_id( $user_id );
        if ( $existing_account_id ) {
            Meta::update_mangopay_account_id( $user_id, $existing_account_id );
        } else {
            $existing_account_id = Meta::get_mangopay_account_id( $user_id );
        }

        // User already exists. It's an update request
        if ( ! empty( $existing_account_id ) ) {
            $response = User::update( $existing_account_id, $user_id, $data );

            if ( ! $response || is_wp_error( $response ) ) {
                wp_send_json_error( __( 'Something went wrong!', 'dokan' ) );
            }

            wp_send_json_success( __( 'Account connected sucessfully.', 'dokan' ) );
        }

        $error_notice = array();

        if ( empty( $data['date_of_birth'] ) ) {
            $error_notice[] = __( 'Date of Birth is required', 'dokan' );
        }

        if ( empty( $data['nationality'] ) ) {
            $error_notice[] = __( 'Nationality is required', 'dokan' );
        }

        if ( 'EITHER' === Settings::get_default_vendor_status() && empty( $data['person_type'] ) ) {
            $error_notice[] = __( 'Type of User is required', 'dokan' );
        }

        if ( 'EITHER' === Settings::get_default_business_type() && ! empty( $data['person_type'] ) && empty( $data['business_type'] ) ) {
            $error_notice[] = __( 'Type of Business is required', 'dokan' );
        }

        if ( ! empty( $error_notice ) ) {
            wp_send_json_error( implode( '<br>', $error_notice ) );
        }

        if ( 'LEGAL' === $data['person_type'] ) {
            $store_info = dokan_get_store_info( $user_id );

            if (
                empty( $store_info['address']['street_1'] ) ||
                empty( $store_info['address']['city'] ) ||
                empty( $store_info['address']['zip'] ) ||
                empty( $store_info['address']['country'] ) ||
                (
                    empty( $store_info['address']['state'] ) &&
                    in_array( $store_info['address']['country'], array( 'US', 'MX', 'CA' ) )
                )
            ) {
                wp_send_json_error( sprintf( __( 'Your store address is required to be a MangoPay business user. Please go to your <a href="%s">store settings</a> and update the address.' ), esc_url_raw( home_url( 'dashboard/settings/store' ) ) ) );
            }

            $data['company_address1'] = $store_info['address']['street_1'];
            $data['company_address2'] = $store_info['address']['street_2'];
            $data['company_city']     = $store_info['address']['city'];
            $data['company_postcode'] = $store_info['address']['zip'];
            $data['company_country']  = $store_info['address']['country'];
            $data['company_state']    = $store_info['address']['state'];

            if ( ! empty( $data['business_type'] ) && 'BUSINESS' === $data['business_type'] ) {
                if ( empty( $data['company_number'] ) ) {
                    $error_notice[] = __( 'Company Number is required', 'dokan' );
                } elseif ( ! Validation::check_company_number_pattern( $data['company_number'] ) ) {
                    $error_notice[] = __( 'Company Number pattern is incorrect. Please provide a valid one.', 'dokan' );
                }
            }

            if ( empty( $data['address1'] ) ) {
                $error_notice[] = __( 'Address is required', 'dokan' );
            }

            if ( empty( $data['country'] ) ) {
                $error_notice[] = __( 'Country is required', 'dokan' );
            }

            if ( empty( $data['city'] ) ) {
                $error_notice[] = __( 'City is required', 'dokan' );
            }

            if ( empty( $data['state'] ) && in_array( $data['country'], array( 'US', 'MX', 'CA' ) ) ) {
                $error_notice[] = __( 'State is required', 'dokan' );
            }

            if ( empty( $data['postcode'] ) ) {
                $error_notice[] = __( 'Postcode is required', 'dokan' );
            }
        }

        if ( ! empty( $error_notice ) ) {
            wp_send_json_error( implode( '<br>', $error_notice ) );
        }

        $response = User::create( $user_id, $data );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( __( 'Account connected sucessfully.', 'dokan' ) );
    }

    /**
     * Renders UBO form for verification.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function render_ubo_form() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( ! isset( $_POST['account_id'] ) ) {
            wp_send_json_error( __( 'Account is not connected with MangoPay. Please connect your account.', 'dokan' ) );
        }

        $account_id = sanitize_text_field( wp_unslash( $_POST['account_id'] ) );
        $pagination = new Pagination(1,99);
        $sorting    = new Sorting();
        $sorting->AddField('CreationDate', 'ASC');

        $ubo_declarations = Ubo::get_declarations( $account_id, $pagination, $sorting );
        if ( ! $ubo_declarations ) {
            $ubo_declarations = array();
        }

        $status             = Ubo::get_status_details();
        $created_ubo_id     = false;
        $created_ubo_button = false;
        $ubo_exists         = false;
        $show_create_button = true;

        foreach ( $ubo_declarations as &$ubo_declaration ) {
            // if at least one has a different status than refused, we do not let the user ask for another declaration
            if ( 'REFUSED' !== $ubo_declaration->Status && 'VALIDATED' !== $ubo_declaration->Status ) {
                $show_create_button = false;
            }
            // if one of this status we add the button
            if ( 'CREATED' === $ubo_declaration->Status || 'INCOMPLETE' === $ubo_declaration->Status ) {
                $created_ubo_id     = $ubo_declaration->Id;
                $created_ubo_button = true;

                if ( count( $ubo_declaration->Ubos ) >= 4 ) {
                    $created_ubo_button = false;
                }

                if ( count( $ubo_declaration->Ubos ) >= 1 ) {
                    $ubo_exists = true;
                }
            }

            $ubo_declaration->CreationDate = date( 'F j, Y', $ubo_declaration->CreationDate );
            $ubo_declaration->OutputStatus = ! empty( $status[ $ubo_declaration->Status ] ) ? $status[ $ubo_declaration->Status ] : sprintf( __( '%s', 'dokan' ), $ubo_declaration->Status );

            if ( 'REFUSED' === $ubo_declaration->Status || 'INCOMPLETE' === $ubo_declaration->Status ) {
                $ubo_declaration->OutputStatus .= sprintf(
                    '<br><span class="%s">%s</span>',
                    sprintf( 'ubo_%s_status_Reason', strtolower( $ubo_declaration->Status ) ),
                    sprintf( '%s', ! empty( $status[ $ubo_declaration->Reason ] ) ? $status[ $ubo_declaration->Reason ] : sprintf( __( '%s', 'dokan' ), $ubo_declaration->Reason ) )
                );
            }
        }

        ob_start();

        Helper::get_template(
            'ubo-info',
            array(
                'ubo_declarations'    => $ubo_declarations,
                'status'              => $status,
                'created_ubo_id'      => $created_ubo_id,
                'created_ubo_button'  => $created_ubo_button,
                'ubo_exists'          => $ubo_exists,
                'show_create_button'  => $show_create_button,
                'existing_account_id' => $account_id,
                'fields'              => Helper::get_ubo_form_field(),
            )
        );

        wp_send_json_success( ob_get_clean() );
    }

    /**
     * Adds a UBO element.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function add_ubo_element() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        // create UBO object
        $ubo                        = new MangoUbo();
        $ubo->FirstName             = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $ubo->LastName              = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
        $ubo->Address               = new Address();
        $ubo->Address->AddressLine1 = ! empty( $_POST['address_line1'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line1'] ) ) : '';
        $ubo->Address->AddressLine2 = ! empty( $_POST['address_line2'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line2'] ) ) : '';
        $ubo->Address->City         = ! empty( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
        $ubo->Address->Region       = ! empty( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '';
        $ubo->Address->PostalCode   = ! empty( $_POST['postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['postal_code'] ) ) : '';
        $ubo->Address->Country      = ! empty( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
        $ubo->Nationality           = ! empty( $_POST['nationality'] ) ? sanitize_text_field( wp_unslash( $_POST['nationality'] ) ) : '';
        $ubo->Birthday              = ! empty( $_POST['date_of_birth'] ) ? Helper::format_date( sanitize_text_field( wp_unslash( $_POST['date_of_birth'] ) ) ) : '';
        $ubo->Birthplace            = new Birthplace();
        $ubo->Birthplace->City      = ! empty( $_POST['birthplace_city'] ) ? sanitize_text_field( wp_unslash( $_POST['birthplace_city'] ) ) : '';
        $ubo->Birthplace->Country   = ! empty( $_POST['birthplace_country'] ) ? sanitize_text_field( wp_unslash( $_POST['birthplace_country'] ) ) : '';
        $account_id                 = ! empty( $_POST['account_id'] ) ? sanitize_text_field( wp_unslash( $_POST['account_id'] ) ) : '';
        $ubo_declaration_id         = ! empty( $_POST['ubo_declaration_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ubo_declaration_id'] ) ) : '';

        if ( ! empty( $_POST['ubo_element_id'] ) ) {
            $ubo->Id  = sanitize_text_field( wp_unslash( $_POST['ubo_element_id'] ) );
            $response = Ubo::update_element( $account_id, $ubo_declaration_id, $ubo );
        } else {
            $response = Ubo::create_element( $account_id, $ubo_declaration_id, $ubo );
        }

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( $response );
    }

    /**
     * Creates UBO.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function create_ubo() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error( __( 'No user found!', 'dokan' ) );
        }

        $ubo = Ubo::create_declaration( intval( wp_unslash( $_POST['user_id'] ) ) );

        if ( is_wp_error( $ubo ) ) {
            wp_send_json_error( $ubo->get_error_message() );
        }

        wp_send_json_success( $ubo );
    }

    /**
     * Asks for UBO declaration.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function ask_ubo_declaration() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_payment_settings_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Pemission denied!', 'dokan' ) );
        }

        if ( empty( $_POST['user_id'] ) ) {
            wp_send_json_error( __( 'No user found!', 'dokan' ) );
        }

        if ( empty( $_POST['ubo_declaration_id'] ) ) {
            wp_send_json_error( __( 'No UBO declaration found!', 'dokan' ) );
        }

        $response = Ubo::ask_for_validation(
            intval( wp_unslash( $_POST['user_id'] ) ),
            intval( wp_unslash( $_POST['ubo_declaration_id'] ) )
        );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( $response );
    }
}
