<?php

namespace App\Stage;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use League\Pipeline\StageInterface;
use App\Exception\ResponseBodyTooLarge;

class HandleRepeatOptionForJsonListItems implements StageInterface
{
    public const KEY_REPEAT = '__repeat';

    protected const MAX_REPEAT = 50000;

    protected $body;

    /**
     * @return mixed
     */
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

        $body = $this->repeatItems($arrBody, $payload);

        $this->unsetKeys($body, [self::KEY_REPEAT]);

        $body = json_encode($body);

        if (strlen($body) > $payload->responseBodyMaxLength) {
            throw new ResponseBodyTooLarge;
        }

        $payload->body = $body;

        return $payload;
    }

    protected function repeatItems(array $body, $payload)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($body),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $generatedLength = 0;
        $firstIteration  = true;

        foreach ($iterator as $key => $value) {
            if (!is_array($value) || is_array($value) && !array_key_exists(self::KEY_REPEAT, $value)) {
                continue;
            }

            $item         = $value;
            $currentDepth = $iterator->getDepth() - 1;

            for ($subDepth = $currentDepth; $subDepth >= 0; $subDepth--) {

                $subIterator = $iterator->getSubIterator($subDepth);

                $subKey = $subIterator->key();

                $repeat = $this->limit($item[self::KEY_REPEAT], self::MAX_REPEAT);
                $repeat = $repeat < 0 ? 1 : $repeat;

                $value = $subIterator->offsetGet($subKey);

                if ($subDepth === $currentDepth && $subDepth === 0) {
                    $this->unsetKeys($value, [self::KEY_REPEAT]);

                    $reservedCharsLength = 1;

                    if (!$firstIteration) {
                        $reservedCharsLength = 2;
                    }

                    $generatedLength += (strlen(json_encode($value)) * $repeat) - ($repeat - 2) + strlen("\"$subKey\":[]") - $reservedCharsLength;

                    if ($generatedLength > $payload->responseBodyMaxLength) {
                        throw new ResponseBodyTooLarge;
                    }

                    $firstIteration = false;
                }

                unset($value[$key]); // delete the original item we're repeating
                $value = array_merge(
                    $value, array_fill(0, $repeat, $item)
                );

                $this->unsetKeys($value, [self::KEY_REPEAT]);

                $subIterator->offsetSet(
                    $subKey,
                    ($subDepth === $currentDepth ?
                        $value :
                        $iterator->getSubIterator($subDepth + 1)->getArrayCopy()
                    )
                );
            }
        }

        return $iterator->getArrayCopy();
    }

    protected function limit($value, int $max)
    {
        if (!is_numeric($value)) {
            $value = 1;
        }

        return $value > $max ? $max : $value;
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
