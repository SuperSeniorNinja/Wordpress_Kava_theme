<?php

namespace Hathoriel\Tatum\rest;

use Hathoriel\NftMaker\Services\DokanService;
use Hathoriel\NftMaker\Services\EstimateService;
use Hathoriel\NftMaker\Services\NftService;

use Hathoriel\NftMaker\Services\SetupService;
use Hathoriel\Utils\Service;
use Hathoriel\Tatum\base\UtilsProvider;
use WP_REST_Response;
use WP_REST_Request;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Create an example REST Service.
 *
 * @codeCoverageIgnore Example implementations gets deleted the most time after plugin creation!
 */
class SetupRest
{
    use UtilsProvider;
    public $estimateService;
    public $setupService;
    public $nftService;

    /**
     * C'tor.
     */
    private function __construct() {
        $this->estimateService = new EstimateService();
        $this->setupService = new SetupService();
        $this->nftService = new NftService();
    }

    /**
     * Register endpoints.
     */
    public function rest_api_init() {
        $this->registerRoute('/setup', 'GET', 'getSetup');
        $this->registerRoute('/api-key', 'POST', 'setApiKey');
        $this->registerRoute('/estimate', 'GET', 'estimate');
        $this->registerRoute('/api-key', 'GET', 'getApiKey');
        $this->registerRoute('/dismiss-tutorial', 'POST', 'dismissTutorial');
        $this->registerRoute('/nfts/lazy', 'GET', 'getLazy');
        $this->registerRoute('/nfts/minted', 'GET', 'getMinted');
        $this->registerRoute('/explorer', 'GET', 'getNftsByAddress');
        $this->registerRoute('/dokan/recent-stores', 'GET', 'getDokanLastVisitedStores');
        $this->registerRoute('/explorer/add-minted', 'POST', 'addMinted');
        $this->registerRoute('/explorer/mint', 'POST', 'mint');
    }

    /**
     * See API docs.
     *
     * @api {get} /tatum/v1/setup Return setup
     * @apiHeader {string} X-WP-Nonce
     * @apiName GetSetup
     * @apiGroup Setup
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     "isWoocommerceInstalled": true"
     * }
     * @apiVersion 0.1.0
     */
    public function getSetup() {
        self::checkNonce();
        return new WP_REST_Response($this->setupService->getSetup());
    }

    public function setApiKey(WP_REST_Request $request) {
        self::checkNonce();
        $data = $request->get_json_params();
        $response = $this->setupService->setApiKey($data['apiKey']);
        return new WP_REST_Response($response);
    }

    public function estimate() {
        self::checkNonce();
        return new WP_REST_Response(["estimates" => $this->estimateService->estimateCountOfMintAllSupportedBlockchain()]);
    }

    public function getApiKey() {
        self::checkNonce();
        $api_key = $this->setupService->getApiKey();
        return new WP_REST_Response($api_key);
    }

    public function dismissTutorial() {
        self::checkNonce();
        SetupService::dismissTutorial();
        return new WP_REST_Response([]);
    }

    public function getLazy() {
        self::checkNonce();
        return new WP_REST_Response(["nfts" => $this->nftService->getPrepared()]);
    }

    public function getMinted() {
        self::checkNonce();
        return new WP_REST_Response(["nfts" => $this->nftService->getMinted()]);
    }

    public function mint(WP_REST_Request $request) {
        $file = $request->get_file_params()['file'];
        $name = $request->get_param('name');
        $description = $request->get_param('description');
        $address = $request->get_param('address');
        $privateKey = $request->get_param('privateKey');

        $result = $this->nftService->mintXdc($name, $description, $file, $address, $privateKey);
        return new WP_REST_Response($result);
    }

    public function addMinted(WP_REST_Request $request) {
        self::checkNonce();
        $body = json_decode($request->get_body());
        return $this->nftService->addMinted($body->address, $body->chain, $body->tokenId, $body->smartContractAddress);
    }

    public function getNftsByAddress(WP_REST_Request $request) {
        $chains = explode(",", $request->get_param('chains'));
        $address = $request->get_param('address');

        $nfts = $this->nftService->getNftsByAddress($chains, $address);

        return new WP_REST_Response($nfts);
    }

    public function getDokanLastVisitedStores() {
        self::checkNonce();
        return new WP_REST_Response(['recentStores' => DokanService::getLastVisitedStores()]);
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new SetupRest();
    }

    private function registerRoute($path, $method, $callback) {
        $namespace = Service::getNamespace($this);
        register_rest_route($namespace, $path, [
            'methods' => $method,
            'callback' => [$this, $callback],
            'permission_callback' => '__return_true'
        ]);
    }

    private static function checkNonce() {
        if (!isset($_SERVER['HTTP_X_WP_NONCE'])) {
            throw new \Exception('Only admin can view this page');
        }
        $nonce = $_SERVER['HTTP_X_WP_NONCE'];
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            throw new \Exception('Only admin can view this page');
        }
    }
}
