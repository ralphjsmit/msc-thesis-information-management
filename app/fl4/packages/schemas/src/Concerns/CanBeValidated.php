<?php

namespace Filament\Schemas\Concerns;

use Filament\Forms\Components;
use Filament\Schemas\Components\Component;

trait CanBeValidated
{
    /**
     * @return array<string, string>
     */
    public function getValidationAttributes(): array
    {
        $attributes = [];

        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydratedWhenHidden()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationAttributes($attributes);
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $attributes = [
                    ...$attributes,
                    ...$childSchema->getValidationAttributes(),
                ];
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    public function getValidationMessages(): array
    {
        $messages = [];

        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component->isHiddenAndNotDehydratedWhenHidden()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationMessages($messages);
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $messages = [
                    ...$messages,
                    ...$childSchema->getValidationMessages(),
                ];
            }
        }

        return $messages;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->getComponents(withActions: false, withHidden: true) as $component) {
            if ($component->isNeitherDehydratedNorValidated()) {
                continue;
            }

            if ($component instanceof Components\Contracts\HasValidationRules) {
                $component->dehydrateValidationRules($rules);
            }

            foreach ($component->getChildSchemas() as $childSchema) {
                if ($childSchema->isHidden()) {
                    continue;
                }

                $rules = [
                    ...$rules,
                    ...$childSchema->getValidationRules(),
                ];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function validate(): array
    {
        if (! count(array_filter(
            $this->getComponents(withActions: false, withHidden: true),
            fn (Component $component): bool => ! $component->isHiddenAndNotDehydratedWhenHidden(),
        ))) {
            return [];
        }

        $rules = $this->getValidationRules();

        if (! count($rules)) {
            return [];
        }

        return $this->getLivewire()->validate($rules, $this->getValidationMessages(), $this->getValidationAttributes());
    }
}
