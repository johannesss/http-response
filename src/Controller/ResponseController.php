<?php

namespace App\Controller;

use App\ResponseValues;
use Psr\Log\LoggerInterface;
use League\Pipeline\Pipeline;
use App\Stage\FinalizeResponse;
use App\Stage\GenerateFakeData;
use Symfony\Component\HttpFoundation\Request;
use App\Stage\HandleRepeatOptionForJsonListItems;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ResponseController extends AbstractController
{
    public function handle(Request $request, LoggerInterface $logger)
    {
        $input = null;
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                $input = $request->toArray();
                break;

            case Request::METHOD_GET:
                $input = $request->query->all();
                break;
        }

        $pipeline = $this->buildPipeline($logger);

        $response = $pipeline->process(
            new ResponseValues($input)
        );

        // $response->prepare($request);
        return $response->send();
    }

    public function jsonResponse(Request $request, LoggerInterface $logger)
    {
        $input = null;
        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                $input = $request->toArray();
                break;

            case Request::METHOD_GET:
                $input = $request->query->all();
                break;
        }

        $responseValues = [
            ResponseValues::KEY_STATUS_CODE => 200,
            ResponseValues::KEY_HEADERS     => [
                'content-type' => 'application/json',
            ],
        ];

        $pipeline = $this->buildPipeline($logger);

        $input = collect($input)
            ->except([
                ResponseValues::KEY_HEADERS,
            ])
            ->toArray();

        $response = $pipeline->process(
            new ResponseValues(array_merge($responseValues, $input))
        );

        // $response->prepare($request);
        return $response->send();
    }

    protected function buildPipeline(LoggerInterface $logger)
    {
        return (new Pipeline)
            ->pipe(new HandleRepeatOptionForJsonListItems)
            ->pipe(new GenerateFakeData($logger))
            ->pipe(new FinalizeResponse);
    }
}
