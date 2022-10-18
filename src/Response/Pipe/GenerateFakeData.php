<?php

namespace App\Response\Pipe;

use App\Service\FakeDataService;
use League\Pipeline\StageInterface;

class GenerateFakeData implements StageInterface
{
    /**
     * @return mixed
     */
    public function __invoke($payload)
    {
        if (!str_contains($payload->body, '{{')) {
            return $payload;
        }

        $fakeDataService = new FakeDataService(
            $payload->fakeDataLocale,
            $payload->fakeDataPersist
        );

        $payload->body = $fakeDataService->generate($payload);

        if ($payload->isJson()) {
            $payload->body = $this->convertNumericStringsToIntegers($payload->body);
        }

        return $payload;
    }

    protected function convertNumericStringsToIntegers(string $body)
    {
        $body = json_decode($body, true);

        array_walk_recursive($body, function (&$value) {
            if (is_numeric($value)) {
                $value = intval($value);
            }
        });

        return json_encode($body);
    }
}
