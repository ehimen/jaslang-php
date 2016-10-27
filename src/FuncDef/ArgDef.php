<?php

namespace Ehimen\Jaslang\FuncDef;

use Ehimen\Jaslang\Exception\InvalidArgumentException;
use Ehimen\Jaslang\Value\Boolean;
use Ehimen\Jaslang\Value\Num;
use Ehimen\Jaslang\Value\Str;
use Ehimen\Jaslang\Value\StringLike;
use Ehimen\Jaslang\Value\Value;

class ArgDef
{
    const STRING = 'string';
    const NUMBER = 'number';
    const BOOLEAN = 'bool';
    const ANY    = '*';         // Note this does not permit no value.
    const STRING_LIKE = 'string-like';
    
    const TYPES_MAP = [
        self::STRING => Str::class,
        self::NUMBER => Num::class,
        self::STRING_LIKE => StringLike::class,
        self::BOOLEAN => Boolean::class,
    ];
    
    /**
     * @var bool
     */
    private $optional;

    /**
     * @var string
     */
    private $type;

    public function __construct($type, $optional)
    {
        if (empty(static::TYPES_MAP[$type]) && (static::ANY !== $type)) {
            throw new InvalidArgumentException(sprintf('Unknown type %s', $type));
        }
        
        $this->type     = $type;
        $this->optional = $optional;
    }
    
    public function getType()
    {
        return $this->type;
    }

    public function isOptional()
    {
        return $this->optional;
    }

    public function isSatisfiedBy(Value $value = null)
    {
        if (null === $value && $this->isOptional()) {
            return true;
        }
        
        return static::isOfType($this->type, $value);
    }

    public static function isOfType($type, Value $value = null)
    {
        if (null === $value) {
            return false;
        }
        
        if ($type === ArgDef::ANY) {
            return true;
        }

        $types = ArgDef::TYPES_MAP;

        if (!isset($types[$type])) {
            throw new InvalidArgumentException('Not recognised type: ' . $type);
        }

        return is_a($value, $types[$type]);
    }
}
