<?php

namespace Hathoriel\NftMaker\Services;

use Hathoriel\NftMaker\Connectors\DbConnector;
use Hathoriel\NftMaker\Connectors\IpfsConnector;
use Hathoriel\NftMaker\Connectors\ScConnector;
use Hathoriel\NftMaker\Connectors\TatumConnector;
use Hathoriel\NftMaker\Utils\BlockchainLink;
use Hathoriel\NftMaker\Utils\Constants;

class NftService
{
    private $tatumConnector;
    private $dbConnector;
    private $scConnector;
    private $ipfsConnector;

    public function __construct() {
        $this->dbConnector = new DbConnector();
        $this->tatumConnector = new TatumConnector();
        $this->scConnector = new ScConnector();
        $this->ipfsConnector = new IpfsConnector();
    }

    public function getPrepared() {
        $nfts = $this->dbConnector->getPrepared();
        return self::formatPreparedNfts($nfts);
    }

    public function formatPreparedNfts($nfts) {
        $prepared = [];
        foreach ($nfts as $nft) {
            $product = wc_get_product($nft->product_id);
            if ($product) {
                array_push($prepared, self::formatPreparedNft($product, $nft));
            }
        }
        return $prepared;
    }

    public function formatMintedNfts($nfts, $addTokenId = true) {
        $minted = [];
        foreach ($nfts as $nft) {
            $order = wc_get_order($nft->order_id);
            $product = wc_get_product($nft->product_id);
            if ($order && $product) {
                $nftPrepared = self::formatPreparedNft($product, $nft);
                $nftMinted = self::formatMintedNft($order, $nft, $addTokenId);
                array_push($minted, array_merge($nftPrepared, $nftMinted));
            }
        }
        return $minted;
    }

    public function getMinted() {
        $nfts = $this->dbConnector->getMinted();
        return $this->formatMintedNfts($nfts);
    }

    public function getPreparedCount() {
        $nfts = $this->dbConnector->getPrepared();
        return count($this->formatPreparedNfts($nfts));
    }

    public function getMintedCount() {
        $nfts = $this->dbConnector->getMinted();
        return count($this->formatMintedNfts($nfts, false));
    }


    private static function formatPreparedNft($product, $nft) {
        $datetime_created = $product->get_date_created();
        return [
            "name" => $product->get_title(),
            "imageUrl" => wp_get_attachment_image_url($product->get_image_id(), 'full'),
            "chain" => $nft->chain,
            "created" => $datetime_created,
            "productId" => $product->get_id()
        ];
    }

    private function formatMintedNft($order, $nft, $addTokenId = true) {
        $formatted = [
            "transactionId" => $nft->transaction_id,
            "transactionLink" => BlockchainLink::tx($nft->transaction_id, $nft->chain, $nft->testnet),
            "errorCause" => $nft->error_cause,
            "sold" => $order->get_date_paid(),
        ];

        if ($addTokenId) {
            $nftDetail = $this->getNftDetail($nft->chain, $nft->transaction_id, $nft->testnet);

            if (array_key_exists("openSeaUrl", $nftDetail) && array_key_exists("tokenId", $nftDetail)) {
                $formatted['openSeaUrl'] = $nftDetail['openSeaUrl'];
                $formatted['tokenId'] = $nftDetail['tokenId'];
            }
            $formatted['contractAddress'] = $nftDetail['contractAddress'];
        }

        return $formatted;
    }


    private function getNftTokenId($transaction, $chain) {
        try {
            $tx = $this->tatumConnector->getNftTransaction($chain, $transaction);
            return hexdec(substr($tx['input'], 74, 64));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getNftDetail($chain, $txId, $testnet) {
        $tokenId = $this->getNftTokenId($txId, $chain);
        $isTestnet = $testnet ? 'TESTNET' : 'MAINNET';
        if (is_null($tokenId)) {
            return [
                'contractAddress' => Constants::CONTRACT_ADDRESS[$isTestnet][$chain],
            ];
        }
        return [
            'contractAddress' => Constants::CONTRACT_ADDRESS[$isTestnet][$chain],
            'openSeaUrl' => BlockchainLink::openSea($tokenId, $chain, $testnet),
            'tokenId' => $tokenId
        ];
    }

    public function getNFTsByAddressAndChain($chains, $address) {
        $filteredChains = array_filter($chains, function ($chain) { return in_array($chain, Constants::SUPPORTED_CHAINS_FETCH_NFTS_BY_ADDRESS); });
        $nfts = [];
        foreach ($filteredChains as $chain) {
            $nfts[$chain] = $this->tatumConnector->getNftTokensByAddress($chain, $address);
        }
        return $nfts;
    }

    public function getNftMetadata($chain, $tokenId, $contractAddress = null) {
        $url = $this->scConnector->getTokeUrlByTokenId($tokenId, $chain, $contractAddress)['data'];
        return $this->formatIpfsMetadata($url, $tokenId);
    }

    public function formatIpfsMetadata($url, $tokenId) {
        $jsonMetadata = $this->getNftMetadataFromIpfs($url);
        if ($jsonMetadata !== null) {
            return ['url' => $url, 'metadata' => $jsonMetadata, 'tokenId' => $tokenId];
        }
        return null;
    }

    public function getNftMetadataFromIpfs($url) {
        $ipfsMetadataUrl = explode('ipfs://', $url);
        if (count($ipfsMetadataUrl) > 1) {
            $metadataResponse = wp_remote_get("https://ipfs.io/ipfs/" . $ipfsMetadataUrl[1], ['timeout' => 120]);
            $metadataBody = wp_remote_retrieve_body($metadataResponse);
            return json_decode($metadataBody, true);
        }
        return null;
    }

    public function getNftsByAddressTatum($chain, $address) {
        $tokenIds = $this->tatumConnector->getNftTokenBalance($chain, $address, Constants::CONTRACT_ADDRESS['MAINNET'][$chain])['data'];
        $nftsMetadata = [];
        foreach ($tokenIds as $tokenId) {
            $metadata = $this->getNftMetadata($chain, $tokenId, Constants::CONTRACT_ADDRESS['MAINNET'][$chain]);
            if ($metadata !== null) {
                $nftsMetadata[] = $metadata;
            }
        }
        return [['metadata' => $nftsMetadata, 'contractAddress' => Constants::CONTRACT_ADDRESS['MAINNET'][$chain]]];
    }

    public function getNftsByAddressSc($address, $chain) {
        $addressCount = $this->scConnector->countNFTsByAddress($address, $chain);
        $nftsMetadata = [];
        for ($i = 0; $i < $addressCount['data']; $i++) {
            $tokenId = $this->scConnector->getTokenOfOwnerByIndex($i, $address, $chain);
            $metadata = $this->getNftMetadata($chain, $tokenId['data']);
            if ($metadata !== null) {
                $nftsMetadata[] = $metadata;
            }
        }
        return [['metadata' => $nftsMetadata, 'contractAddress' => Explorer::CONTRACT_ADDRESSES['XDC']]];
    }

    public function getBscNfts($address) {
        $nfts = $this->getNftsByAddressTatum('BSC', $address);
        $alreadyMintedNfts = $this->dbConnector->getExplorer($address, 'BSC');
        $aggregatedContracts = [];
        foreach ($alreadyMintedNfts as $explorerNft) {
            $aggregatedContracts[$explorerNft->smartContractAddress][] = $explorerNft;
        }

        foreach ($aggregatedContracts as $contract => $aggregatedContract) {
            $contractTokens = [];
            foreach ($aggregatedContract as $tokens) {
                try {
                    $metadata = $this->getNftMetadata('BSC', $tokens->tokenId, $tokens->smartContractAddress);
                    if ($metadata !== null) {
                        $contractTokens[] = $metadata;
                    }
                } catch (\Exception $e) {
                }
            }
            if (count($contractTokens) > 0) {
                $nfts[] = ['metadata' => $contractTokens, 'contractAddress' => $contract];
            }
        }
        return $nfts;
    }

    public function getXdcNfts($address) {
        $nfts = $this->getNftsByAddressSc($address, 'XDC');

        $aggregatedContracts = [];
        $explorerNfts = $this->dbConnector->getExplorer($address, 'XDC');

        foreach ($explorerNfts as $explorerNft) {
            $aggregatedContracts[$explorerNft->smartContractAddress][] = $explorerNft;
        }

        foreach ($aggregatedContracts as $contract => $aggregatedContract) {
            $contractTokens = [];
            foreach ($aggregatedContract as $tokens) {
                try {
                    $metadata = $this->getNftMetadata('XDC', $tokens->tokenId, $tokens->smartContractAddress);
                    if ($metadata !== null) {
                        $contractTokens[] = $metadata;
                    }
                } catch (\Exception $e) {
                }
            }
            if (count($contractTokens) > 0) {
                $nfts[] = ['metadata' => $contractTokens, 'contractAddress' => $contract];
            }
        }
        return $nfts;
    }

    public function addMinted($address, $chain, $tokenId, $smartContractAddress) {
        $checkIfAlreadyAdded = $this->dbConnector->getAlreadyAddedAddress($address, $chain, $tokenId, $smartContractAddress);
        if ($checkIfAlreadyAdded) {
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Token is already added.'
            ]);
        }

        try {
            if ($chain === 'XDC') {
                if (strtolower(Constants::CONTRACT_ADDRESS['MAINNET']['XDC']) === strtolower($smartContractAddress)) {
                    return new WP_REST_Response([
                        'status' => 'error',
                        'message' => 'Token is already displayed.'
                    ]);
                }

                $nftOwner = $this->scConnector->getOwnerOf($tokenId, $chain, $smartContractAddress);
                if (strtolower($nftOwner['data']) === strtolower($address)) {
                    $url = $this->scConnector->getTokeUrlByTokenId($tokenId, 'XDC', $smartContractAddress)['data'];
                    $jsonMetadata = $this->getNftMetadataFromIpfs($url);
                    if ($jsonMetadata !== null) {
                        $this->dbConnector->insertAlreadyMinted($tokenId, $smartContractAddress, $chain, $address);
                        return new WP_REST_Response(['url' => $url, 'metadata' => $jsonMetadata, 'tokenId' => $tokenId]);
                    }
                }

                return new WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Cannot find nft image, nft dont exists or its owned by another address.'
                ]);
            } else if ($chain === 'BSC') {
                $tokenIds = $this->tatumConnector->getNftTokenBalance('BSC', $address, $smartContractAddress)['data'];
                if (in_array($tokenId, $tokenIds)) {
                    $metadata = $this->tatumConnector->getNftTokenMetadata($chain, $tokenId, $smartContractAddress);
                    $this->dbConnector->insertAlreadyMinted($tokenId, $smartContractAddress, $chain, $address);
                    return $metadata;
                }

                return new WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Cannot find nft image, nft dont exists or its owned by another address.'
                ]);
            }
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Not supported chain.'
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'nft.error') {
                return new WP_REST_Response([
                    'status' => 'error',
                    'message' => 'Cannot find nft image.'
                ]);
            }
            return new WP_REST_Response([
                'status' => 'error',
                'message' => 'Cannot find nft image, nft dont exists or its owned by another address.'
            ]);
        }
    }

    public function getNftsByAddress($chains, $address) {
        $nfts = $this->getNFTsByAddressAndChain($chains, $address);

        if (in_array('BSC', $chains)) {
            $nfts['BSC'] = $this->getBscNfts($address);
        }

        if (in_array('XDC', $chains)) {
            $nfts['XDC'] = $this->getXdcNfts($address);
        }

        return new WP_REST_Response($nfts);
    }

    public function mintXdc($name, $description, $file, $address, $privateKey) {
        $ipfsHash = $this->ipfsConnector->uploadCustomImage($name, $description, $file['tmp_name']);
        $txId = $this->scConnector->mintNft($address, "ipfs://$ipfsHash", $privateKey, 'XDC');
        return ['txId' => $txId['txId'], 'ipfs' => "ipfs://$ipfsHash"];
    }

}