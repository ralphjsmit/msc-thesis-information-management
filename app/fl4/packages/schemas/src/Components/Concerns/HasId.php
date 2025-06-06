<?php

namespace Filament\Schemas\Components\Concerns;

use Closure;

trait HasId
{
    protected string | Closure | null $id = null;

    public function id(string | Closure | null $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->getCustomId() ?? $this->getKey();
    }

    public function getCustomId(): ?string
    {
        return $this->evaluate($this->id);
    }
}
