<?php

namespace Filament\Support\Facades;

use Closure;
use Filament\Support\Colors\ColorManager;
use Filament\Support\View\Components\Contracts\HasColor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, array<int | string, string | int>> getColors()
 * @method static ?array<int | string, string | int> getColor(string $color)
 * @method static array<string> getComponentClasses(class-string<HasColor> | HasColor $component, ?string $color)
 *
 * @see ColorManager
 */
class FilamentColor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ColorManager::class;
    }

    /**
     * @param  array<string, array<int | string, string | int> | string> | Closure  $colors
     */
    public static function register(array | Closure $colors): void
    {
        static::resolved(function (ColorManager $colorManager) use ($colors): void {
            $colorManager->register($colors);
        });
    }
}
