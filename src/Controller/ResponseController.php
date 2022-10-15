<?php

namespace App\Controller;

use App\ResponseValues;
use Psr\Log\LoggerInterface;
use League\Pipeline\Pipeline;
use App\Stage\FinalizeResponse;
use App\Stage\GenerateFakeData;
use App\Exception\ResponseBodyTooLarge;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Stage\HandleRepeatOptionForJsonListItems;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResponseController extends AbstractController
{
    protected const REQUEST_INPUT_MAX_LENGTH = 2000;

    public function handle(
        Request $request,
        LoggerInterface $logger,
        RateLimiterFactory $anonymousApiLimiter
    ) {
        $input = $this->getInput($request);

        if (count($input) === 0) {
            return $this->redirect('https://johannesss.github.io/http-response/');
        }

        $rateLimit = $this->getRateLimit(
            $anonymousApiLimiter,
            $request
        );

        if (!$rateLimit->isAccepted()) {
            return $this->tooManyRequestsResponse($rateLimit);
        }

        $pipeline = $this->buildPipeline($logger);

        try {
            $response = $pipeline->process(
                new ResponseValues($input)
            );
        } catch (ResponseBodyTooLarge $e) {
            throw new BadRequestHttpException('Generated response body too large');
        }

        return $response->send();
    }

    public function jsonResponse(
        Request $request,
        LoggerInterface $logger,
        RateLimiterFactory $anonymousApiLimiter
    ) {
        $rateLimit = $this->getRateLimit(
            $anonymousApiLimiter,
            $request
        );

        if (!$rateLimit->isAccepted()) {
            return $this->tooManyRequestsResponse($rateLimit);
        }

        $responseValues = [
            ResponseValues::KEY_STATUS_CODE => 200,
            ResponseValues::KEY_HEADERS     => [
                'content-type' => 'application/json',
            ],
        ];

        $pipeline = $this->buildPipeline($logger);

        $input = collect($this->getInput($request))
            ->except([
                ResponseValues::KEY_HEADERS,
            ])
            ->toArray();

        try {
            $response = $pipeline->process(
                new ResponseValues(array_merge($responseValues, $input))
            );
        } catch (ResponseBodyTooLarge $e) {
            throw new BadRequestHttpException('Generated response body too large');
        }

        return $response->send();
    }

    protected function buildPipeline(LoggerInterface $logger)
    {
        return (new Pipeline)
            ->pipe(new HandleRepeatOptionForJsonListItems)
            ->pipe(new GenerateFakeData($logger))
            ->pipe(new FinalizeResponse);
    }

    protected function getInput(Request $request)
    {
        $input = [];
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                $input = $request->toArray();
                break;

            case Request::METHOD_GET:
                $input = $request->query->all();
                break;
        }

        if (strlen(json_encode($input)) > self::REQUEST_INPUT_MAX_LENGTH) {
            throw new BadRequestHttpException('Request input too large');
        }

        return $input;
    }

    protected function getRateLimit(
        RateLimiterFactory $anonymousApiLimiter,
        Request $request
    ) {
        $limiter = $anonymousApiLimiter->create($request->getClientIp());

        return $limiter->consume();
    }

    protected function tooManyRequestsResponse(RateLimit $limit)
    {
        $headers = [
            'X-RateLimit-Remaining'   => $limit->getRemainingTokens(),
            'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
            'X-RateLimit-Limit'       => $limit->getLimit(),
        ];

        return new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
    }
}
