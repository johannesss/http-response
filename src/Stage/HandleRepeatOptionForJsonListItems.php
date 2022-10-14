<?php

namespace App\Stage;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use League\Pipeline\StageInterface;

class HandleRepeatOptionForJsonListItems implements StageInterface
{
    public const KEY_REPEAT = '__repeat';

    protected $body;

    protected $structures = [];

    public function __invoke($payload)
    {
        $this->payload = $payload;

        if (!str_contains($payload->body, self::KEY_REPEAT) || !$payload->isJson()) {
            return $payload;
        }

        $arrBody = json_decode($payload->body, true);

        if (is_null($arrBody)) {
            return $payload;
        }

        $body = $this->repeatItems($arrBody);

        $this->unsetKeys($body, [self::KEY_REPEAT]);

        $payload->body = json_encode($body);

        return $payload;
    }

    protected function repeatItems(array $body)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($body),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $key => $value) {
            if (is_array($value) && array_key_exists(self::KEY_REPEAT, $value)) {

                $item         = $value;
                $currentDepth = $iterator->getDepth() - 1;

                for ($subDepth = $currentDepth; $subDepth >= 0; $subDepth--) {

                    $subIterator = $iterator->getSubIterator($subDepth);

                    $subKey = $subIterator->key();

                    $value = $subIterator->offsetGet($subKey);
                    $value = array_merge(
                        $value, array_fill(0, $item[self::KEY_REPEAT] - 1, $item)
                    );

                    $subIterator->offsetSet(
                        $subKey,
                        ($subDepth === $currentDepth ?
                            $value :
                            $iterator->getSubIterator($subDepth + 1)->getArrayCopy()
                        )
                    );
                }
            }
        }

        return $iterator->getArrayCopy();
    }

    protected function unsetKeys(&$array, array $keys)
    {
        foreach ($keys as $key) {
            unset($array[$key]);
        }

        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->unsetKeys($value, $keys);
            }
        }
    }
}
