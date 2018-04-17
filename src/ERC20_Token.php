<?php
/**
 * This file is a part of "furqansiddiqui/erc20-php" package.
 * https://github.com/furqansiddiqui/erc20-php
 *
 * Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/erc20-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace ERC20;

use ERC20\Exception\ERC20Exception;
use EthereumRPC\Contracts\Contract;
use EthereumRPC\Validator;

/**
 * Class ERC20_Token
 * @package ERC20
 */
class ERC20_Token extends Contract
{
    /** @var null|string */
    private $_name;
    /** @var null|string */
    private $_symbol;
    /** @var null|int */
    private $_decimals;

    /**
     * @return string
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \Exception
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function name(): string
    {
        if ($this->_name) {
            return $this->_name;
        }

        $result = $this->call("name");
        $name = $result[0] ?? null;
        if (!is_string($name)) {
            throw new ERC20Exception('Failed to retrieve ERC20 token name');
        }

        $this->_name = $this->trim($name);
        return $this->_name;
    }

    /**
     * @return string
     * @throws ERC20Exception
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \Exception
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function symbol(): string
    {
        if ($this->_symbol) {
            return $this->_symbol;
        }

        $result = $this->call("symbol");
        $code = $result[0] ?? null;
        if (!is_string($code)) {
            throw new ERC20Exception('Failed to retrieve ERC20 token symbol');
        }

        $this->_symbol = $this->trim($code);
        return $this->_symbol;
    }

    /**
     * @return int
     * @throws ERC20Exception
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \Exception
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function decimals(): int
    {
        if ($this->_decimals) {
            return $this->_decimals;
        }

        $result = $this->call("decimals");
        $scale = $result[0] ?? null;
        if (!is_int($scale)) {
            throw new ERC20Exception('Failed to retrieve ERC20 token decimals/scale value');
        }

        $this->_decimals = $scale;
        return $this->_decimals;
    }

    /**
     * @param string $address
     * @param bool $scaled
     * @return string
     * @throws \EthereumRPC\Exception\ConnectionException
     * @throws \EthereumRPC\Exception\ContractABIException
     * @throws \EthereumRPC\Exception\GethException
     * @throws \Exception
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function balanceOf(string $address, bool $scaled = true): string
    {
        if (!Validator::Address($address)) {
            throw new ERC20Exception('Invalid address to check balance of');
        }

        $result = $this->call("balanceOf", [$address]);
        $balance = $result["balance"] ?? null;
        if (!is_int($balance)) {
            throw new ERC20Exception(
                sprintf('Failed to retrieve ERC20 token balance of address "%s..."', substr($address, 0, 8))
            );
        }

        $balance = number_format($balance, 0, '.', ''); // convert to string
        if (!$scaled) {
            return $balance;
        }

        $scale = $this->decimals();
        return bcdiv($balance, bcpow("10", strval($scale), 0), $scale);
    }

    /**
     * @param string $in
     * @return string
     */
    private function trim(string $in): string
    {
        return preg_replace('/[^\w]/', '', trim($in));
    }
}