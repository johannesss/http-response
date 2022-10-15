<?php

namespace App;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResponseValues
{
    public const KEY_STATUS_CODE          = 'status_code';
    public const KEY_HEADERS              = 'headers';
    public const KEY_BODY                 = 'body';
    public const KEY_FAKE_DATA_LOCALE     = 'fake_data_locale';
    public const KEY_FAKE_DATA_PERSIST    = 'fake_data_persist';
    public const RESPONSE_BODY_MAX_LENGTH = 10000;

    public $statusCode = Response::HTTP_OK;

    public $headers = [];

    public $body = '';

    public $fakeDataLocale = null;

    public $fakeDataPersist = false;

    public function __construct(array $values)
    {
        if (array_key_exists(self::KEY_STATUS_CODE, $values)) {
            $this->statusCode = $values[self::KEY_STATUS_CODE];
        }

        if (
            array_key_exists(self::KEY_HEADERS, $values) &&
            is_array($values[self::KEY_HEADERS])
        ) {
            $this->headers = array_change_key_case($values[self::KEY_HEADERS], CASE_UPPER);
        }

        if (array_key_exists(self::KEY_BODY, $values)) {
            $body = $values[self::KEY_BODY];

            if (is_array($values[self::KEY_BODY])) {
                $body = json_encode($values[self::KEY_BODY]);
            }

            $this->body = $body;

            $this->bodyLength = strlen($body);
        }

        if (array_key_exists(self::KEY_FAKE_DATA_LOCALE, $values)) {
            $this->fakeDataLocale = $values[self::KEY_FAKE_DATA_LOCALE];
        }

        if (
            array_key_exists(self::KEY_FAKE_DATA_PERSIST, $values) &&
            ($values[self::KEY_FAKE_DATA_PERSIST] === 'true' ||
                $values[self::KEY_FAKE_DATA_PERSIST] === true)
        ) {
            $this->fakeDataPersist = true;
        }
    }

    public function isJson()
    {
        if (array_key_exists('CONTENT-TYPE', $this->headers)) {
            return Str::contains($this->headers['CONTENT-TYPE'] ?? '', ['/json', '+json']);
        }

        return false;
    }
}
