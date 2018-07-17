<?php
declare(strict_types = 1);

namespace GuzzleRetry;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Logger;

class GuzzleHandler
{
    const MAX_RETRIES = 10;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * GuzzleHandler constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient ()
    {
        $handler_stack = \GuzzleHttp\HandlerStack::create();

        $handler_stack->push(\GuzzleHttp\Middleware::retry($this->retryHandler($this->logger), $this->retryDelay()));

        return new \GuzzleHttp\Client(['handler' => $handler_stack]);

    }

    public function retryHandler (Logger $logger)
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $requestException = null
        ) use ($logger) {

            if ($retries >= self::MAX_RETRIES) {
                return false;
            }

            if (!($this->isServerError($response) || $this->isConnectError($requestException))) {
                return false;
            }

            $logger->info(sprintf(
                'Retrying %s %s %s/%s, %s',
                $request->getMethod(),
                $request->getUri(),
                $retries + 1,
                self::MAX_RETRIES,
                $response ? 'status code: ' . $response->getStatusCode() : $requestException->getMessage()
            ), [$request->getHeader('Host')[0]]);

            return true;
        };
    }

    /**
     * @param Response|null $response
     * @return bool
     */
    function isServerError(Response $response = null)
    {
        return $response && $response->getStatusCode() >= 500;
    }

    /**
     * @param RequestException|null $exception
     * @return bool
     */
    function isConnectError(RequestException $exception = null)
    {
        return $exception instanceof ConnectException;
    }

    /**
     * delay 1s 2s 3s 4s 5s
     */
    public function retryDelay()
    {
        return function ($numberOfRetries) {
           return 1000 * $numberOfRetries;
        };
    }
}
