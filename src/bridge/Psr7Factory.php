<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Oauth2\bridge;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use yii\web\Request as YiiRequest;
use yii\web\Response as YiiResponse;

/**
 * PSR-7 Bridge for converting between Yii2 and PSR-7 requests/responses
 */
class Psr7Factory
{
    /**
     * Convert Yii2 Request to PSR-7 ServerRequest
     */
    public static function createServerRequest(YiiRequest $yiiRequest): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        $psrRequest = $creator->fromGlobals();

        // Handle different content types
        $contentType = $yiiRequest->getContentType();
        $rawBody = $yiiRequest->getRawBody();
        $parsedBody = [];

        if (!empty($rawBody)) {
            // Support for JSON (like filsh/yii2-oauth2-server)
            if (stripos($contentType, 'application/json') !== false) {
                $jsonData = json_decode($rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $parsedBody = $jsonData;
                    \Yii::info('Parsed JSON request body: ' . json_encode($parsedBody), __METHOD__);
                }
            }
            // If Content-Type is missing or empty, try to parse as form-urlencoded
            elseif (empty($contentType)) {
                parse_str($rawBody, $parsedBody);
                if (!empty($parsedBody)) {
                    \Yii::info('Manually parsed form-urlencoded body: ' . json_encode($parsedBody), __METHOD__);
                }
            }

            if (!empty($parsedBody)) {
                $psrRequest = $psrRequest->withParsedBody($parsedBody);
            }
        }

        return $psrRequest;
    }

    /**
     * Convert PSR-7 Response to Yii2 Response
     */
    public static function populateYiiResponse(ResponseInterface $psrResponse, YiiResponse $yiiResponse): void
    {
        $yiiResponse->setStatusCode($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $yiiResponse->headers->add($name, $value);
            }
        }

        $yiiResponse->content = (string)$psrResponse->getBody();
    }
}
