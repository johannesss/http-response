<?php

namespace App\Response;

use League\Pipeline\Pipeline;
use App\Response\Pipe\FinalizeResponse;
use App\Response\Pipe\GenerateFakeData;
use App\Response\Pipe\RepeatJsonListItems;

class ResponseGenerator
{
    protected $responseBodyMaxLength;

    public function __construct(int $responseBodyMaxLength)
    {
        $this->responseBodyMaxLength = $responseBodyMaxLength;
    }

    public function generate(array $input)
    {
        $responseValues = new ResponseValues($input, $this->responseBodyMaxLength);

        $pipeline = (new Pipeline)
            ->pipe(new RepeatJsonListItems)
            ->pipe(new GenerateFakeData)
            ->pipe(new FinalizeResponse);

        return $pipeline->process($responseValues);
    }

}
