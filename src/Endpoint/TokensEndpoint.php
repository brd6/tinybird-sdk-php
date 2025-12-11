<?php

declare(strict_types=1);

namespace Brd6\TinybirdSdk\Endpoint;

use Brd6\TinybirdSdk\Constant\ContentType;
use Brd6\TinybirdSdk\RequestParameters\CreateJwtTokenParams;
use Brd6\TinybirdSdk\RequestParameters\CreateTokenParams;
use Brd6\TinybirdSdk\RequestParameters\UpdateTokenParams;
use Brd6\TinybirdSdk\Resource\DeleteResult;
use Brd6\TinybirdSdk\Resource\Token;
use Brd6\TinybirdSdk\Resource\TokensList;

use const JSON_THROW_ON_ERROR;

/**
 * Token API endpoint.
 *
 * List, create, update, refresh, or delete Static Tokens.
 * Requires a Token with TOKENS or ADMIN scope.
 *
 * @see https://www.tinybird.co/docs/api-reference/token-api
 */
class TokensEndpoint extends AbstractEndpoint
{
    private const PATH = '/tokens';

    /**
     * GET /v?/tokens
     *
     * List all workspace Static Tokens.
     */
    public function list(): TokensList
    {
        return TokensList::fromArray($this->get(self::PATH));
    }

    /**
     * GET /v?/tokens/{token_name}
     *
     * Get information about a specific token.
     */
    public function retrieve(string $tokenName): Token
    {
        return Token::fromArray($this->get(self::PATH . '/' . $tokenName));
    }

    /**
     * POST /v?/tokens
     *
     * Create a new Static Token.
     */
    public function create(CreateTokenParams $params): Token
    {
        return Token::fromArray(
            $this->post(
                self::PATH,
                $params->toFormString(),
                [],
                ['Content-Type' => ContentType::FORM_URLENCODED],
            ),
        );
    }

    /**
     * POST /v?/tokens?expiration_time={timestamp}
     *
     * Create a new JWT Token with expiration and optional fixed parameters.
     */
    public function createJwt(CreateJwtTokenParams $params): Token
    {
        return Token::fromArray(
            $this->post(
                self::PATH,
                json_encode($params->toBody(), JSON_THROW_ON_ERROR),
                $params->toQueryParams(),
                ['Content-Type' => ContentType::JSON],
            ),
        );
    }

    /**
     * PUT /v?/tokens/{token_name}
     *
     * Update a Static Token. New scopes override existing ones.
     */
    public function update(string $tokenName, UpdateTokenParams $params): Token
    {
        return Token::fromArray(
            $this->put(
                self::PATH . '/' . $tokenName,
                $params->toFormString(),
                [],
                ['Content-Type' => ContentType::FORM_URLENCODED],
            ),
        );
    }

    /**
     * POST /v?/tokens/{token_name}/refresh
     *
     * Refresh a Static Token without modifying its attributes.
     * Useful for rotating tokens or when a token is leaked.
     */
    public function refresh(string $tokenName): Token
    {
        return Token::fromArray(
            $this->post(self::PATH . '/' . $tokenName . '/refresh'),
        );
    }

    /**
     * DELETE /v?/tokens/{token_name}
     *
     * Delete a Static Token.
     */
    public function remove(string $tokenName): DeleteResult
    {
        return DeleteResult::fromArray($this->delete(self::PATH . '/' . $tokenName));
    }
}
