<?php

namespace App\Service;

use Exception;
use Faker\Factory;
use App\ResponseValues;
use PhpParser\ParserFactory;
use App\Exception\ResponseBodyTooLarge;

class MethodNotSupportedException extends Exception
{
}

class FakeDataService
{
    protected $generator;

    protected $methodArgSettings = [];

    protected $notSupported = [
        'randomElements',
        'randomKey',
        'shuffle',
        'image',
        'imageUrl',
        'rgbColorAsArray',
        'hslColorAsArray',
        'creditCardDetails',
    ];

    const PATTERN = '/{{\s?([a-zA-Z]+)\((.*?)\)\s?}}/';

    public function __construct(string $locale = null, bool $setSeed = false)
    {
        $this->generator = Factory::create(
            $locale ?? Factory::DEFAULT_LOCALE
        );

        if ($setSeed) {
            $this->generator->seed('seed-value');
        }

        $this->methodArgSettings = [
            'realText'        => [
                0 => fn($value) => $this->limit($value, 500),
            ],
            'realTextBetween' => [
                0 => fn($value) => $this->limit($value, 250),
                1 => fn($value) => $this->limit($value, 500),
            ],
            'words'           => [
                0 => fn($value) => $this->limit($value, 100),
                1 => fn() => true,
            ],
            'sentence'        => [
                0 => fn($value) => $this->limit($value, 100),
            ],
            'sentences'       => [
                0 => fn($value) => $this->limit($value, 25),
                1 => fn() => true,
            ],
            'paragraph'       => [
                0 => fn($value) => $this->limit($value, 25),
            ],
            'paragraphs'      => [
                0 => fn($value) => $this->limit($value, 5),
                1 => fn() => true,
            ],
            'text'            => [
                0 => fn($value) => $this->limit($value, 500),
            ],
            'slug'            => [
                0 => fn($value) => $this->limit($value, 10),
            ],
            'randomHtml'      => [
                0 => fn($value) => $this->limit($value, 6),
                1 => fn($value) => $this->limit($value, 10),
            ],
        ];
    }

    public function generate(ResponseValues $payload)
    {
        $generatedLength = strlen($payload->body);

        return preg_replace_callback(self::PATTERN, function ($matches) use ($payload, &$generatedLength) {
            [$fullMatch, $method, $argsString] = $matches;

            try {
                if (in_array($method, $this->notSupported)) {
                    throw new MethodNotSupportedException();
                }

                $args = $this->parseArgs($argsString);

                $this->applyMethodArgSettings($method, $args);

                $data = call_user_func_array(
                    [$this->generator, $method], $args
                );

                if ($payload->isJson() && is_string($data)) {
                    $data = json_encode($data); // convert line breaks to \r\n
                    $data = substr($data, 1, -1); // remove double "" created by json_encode as its already a string
                    $data = addcslashes($data, '"\\/'); // escape quotes in string
                }

                $generatedLength += (strlen($generatedLength) - strlen($fullMatch)) + strlen($data);

                if ($generatedLength > $payload->responseBodyMaxLength) {
                    throw new ResponseBodyTooLarge;
                }

                return $data;
            } catch (MethodNotSupportedException $e) {
                return $fullMatch;
            }
        }, $payload->body);
    }

    protected function limit(int $value, int $max)
    {
        return $value > $max ? $max : $value;
    }

    protected function applyMethodArgSettings($method, &$args)
    {
        if (array_key_exists($method, $this->methodArgSettings)) {
            foreach ($this->methodArgSettings[$method] as $argIndex => $callback) {
                if (array_key_exists($argIndex, $args)) {
                    $args[$argIndex] = $callback($args[$argIndex]);
                } else {
                    $args[$argIndex] = $callback();
                }
            }
        }
    }

    protected function parseArgs(string $argsString)
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $code = "<?php [$argsString] ?>";

        $ast = $parser->parse($code);

        return array_map(function ($item) {
            return $this->getArgValue($item);
        }, $ast[0]->expr->items);
    }

    protected function getArgValue($arg)
    {
        switch (get_class($arg->value)) {
            case \PhpParser\Node\Expr\Array_::class:
                return array_map(function ($val) {
                    return $this->getArgValue($val);
                }, $arg->value->items);

            case \PhpParser\Node\Expr\UnaryMinus::class:
                return intval("-{$arg->value->expr->value}");

            case \PhpParser\Node\Scalar\LNumber::class:
                return intval($arg->value->value);

            case \PhpParser\Node\Scalar\String_::class:
                return strval($arg->value->value);

            case \PhpParser\Node\Expr\ConstFetch::class:
                return constant($arg->value->name->parts[0]);

            default:
                return null;
        }
    }
}
