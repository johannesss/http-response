<?php

namespace App\Stage;

use League\Pipeline\StageInterface;
use Symfony\Component\HttpFoundation\Response;

class FinalizeResponse implements StageInterface
{
    protected $request;

    public function __invoke($payload)
    {
        $response = new Response;

        $response->setStatusCode($payload->statusCode);

        foreach ($payload->headers as $headerName => $value) {
            $response->headers->set($headerName, $value);
        }

        $response->setContent($payload->body);

        return $response;
    }
}
