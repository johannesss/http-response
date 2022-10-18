<?php

namespace App\Controller;

use App\Response\ResponseValues;
use App\Response\ResponseGenerator;
use App\Exception\ResponseBodyTooLarge;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResponseController extends AbstractController
{
    public function handle(
        Request $request,
        RateLimiterFactory $anonymousApiLimiter
    ) {
        $input = $this->getInput($request);

        if (count($input) === 0) {
            return $this->redirect($this->getParameter('app.project_page_url'));
        }

        $rateLimit = $this->getRateLimit(
            $anonymousApiLimiter,
            $request
        );

        if (!$rateLimit->isAccepted()) {
            return $this->tooManyRequestsResponse($rateLimit);
        }

        $responseGenerator = $this->responseGenerator();

        try {
            $response = $responseGenerator->generate($input);
        } catch (ResponseBodyTooLarge $e) {
            throw new BadRequestHttpException('Requested response body too large');
        }

        return $response;
    }

    public function jsonResponse(
        Request $request,
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

        $input = collect($this->getInput($request))
            ->except([
                ResponseValues::KEY_HEADERS,
            ])
            ->toArray();

        $responseGenerator = $this->responseGenerator();

        try {
            $response = $responseGenerator->generate(array_merge($responseValues, $input));
        } catch (ResponseBodyTooLarge $e) {
            throw new BadRequestHttpException('Requested response body too large');
        }

        return $response;
    }

    protected function responseGenerator()
    {
        return new ResponseGenerator(
            $this->getParameter('app.response_body_max_length')
        );
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

        if (strlen(json_encode($input)) > $this->getParameter('app.request_input_max_length')) {
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
