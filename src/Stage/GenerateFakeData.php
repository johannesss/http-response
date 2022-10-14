<?php

namespace App\Stage;

use Psr\Log\LoggerInterface;
use App\Service\FakeDataService;
use League\Pipeline\StageInterface;

class GenerateFakeData implements StageInterface
{
    public const PREFIX = '__faker';

    protected $faker;

    protected $logger;

    protected $blacklistedFakerMethods = [
        'image',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke($payload)
    {
        if (!str_contains($payload->body, '{{')) {
            return $payload;
        }

        $fakeDataService = new FakeDataService(
            $payload->fakeDataLocale,
            $payload->fakeDataPersist,
            $this->logger
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
