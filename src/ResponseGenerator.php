<?php

namespace App;

use League\Pipeline\Pipeline;
use App\Stage\FinalizeResponse;
use App\Stage\GenerateFakeData;
use App\Stage\HandleRepeatOptionForJsonListItems;

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
            ->pipe(new HandleRepeatOptionForJsonListItems)
            ->pipe(new GenerateFakeData)
            ->pipe(new FinalizeResponse);

        return $pipeline->process($responseValues);
    }

}
