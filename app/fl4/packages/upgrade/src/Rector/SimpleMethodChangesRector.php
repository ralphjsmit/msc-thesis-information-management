<?php

namespace Filament\Upgrade\Rector;

use Closure;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Contracts\HasColor;
use Illuminate\Validation\Rules\Enum;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\UnionType;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;

class SimpleMethodChangesRector extends AbstractRector
{
    /**
     * @return array<array{
     *     class: class-string | array<class-string>,
     *     classIdentifier: string,
     *     changes: array<string, Closure>,
     * }>
     */
    public function getChanges(): array
    {
        return [
            [
                'class' => [
                    Page::class,
                ],
                'changes' => [
                    'getFooterWidgetsColumns' => function (ClassMethod $node): void {
                        $node->returnType = new UnionType([new Identifier('int'), new Identifier('array')]);
                    },
                    'getHeaderWidgetsColumns' => function (ClassMethod $node): void {
                        $node->returnType = new UnionType([new Identifier('int'), new Identifier('array')]);
                    },
                    'getSubNavigationPosition' => function (ClassMethod $node): void {
                        $node->flags &= Modifiers::STATIC;
                    },
                ],
            ],
            [
                'class' => [
                    CreateRecord::class,
                ],
                'changes' => [
                    'canCreateAnother' => function (ClassMethod $node): void {
                        $node->flags &= ~Modifiers::STATIC;
                    },
                ],
            ],
            [
                'class' => [
                    ViewRecord::class,
                ],
                'changes' => [
                    'infolist' => function (ClassMethod $node): void {
                        $param = new Param(new Variable('schema'));
                        $param->type = new Name('\\Filament\\Schemas\\Schema');

                        $node->params = [$param];
                    },
                ],
            ],
            [
                'class' => [
                    Dashboard::class,
                ],
                'changes' => [
                    'getColumns' => function (ClassMethod $node): void {
                        $node->returnType = new UnionType([new Identifier('int'), new Identifier('array')]);
                    },
                ],
            ],
            [
                'class' => [
                    HasColor::class,
                ],
                'changes' => [
                    'getColor' => function (ClassMethod $node): void {
                        $node->returnType = new Identifier('?string');
                    },
                ],
            ],
        ];
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, Enum_::class];
    }

    /**
     * @param  Class_ | Enum_  $node
     */
    public function refactor(Node $node): ?Node
    {
        $touched = false;

        foreach ($this->getChanges() as $change) {
            if (! $this->isClassMatchingChange($node, $change)) {
                continue;
            }

            foreach ($change['changes'] as $methodName => $modifier) {
                foreach ($node->getMethods() as $method) {
                    if (! $this->isName($method, $methodName)) {
                        continue;
                    }

                    $modifier($method);

                    $touched = true;
                }
            }
        }

        return $touched ? $node : null;
    }

    /**
     * @param array{
     *     class: class-string | array<class-string>,
     *     classIdentifier: string,
     * } $change
     */
    public function isClassMatchingChange(Class_ | Enum_ $class, array $change): bool
    {
        if (! array_key_exists('class', $change)) {
            return true;
        }

        $classes = is_array($change['class']) ?
            $change['class'] :
            [$change['class']];

        $classes = array_map(fn (string $class): string => ltrim($class, '\\'), $classes);

        foreach ($classes as $classToCheck) {
            if ($class instanceof Enum_) {
                foreach ($class->implements as $enumInterface) {
                    if ($enumInterface->toString() === $classToCheck) {
                        return true;
                    }
                }

                continue;
            }

            if ($this->isObjectType($class, new ObjectType($classToCheck))) {
                return true;
            }
        }

        return false;
    }
}
