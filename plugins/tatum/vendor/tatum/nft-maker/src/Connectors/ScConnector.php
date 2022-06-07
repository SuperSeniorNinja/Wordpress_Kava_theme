<?php

namespace Hathoriel\NftMaker\Connectors;

use Hathoriel\NftMaker\Connectors\TatumConnector;
use Hathoriel\NftMaker\Utils\Constants;

class ScConnector
{
    private $tatumConnector;

    public function __construct() {
        $this->tatumConnector = new TatumConnector();
    }
    public function mintNft($recipient_address, $url, $privateKey, $chain) {
        $body = [
            'contractAddress' => Constants::CONTRACT_ADDRESS['MAINNET'][$chain],
            'methodName' => 'safeMint',
            'methodABI' => [
                'inputs' => [
                    [
                        'internalType' => 'address',
                        'name' => 'to',
                        'type' => 'address'
                    ],
                    [
                        'internalType' => 'string',
                        'name' => 'uri',
                        'type' => 'string'
                    ]
                ],
                'name' => 'safeMint',
                'outputs' => [],
                'stateMutability' => 'nonpayable',
                'type' => 'function'
            ],
            'params' => [$recipient_address, "ipfs://$url"],
            'fromPrivateKey' => $privateKey,
            'fee' => [
                'gasLimit' => '5000000',
                'gasPrice' => '10'
            ]
        ];
        return $this->tatumConnector->callScMethod($chain, $body);
    }

    public function countNFTsByAddress($address, $chain) {
        $body = [
            'contractAddress' => Constants::CONTRACT_ADDRESS['MAINNET'][$chain],
            'methodName' => 'balanceOf',
            'methodABI' => [
                'inputs' => [
                    [
                        'internalType' => 'address',
                        'name' => 'owner',
                        'type' => 'address'
                    ]
                ],
                'name' => 'balanceOf',
                'outputs' => [[
                    'internalType' => 'uint256',
                    'name' => '',
                    'type' => 'uint256'
                ]],
                'stateMutability' => 'view',
                'type' => 'function'
            ],
            'params' => [$address],
        ];
        return $this->tatumConnector->callScMethod($chain, $body);
    }
    public function getTokenOfOwnerByIndex($index, $address, $chain) {
        $body = [
            'contractAddress' => Constants::CONTRACT_ADDRESS['MAINNET'][$chain],
            'methodName' => 'tokenOfOwnerByIndex',
            'methodABI' => [
                'inputs' => [
                    [
                        'internalType' => 'address',
                        'name' => 'owner',
                        'type' => 'address'
                    ],
                    [
                        'internalType' => 'uint256',
                        'name' => 'index',
                        'type' => 'uint256'
                    ]
                ],
                'name' => 'tokenOfOwnerByIndex',
                'outputs' => [[
                    'internalType' => 'uint256',
                    'name' => '',
                    'type' => 'uint256'
                ]],
                'stateMutability' => 'view',
                'type' => 'function'
            ],
            'params' => [$address, $index],
        ];
        return $this->tatumConnector->callScMethod($chain, $body);
    }

    public function getTokeUrlByTokenId($tokenId, $chain, $contractAddress = null) {
        $body = [
            'contractAddress' => $contractAddress === null ? Constants::CONTRACT_ADDRESS['MAINNET'][$chain] : $contractAddress,
            'methodName' => 'tokenURI',
            'methodABI' => [
                'inputs' => [
                    [
                        'internalType' => 'uint256',
                        'name' => 'tokenId',
                        'type' => 'uint256'
                    ]
                ],
                'name' => 'tokenURI',
                'outputs' => [[
                    'internalType' => 'string',
                    'name' => '',
                    'type' => 'string'
                ]],
                'stateMutability' => 'view',
                'type' => 'function'
            ],
            'params' => [$tokenId],
        ];
        return $this->tatumConnector->callScMethod($chain, $body);
    }

    public function getOwnerOf($tokenId, $chain, $smartContractAddress) {
        $body = [
            'contractAddress' => $smartContractAddress,
            'methodName' => 'ownerOf',
            'methodABI' => [
                'inputs' => [
                    [
                        'internalType' => 'uint256',
                        'name' => 'tokenId',
                        'type' => 'uint256'
                    ]
                ],
                'name' => 'ownerOf',
                'outputs' => [[
                    'internalType' => 'address',
                    'name' => '',
                    'type' => 'address'
                ]],
                'stateMutability' => 'view',
                'type' => 'function'
            ],
            'params' => [$tokenId],
        ];
        return $this->tatumConnector->callScMethod($chain, $body);
    }
}