<?php

namespace Filament\Actions\Testing\Fixtures;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

class TestAction implements Arrayable
{
    /** @var array<string, mixed> | Closure | null */
    protected array | Closure | null $arguments = null;

    /** @var array<string, mixed> */
    protected array $context = [];

    protected ?string $schemaComponent = null;

    protected mixed $table = null;

    protected bool $isBulk = false;

    final public function __construct(
        protected string $name,
    ) {}

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    /**
     * @param  array<string, mixed> | Closure | null  $arguments
     */
    public function arguments(array | Closure | null $arguments): static
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function schemaComponent(?string $key): static
    {
        $this->schemaComponent = $key;

        return $this;
    }

    public function table(mixed $recordKey = true): static
    {
        $this->table = $recordKey;

        return $this;
    }

    public function bulk(bool $condition = true): static
    {
        $this->isBulk = $condition;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $actualArguments
     */
    public function checkArguments(array $actualArguments): bool
    {
        if (! ($this->arguments instanceof Closure)) {
            return true;
        }

        return ($this->arguments)($actualArguments);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            ...((is_array($this->arguments)) ? ['arguments' => $this->arguments] : []),
            'context' => [
                ...($this->isBulk ? ['bulk' => true] : []),
                ...($this->schemaComponent ? ['schemaComponent' => $this->schemaComponent] : []),
                ...$this->context,
            ],
        ];

        if (blank($this->table) || ($this->table === false)) {
            return $array;
        }

        $array['context']['table'] = true;

        if ($this->table === true) {
            return $array;
        }

        $array['context']['recordKey'] = $this->table;

        return $array;
    }
}
