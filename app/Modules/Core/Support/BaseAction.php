<?php

declare(strict_types=1);

namespace App\Modules\Core\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

abstract class BaseAction
{
    protected bool $useTransaction = true;

    public function execute(mixed ...$args): mixed
    {
        if ($this->useTransaction) {
            return DB::transaction(fn () => $this->handle(...$args));
        }

        return $this->handle(...$args);
    }

    abstract protected function handle(mixed ...$args): mixed;

    protected function withoutTransaction(): static
    {
        $clone = clone $this;
        $clone->useTransaction = false;
        return $clone;
    }

    protected function fail(string $message, ?Throwable $previous = null): never
    {
        throw new \RuntimeException($message, 0, $previous);
    }
}
