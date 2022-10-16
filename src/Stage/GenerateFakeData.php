<?php

namespace App\Stage;

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

        $body = json_decode($payload->body, true);
        if ($payload->isJson() && $body !== null) {
            array_walk_recursive($body, function (&$value) {
                if (is_numeric($value)) {
                    $value = intval($value);
                }
            });
            $payload->body = json_encode($body);
        }

        return $payload;
    }
}
