<?php

declare(strict_types=1);

namespace Aimating\Graphql;
use GraphQL\Type\Schema;
use GraphQL\Error\DebugFlag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\StandardServer;
use GraphQL\Upload\UploadMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use TheCodingMachine\GraphQLite\Http\HttpCodeDeciderInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function array_map;
use function json_decode;
use function json_last_error;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GraphQLProvider
{
    /**
     * Summary of schema
     * @var Schema
     */
    private $schema;

    public function __construct(
        private StandardServer $standardServer,
        private HttpCodeDeciderInterface $httpCodeDecider, 
        private ResponseFactoryInterface $responseFactory,
        private ?int $debug = DebugFlag::RETHROW_UNSAFE_EXCEPTIONS|DebugFlag::INCLUDE_TRACE
        )
    {
       
       

    }


    public function process(ServerRequestInterface $request): ResponseInterface
    {
        if (strtoupper($request->getMethod()) === "POST" && empty($request->getParsedBody())) {
            $content = $request->getBody()->getContents();
            $parsedBody = json_decode($content);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON received in POST body: '.json_last_error_msg());
            }
            $request = $request->withParsedBody($parsedBody);
        }
        
        if (class_exists('\GraphQL\Upload\UploadMiddleware')) {
            // Let's parse the request and adapt it for file uploads.
            /**
             * @var UploadMiddleware
             */
            $uploadMiddleware = new UploadMiddleware();
            $request = $uploadMiddleware->processRequest($request);
        }
        
        return $this->createResponse($this->handleRequest($request));

    }


    protected function handleRequest(ServerRequestInterface $request): JsonResponse
    {
        $result = $this->standardServer->executePsrRequest($request);

        $httpCodeDecider = $this->httpCodeDecider;
        if ($result instanceof ExecutionResult) {
            return new JsonResponse($result->toArray($this->debug), $httpCodeDecider->decideHttpStatusCode($result));
        }
        if (is_array($result)) {
            $finalResult =  array_map(function (ExecutionResult $executionResult) {
                return new JsonResponse($executionResult->toArray($this->debug));
            }, $result);
            // Let's return the highest result.
            $statuses = array_map([$httpCodeDecider, 'decideHttpStatusCode'], $result);
            $status = max($statuses);
            return new JsonResponse($finalResult, $status);
        }
        if ($result instanceof Promise) {
            throw new RuntimeException('Only SyncPromiseAdapter is supported');
        }
        throw new RuntimeException('Unexpected response from StandardServer::executePsrRequest');
    }

    protected function createResponse(JsonResponse $symfonyResponse): ResponseInterface
    {
        /**
         * @var \Hyperf\HttpServer\Response
         */
        $response = $this->responseFactory->createResponse($symfonyResponse->getStatusCode(), Response::$statusTexts[$symfonyResponse->getStatusCode()] ?? '');

        if ($symfonyResponse instanceof BinaryFileResponse && !$symfonyResponse->headers->has('Content-Range')) {
           $response = $response->download($symfonyResponse->getFile()->getPathname());
             
        } else {
            $stream = new SwooleStream();
            if ($symfonyResponse instanceof StreamedResponse || $symfonyResponse instanceof BinaryFileResponse) {
                ob_start(function ($buffer) use ($stream) {
                    $stream->write($buffer);

                    return '';
                }, 1);

                $symfonyResponse->sendContent();
                ob_end_clean();
            } else {
                $stream->write($symfonyResponse->getContent());
            }
        }

        $response = $response->withBody($stream);

        $headers = $symfonyResponse->headers->all();
        $cookies = $symfonyResponse->headers->getCookies();
        if ($cookies) {
            $headers['Set-Cookie'] = [];

            foreach ($cookies as $cookie) {
                $headers['Set-Cookie'][] = $cookie->__toString();
            }
        }

        foreach ($headers as $name => $value) {
            try {
                $response = $response->withHeader($name, $value);
            } catch (\InvalidArgumentException $e) {
                // ignore invalid header
            }
        }

        $protocolVersion = $symfonyResponse->getProtocolVersion();

        return $response->withProtocolVersion($protocolVersion);
    }
    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    protected function isPost(ServerRequestInterface $request) {
       
        return strtoupper($request->getMethod()) === 'POST';
    }
}
