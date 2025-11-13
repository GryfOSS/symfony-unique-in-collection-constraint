<?php

declare(strict_types=1);

namespace GryfOSS\SymfonyUniqueInCollectionConstraint;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validator implementation for the UniqueInCollection constraint.
 *
 * This validator performs the actual validation logic by:
 * 1. Iterating through all items in the collection
 * 2. Extracting specified field values from each item
 * 3. Checking for duplicate combinations of field values
 * 4. Adding violations when duplicates are found
 *
 * The validator uses Symfony's PropertyAccessor component to safely access
 * object properties and array keys, supporting nested properties with dot notation.
 */

class UniqueInCollectionValidator extends ConstraintValidator
{
    /**
     * Property accessor for safe retrieval of object properties and array values.
     * Supports nested property access using dot notation (e.g., 'user.email').
     */
    private PropertyAccessor $propertyAccessor;

    /**
     * Constructor initializes the property accessor component.
     * The PropertyAccessor is used to safely extract field values from collection items.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Validates that specified fields have unique values within the collection.
     *
     * The validation process:
     * 1. Validates input parameters (constraint type, collection type)
     * 2. Extracts field values from each collection item
     * 3. Compares field combinations to detect duplicates
     * 4. Adds validation violations for duplicate entries
     *
     * @param mixed $collection The collection to validate (array or IteratorAggregate)
     * @param Constraint $constraint The UniqueInCollection constraint with field specifications
     *
     * @throws UnexpectedTypeException If constraint is not UniqueInCollection
     * @throws UnexpectedValueException If collection is not array or IteratorAggregate
     * @throws \Exception If no fields are specified for uniqueness checking
     */
    public function validate(mixed $collection, Constraint $constraint)
    {
        // Ensure we're working with the correct constraint type
        if (!$constraint instanceof UniqueInCollection) {
            throw new UnexpectedTypeException($constraint, UniqueInCollection::class);
        }

        // Allow null collections (no validation needed)
        if (null === $collection) {
            return;
        }

        // Validate that collection is iterable
        if (!\is_array($collection) && !$collection instanceof \IteratorAggregate) {
            throw new UnexpectedValueException($collection, 'array|IteratorAggregate');
        }

        // Ensure fields are specified for uniqueness checking
        if (null === $constraint->getFields()) {
            // TODO: Implement automatic field detection based on object properties
            throw new \Exception('Fields cannot be null');
        }

        // Normalize fields to array format for consistent processing
        $fields = \is_array($constraint->getFields()) ? $constraint->getFields() : [$constraint->getFields()];

        // Track unique combinations of field values to detect duplicates
        $checksums = [];

        // Iterate through each item in the collection
        foreach ($collection as $key => $element) {
            // Extract values for all specified fields from current element
            $propertyValue = [];
            foreach ($fields as $field) {
                $propertyValue[] = $this->propertyAccessor->getValue($element, $field);
            }

            $checksum = md5(serialize($propertyValue));

            // Check if this combination of field values already exists
            if (\in_array($checksum, $checksums, true)) {
                // Build validation violation for duplicate entry
                $violationBuilder = $this->context->buildViolation($constraint->getMessage());

                // Set specific path for the violation if propertyPath is specified
                if ($constraint->getPropertyPath() !== null) {
                    $violationBuilder->atPath(\sprintf('[%s].%s', $key, $constraint->getPropertyPath()));
                }

                $violationBuilder->addViolation();
            }

            // Add current combination to tracking array for future comparisons
            $checksums[] = $checksum;
        }
    }
}
