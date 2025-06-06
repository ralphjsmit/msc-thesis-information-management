<?php

namespace Filament\Forms\Components;

use Closure;
use Filament\Schemas\Components\StateCasts\BooleanStateCast;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Filament\Schemas\Components\StateCasts\EnumArrayStateCast;
use Filament\Schemas\Components\StateCasts\EnumStateCast;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;

class ToggleButtons extends Field implements Contracts\CanDisableOptions
{
    use Concerns\CanDisableOptions;
    use Concerns\CanDisableOptionsWhenSelectedInSiblingRepeaterItems;
    use Concerns\CanFixIndistinctState;
    use Concerns\HasColors;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasGridDirection;
    use Concerns\HasIcons;
    use Concerns\HasNestedRecursiveValidationRules;
    use Concerns\HasOptions;

    public const GROUPED_VIEW = 'filament-forms::components.toggle-buttons.grouped';

    protected bool | Closure $isMultiple = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament-forms::components.toggle-buttons.index';

    protected bool | Closure $isInline = false;

    protected bool | Closure $areButtonLabelsHidden = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(fn (ToggleButtons $component): mixed => $component->isMultiple() ? [] : null);

        $this->afterStateHydrated(static function (ToggleButtons $component, $state): void {
            if (! $component->isMultiple()) {
                return;
            }

            if (is_array($state)) {
                return;
            }

            $component->state([]);
        });
    }

    public function grouped(): static
    {
        return $this->view(static::GROUPED_VIEW);
    }

    public function boolean(?string $trueLabel = null, ?string $falseLabel = null): static
    {
        $this->options([
            1 => $trueLabel ?? __('filament-forms::components.toggle_buttons.boolean.true'),
            0 => $falseLabel ?? __('filament-forms::components.toggle_buttons.boolean.false'),
        ]);

        $this->colors([
            1 => 'success',
            0 => 'danger',
        ]);

        $this->icons([
            1 => FilamentIcon::resolve('forms::components.toggle-buttons.boolean.true') ?? Heroicon::Check,
            0 => FilamentIcon::resolve('forms::components.toggle-buttons.boolean.false') ?? Heroicon::XMark,
        ]);

        $this->stateCast(app(BooleanStateCast::class, ['isStoredAsInt' => true]));

        return $this;
    }

    public function inline(bool | Closure $condition = true): static
    {
        $this->isInline = $condition;

        return $this;
    }

    public function isInline(): bool
    {
        return (bool) $this->evaluate($this->isInline);
    }

    public function hiddenButtonLabels(bool | Closure $condition = true): static
    {
        $this->areButtonLabelsHidden = $condition;

        return $this;
    }

    public function areButtonLabelsHidden(): bool
    {
        return (bool) $this->evaluate($this->areButtonLabelsHidden);
    }

    public function multiple(bool | Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function getDefaultState(): mixed
    {
        $state = parent::getDefaultState();

        if (is_bool($state)) {
            return $state ? 1 : 0;
        }

        return $state;
    }

    public function getEnumDefaultStateCast(): ?StateCast
    {
        $enum = $this->getEnum();

        if (blank($enum)) {
            return null;
        }

        return app(
            $this->isMultiple() ? EnumArrayStateCast::class : EnumStateCast::class,
            ['enum' => $enum],
        );
    }

    /**
     * @return ?array<string>
     */
    public function getInValidationRuleValues(): ?array
    {
        $values = parent::getInValidationRuleValues();

        if ($values !== null) {
            return $values;
        }

        return array_keys($this->getEnabledOptions());
    }

    public function hasInValidationOnMultipleValues(): bool
    {
        return $this->isMultiple();
    }

    public function hasNullableBooleanState(): bool
    {
        return true;
    }
}
