<?php

declare(strict_types=1);

namespace App\Modules\Core\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject implements Arrayable, JsonSerializable
{
    public static function fromRequest(Request $request): static
    {
        return static::fromArray($request->validated());
    }

    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return new static();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $snakeName = self::toSnakeCase($name);

            if (array_key_exists($name, $data)) {
                $args[$name] = $data[$name];
            } elseif (array_key_exists($snakeName, $data)) {
                $args[$name] = $data[$snakeName];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[$name] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $args[$name] = null;
            }
        }

        return new static(...$args);
    }

    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->{$name};

            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }

            $data[self::toSnakeCase($name)] = $value;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->toArray(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->toArray(), array_flip($keys));
    }

    private static function toSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
