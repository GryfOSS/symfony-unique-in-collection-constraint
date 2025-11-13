<?php

declare(strict_types=1);

namespace GryfOSS\SymfonyUniqueInCollectionConstraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * Symfony validation constraint that ensures uniqueness of specific fields within a collection.
 *
 * This constraint validates that the specified field(s) have unique values across all items
 * in a collection. It's useful for ensuring no duplicate entries based on certain properties.
 *
 * Example usage:
 * - Ensuring unique email addresses in a collection of users
 * - Preventing duplicate product codes in a collection of items
 * - Validating unique combinations of fields (composite uniqueness)
 */

#[Attribute()]
class UniqueInCollection extends Constraint
{
    /**
     * The field(s) to check for uniqueness within the collection.
     * Can be a single field name (string) or an array of field names for composite uniqueness.
     */
    protected ?array $fields = null;

    /**
     * The error message to display when uniqueness validation fails.
     */
    protected string $message = 'Must be unique within collection.';

    /**
     * Optional property path to specify where the constraint should be applied.
     * Useful when validating nested properties or specific collection paths.
     */
    protected ?string $propertyPath = null;

    /**
     * Constructor for the UniqueInCollection constraint.
     *
     * @param mixed $options The constraint options. Can be:
     *                      - string: single field name to check for uniqueness
     *                      - array: multiple options including 'fields' key for field names
     *                      - null: no specific options
     * @param array|null $groups Validation groups this constraint belongs to
     * @param string|null $propertyPath Specific property path for constraint application
     */
    public function __construct(mixed $options = null, ?array $groups = null, ?string $propertyPath = null)
    {
        parent::__construct($options, $groups);

        $this->propertyPath = $propertyPath;

        // Handle different option formats for field specification
        if (is_array($options) && isset($options['fields'])) {
            // Options array with 'fields' key
            $this->fields = $options['fields'];
        } elseif (is_string($options)) {
            // Single field name as string - convert to array
            $this->fields = [ $options ];
        }
    }

    /**
     * Gets the field names that should be checked for uniqueness.
     *
     * @return array|null Array of field names, or null if no fields specified
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * Gets the error message displayed when validation fails.
     *
     * @return string The validation error message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Gets the property path where this constraint should be applied.
     *
     * @return string|null The property path, or null for default behavior
     */
    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /**
     * Returns the validator class responsible for performing the actual validation logic.
     *
     * @return string The fully qualified class name of the validator
     */
    public function validatedBy(): string
    {
        return UniqueInCollectionValidator::class;
    }
}
