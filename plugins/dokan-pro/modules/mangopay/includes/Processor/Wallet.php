<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use Exception;
use MangoPay\Wallet as MangoWallet;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class for processing wallets
 *
 * @since 3.5.0
 */
class Wallet extends Processor {

    /**
     * Retrieves all wallets of a specific user.
     *
     * @since 3.5.0
     *
     * @param string|int $mangopay_user_id
     *
     * @return object|false
     */
    public static function get( $mangopay_user_id ) {
        if ( empty( $mangopay_user_id ) ) {
            return false;
        }

        try {
            $wallets = static::config()->mangopay_api->Users->GetWallets( $mangopay_user_id );
        } catch( Exception $e ) {
            self::log( sprintf( 'Could not fetch wallets for user: %s. Message: %s', $mangopay_user_id, $e->getMessage() ) );
            return false;
        }

        return $wallets;
    }

    /**
     * Creates a Mangopay wallet.
     * If a wallet already exists, returns that.
     *
     * @since 3.5.0
     *
     * @param int|string $mangopay_user_id
     *
     * @return object|\WP_Error
     */
    public static function create( $mangopay_user_id ) {
        if ( ! $mangopay_user_id ) {
            return new \WP_Error( 'dokan-mangopay-no-valid-user', __( 'Could not create Mangopay wallet for the user', 'dokan' ) );
        }

        $mangopay_user = User::get( $mangopay_user_id );
        if ( ! $mangopay_user ) {
            return new \WP_Error( 'dokan-mangopay-no-valid-user', __( 'No Mangopay user found to create a wallet', 'dokan' ) );
        }

        if ( 'BUSINESS' === $mangopay_user->PersonType || 'LEGAL' === $mangopay_user->PersonType ) {
            $account_type = 'Business';
        } elseif ( 'NATURAL' === $mangopay_user->PersonType ) {
            $account_type = 'Individual';
        } else {
            self::log( sprintf( 'Could not create wallet for unknown user type: %s', $mangopay_user->PersonType ) );
            return new \WP_Error( 'dokan-mangopay-unknown-usertype', sprintf( __( 'Could not create wallet for unknown user type: %s', 'dokan' ), $mangopay_user->PersonType ) );
        }

        $created = self::add( $mangopay_user_id, $account_type );
        if ( is_wp_error( $created ) ) {
            return $created;
        }

        $wallets      = self::get( $mangopay_user_id );
        $valid_wallet = false;
        foreach ( $wallets as $wallet ) {
            // Check that one wallet has the right currency
            if ( $wallet->Currency === get_woocommerce_currency() ) {
                $valid_wallet = $wallet;
            }
        }

        if ( ! $valid_wallet ) {
            return new \WP_Error( 'dokan-mangopay-no-valid-wallet', sprintf( __( 'No valid Mngopay wallet found.', 'dokan' ), $mangopay_user->PersonType ) );
        }

        return $valid_wallet;
    }

    /**
     * Creates a Mangopay wallet
     *
     * @since 3.5.0
     *
     * @param int|string $mp_user_id
     * @param string     $account_type
     * @param string     $currency     (Optional)
     *
     * @return object|\WP_Error
     */
    private static function add( $mp_user_id, $account_type, $currency = '' ) {
        if ( empty( $currency ) ) {
            $currency = get_woocommerce_currency();
        }

        $wallets = self::get( $mp_user_id );
        if ( ! empty( $wallets ) ) {
            foreach ( $wallets as $wallet ) {
                // Check that one wallet has the right currency
                if ( $wallet->Currency === $currency ) {
                    return $wallet;
                }
            }
        }

        try {
            $wallet				 = new MangoWallet();
            $wallet->Owners		 = array( $mp_user_id );
            $wallet->Description = "Dokan $account_type $currency Wallet";
            $wallet->Currency	 = $currency;
            return static::config()->mangopay_api->Wallets->Create( $wallet );
        } catch( Exception $e ) {
            self::log( sprintf( 'Could not add a wallet for user: %s. Message: %s.', $mp_user_id, $e->getMessage() ) );
            return new \WP_Error( 'dokan-mangopay-wallet-create-error', sprintf( 'Could not add a wallet. Message: %s.', $e->getMessage() ) );
        }
    }

    /**
     * Logs wallet related debugging info.
     *
     * @since 3.5.0
     *
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    public static function log( $message, $level = 'debug' ) {
        Helper::log( $message, 'Wallet', $level );
    }
}
