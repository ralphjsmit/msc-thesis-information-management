<?php

namespace Filament\Infolists\Components;

use BackedEnum;
use Closure;
use Filament\Infolists\View\Components\IconEntryComponent\IconComponent;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Concerns\CanWrap;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

use function Filament\Support\generate_icon_html;

class IconEntry extends Entry implements HasEmbeddedView
{
    use CanWrap;
    use Concerns\HasColor {
        getColor as getBaseColor;
    }
    use Concerns\HasIcon {
        getIcon as getBaseIcon;
    }

    protected bool | Closure | null $isBoolean = null;

    protected string | Closure | null $falseColor = null;

    protected string | BackedEnum | Closure | null $falseIcon = null;

    protected string | Closure | null $trueColor = null;

    protected string | BackedEnum | Closure | null $trueIcon = null;

    protected IconSize | string | Closure | null $size = null;

    protected bool | Closure $isListWithLineBreaks = false;

    public function boolean(bool | Closure $condition = true): static
    {
        $this->isBoolean = $condition;

        return $this;
    }

    /**
     * @param  string | array<int | string, string | int> | Closure | null  $color
     */
    public function false(string | BackedEnum | Closure | null $icon = null, string | array | Closure | null $color = null): static
    {
        $this->falseIcon($icon);
        $this->falseColor($color);

        return $this;
    }

    public function falseColor(string | Closure | null $color): static
    {
        $this->boolean();
        $this->falseColor = $color;

        return $this;
    }

    public function falseIcon(string | BackedEnum | Closure | null $icon): static
    {
        $this->boolean();
        $this->falseIcon = $icon;

        return $this;
    }

    /**
     * @param  string | array<int | string, string | int> | Closure | null  $color
     */
    public function true(string | BackedEnum | Closure | null $icon = null, string | array | Closure | null $color = null): static
    {
        $this->trueIcon($icon);
        $this->trueColor($color);

        return $this;
    }

    public function trueColor(string | Closure | null $color): static
    {
        $this->boolean();
        $this->trueColor = $color;

        return $this;
    }

    public function trueIcon(string | BackedEnum | Closure | null $icon): static
    {
        $this->boolean();
        $this->trueIcon = $icon;

        return $this;
    }

    public function size(IconSize | string | Closure | null $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(mixed $state): IconSize | string | null
    {
        return $this->evaluate($this->size, [
            'state' => $state,
        ]);
    }

    public function getIcon(mixed $state): string | BackedEnum | null
    {
        if (filled($icon = $this->getBaseIcon($state))) {
            return $icon;
        }

        if (! $this->isBoolean()) {
            return null;
        }

        if ($state === null) {
            return null;
        }

        return $state ? $this->getTrueIcon() : $this->getFalseIcon();
    }

    /**
     * @return string | array<int | string, string | int> | null
     */
    public function getColor(mixed $state): string | array | null
    {
        if (filled($color = $this->getBaseColor($state))) {
            return $color;
        }

        if (! $this->isBoolean()) {
            return null;
        }

        if ($state === null) {
            return null;
        }

        return $state ? $this->getTrueColor() : $this->getFalseColor();
    }

    public function getFalseColor(): string
    {
        return $this->evaluate($this->falseColor) ?? 'danger';
    }

    public function getFalseIcon(): string | BackedEnum
    {
        return $this->evaluate($this->falseIcon)
            ?? FilamentIcon::resolve('infolists::components.icon-entry.false')
            ?? Heroicon::OutlinedXCircle;
    }

    public function getTrueColor(): string
    {
        return $this->evaluate($this->trueColor) ?? 'success';
    }

    public function getTrueIcon(): string | BackedEnum
    {
        return $this->evaluate($this->trueIcon)
            ?? FilamentIcon::resolve('infolists::components.icon-entry.true')
            ?? Heroicon::OutlinedCheckCircle;
    }

    public function listWithLineBreaks(bool | Closure $condition = true): static
    {
        $this->isListWithLineBreaks = $condition;

        return $this;
    }

    public function isListWithLineBreaks(): bool
    {
        return (bool) $this->evaluate($this->isListWithLineBreaks);
    }

    public function isBoolean(): bool
    {
        if (blank($this->isBoolean)) {
            $this->isBoolean = $this->getRecord()?->hasCast($this->getName(), ['bool', 'boolean']);
        }

        return (bool) $this->evaluate($this->isBoolean);
    }

    public function toEmbeddedHtml(): string
    {
        $state = $this->getState();

        if ($state instanceof Collection) {
            $state = $state->all();
        }

        $attributes = $this->getExtraAttributeBag()
            ->class([
                'fi-in-icon',
            ]);

        if (blank($state)) {
            $attributes = $attributes
                ->merge([
                    'x-tooltip' => filled($tooltip = $this->getEmptyTooltip())
                        ? '{
                            content: ' . Js::from($tooltip) . ',
                            theme: $store.theme,
                        }'
                        : null,
                ], escape: false);

            $placeholder = $this->getPlaceholder();

            ob_start(); ?>

            <div <?= $attributes->toHtml() ?>>
                <?php if (filled($placeholder !== null)) { ?>
                    <p class="fi-in-placeholder">
                        <?= e($placeholder) ?>
                    </p>
                <?php } ?>
            </div>

            <?php return $this->wrapEmbeddedHtml(ob_get_clean());
        }

        $state = Arr::wrap($state);

        $alignment = $this->getAlignment();

        $attributes = $attributes
            ->class([
                'fi-in-icon-has-line-breaks' => $this->isListWithLineBreaks(),
                'fi-wrapped' => $this->canWrap(),
                ($alignment instanceof Alignment) ? "fi-align-{$alignment->value}" : (is_string($alignment) ? $alignment : ''),
            ]);

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php foreach ($state as $stateItem) { ?>
                <?php
                $color = $this->getColor($stateItem);
                $size = $this->getSize($stateItem);
                ?>

                <?= generate_icon_html($this->getIcon($stateItem), attributes: (new ComponentAttributeBag)
                    ->merge([
                        'x-tooltip' => filled($tooltip = $this->getTooltip($stateItem))
                            ? '{
                                content: ' . Js::from($tooltip) . ',
                                theme: $store.theme,
                            }'
                            : null,
                    ], escape: false)
                    ->color(IconComponent::class, $color), size: $size ?? IconSize::Large)
                    ->toHtml() ?>
            <?php } ?>
        </div>

        <?php return $this->wrapEmbeddedHtml(ob_get_clean());
    }

    public function canWrapByDefault(): bool
    {
        return true;
    }
}
