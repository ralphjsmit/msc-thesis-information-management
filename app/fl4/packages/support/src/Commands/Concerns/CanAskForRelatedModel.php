<?php

namespace Filament\Support\Commands\Concerns;

use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\search;

trait CanAskForRelatedModel
{
    /**
     * @return ?class-string
     */
    protected function askForRelatedModel(string $relationship): ?string
    {
        $modelFqns = array_filter(
            get_declared_classes(),
            fn (string $class): bool => is_subclass_of($class, Model::class),
        );

        $modelFqns = array_combine(
            $modelFqns,
            $modelFqns,
        );

        return search(
            label: "Filament couldn't automatically find the related model for the [{$relationship}] relationship. What is the fully qualified class name of the related model?",
            options: function (?string $search) use ($modelFqns): array {
                if (blank($search)) {
                    return $modelFqns;
                }

                $search = str($search)->trim()->replace(['\\', '/'], '');

                return array_filter($modelFqns, fn (string $modelFqn): bool => str($modelFqn)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
            },
            placeholder: 'App\\Models\\User',
        );
    }
}
