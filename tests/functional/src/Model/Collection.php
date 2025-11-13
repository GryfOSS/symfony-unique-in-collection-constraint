<?php

namespace App\Model;

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class Collection
{
    #[NotBlank()]
    public ?string $name = null;

    /**
     * @var Single[]
     */
    #[UniqueInCollection(options: ['fields' => ['group']], propertyPath: 'singles')]
    public array $singles = [];
}