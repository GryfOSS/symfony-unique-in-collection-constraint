<?php

namespace App\Model;

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class TwoFieldsContraintsCollection
{
    #[NotBlank()]
    public ?string $name = null;

    /**
     * @var Single[]
     */
    #[UniqueInCollection(options: ['fields' => ['group', 'name']], propertyPath: 'singles')]
    public array $singles = [];
}