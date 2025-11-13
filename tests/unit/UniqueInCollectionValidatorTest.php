<?php

declare(strict_types=1);

namespace GryfOSS\SymfonyUniqueInCollectionConstraint\Tests\Unit;

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollectionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * Unit tests for the UniqueInCollectionValidator class.
 *
 * Tests all validation scenarios including successful validation,
 * duplicate detection, error handling, and edge cases.
 */
class UniqueInCollectionValidatorTest extends TestCase
{
    private UniqueInCollectionValidator $validator;
    private MockObject $context;

    /**
     * Set up test fixtures before each test method.
     * Creates validator instance and mock context.
     */
    protected function setUp(): void
    {
        $this->validator = new UniqueInCollectionValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    /**
     * Test validation with wrong constraint type.
     * Should throw UnexpectedTypeException.
     */
    public function testValidateWithWrongConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = $this->createMock(Constraint::class);
        $this->validator->validate(['item1', 'item2'], $constraint);
    }

    /**
     * Test validation with null collection.
     * Should pass validation without any violations.
     */
    public function testValidateWithNullCollection(): void
    {
        $constraint = new UniqueInCollection('[email]');

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }

    /**
     * Test validation with invalid collection type.
     * Should throw UnexpectedValueException for non-iterable values.
     */
    public function testValidateWithInvalidCollectionType(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $constraint = new UniqueInCollection('[email]');
        $this->validator->validate('not_an_array', $constraint);
    }

    /**
     * Test validation with null fields in constraint.
     * Should throw Exception indicating fields cannot be null.
     */
    public function testValidateWithNullFields(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Fields cannot be null');

        $constraint = new UniqueInCollection();
        $collection = [['email' => 'test@example.com']];

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with unique values in collection.
     * Should pass validation without any violations.
     */
    public function testValidateWithUniqueValues(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $collection = [
            ['email' => 'user1@example.com'],
            ['email' => 'user2@example.com'],
            ['email' => 'user3@example.com']
        ];

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with duplicate values in collection.
     * Should add violation for duplicate entries.
     */
    public function testValidateWithDuplicateValues(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $collection = [
            ['email' => 'user1@example.com'],
            ['email' => 'user2@example.com'],
            ['email' => 'user1@example.com'] // Duplicate
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with multiple duplicate pairs.
     * Should add violations for each duplicate occurrence.
     */
    public function testValidateWithMultipleDuplicates(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $collection = [
            ['email' => 'user1@example.com'],
            ['email' => 'user2@example.com'],
            ['email' => 'user1@example.com'], // First duplicate
            ['email' => 'user2@example.com'], // Second duplicate
            ['email' => 'user3@example.com']
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->exactly(2))
            ->method('addViolation');

        $this->context->expects($this->exactly(2))
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with multiple fields (composite uniqueness).
     * Should detect duplicates based on combination of all specified fields.
     */
    public function testValidateWithMultipleFields(): void
    {
        $constraint = new UniqueInCollection(['fields' => ['[firstName]', '[lastName]']]);
        $collection = [
            ['firstName' => 'John', 'lastName' => 'Doe'],
            ['firstName' => 'Jane', 'lastName' => 'Doe'],
            ['firstName' => 'John', 'lastName' => 'Smith'],
            ['firstName' => 'John', 'lastName' => 'Doe'] // Duplicate combination
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with property path specified.
     * Should set violation path correctly when propertyPath is provided.
     */
    public function testValidateWithPropertyPath(): void
    {
        $propertyPath = 'contact.email';
        $constraint = new UniqueInCollection('[email]', null, $propertyPath);
        $collection = [
            ['email' => 'user@example.com'],
            ['email' => 'user@example.com'] // Duplicate
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('[1].contact.email')
            ->willReturnSelf();
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with object collection using property accessor.
     * Should work with objects having properties accessible via getters.
     */
    public function testValidateWithObjectCollection(): void
    {
        $constraint = new UniqueInCollection('email');

        // Create test objects
        $user1 = new class {
            public function getEmail(): string {
                return 'user1@example.com';
            }
        };

        $user2 = new class {
            public function getEmail(): string {
                return 'user2@example.com';
            }
        };

        $user3 = new class {
            public function getEmail(): string {
                return 'user1@example.com'; // Duplicate
            }
        };

        $collection = [$user1, $user2, $user3];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with nested property access.
     * Should work with dot notation for nested properties.
     */
    public function testValidateWithNestedPropertyAccess(): void
    {
        $constraint = new UniqueInCollection('[user][email]');
        $collection = [
            ['user' => ['email' => 'user1@example.com']],
            ['user' => ['email' => 'user2@example.com']],
            ['user' => ['email' => 'user1@example.com']] // Duplicate
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with empty collection.
     * Should pass validation without any violations.
     */
    public function testValidateWithEmptyCollection(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $collection = [];

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with collection containing null values.
     * Should handle null values gracefully in uniqueness comparison.
     */
    public function testValidateWithNullValues(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $collection = [
            ['email' => null],
            ['email' => 'user@example.com'],
            ['email' => null] // Duplicate null
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with IteratorAggregate collection.
     * Should work with any iterable collection type.
     */
    public function testValidateWithIteratorAggregate(): void
    {
        $constraint = new UniqueInCollection('[value]');

        // Create IteratorAggregate implementation
        $collection = new class implements \IteratorAggregate {
            public function getIterator(): \Iterator {
                return new \ArrayIterator([
                    ['value' => 'a'],
                    ['value' => 'b'],
                    ['value' => 'a'] // Duplicate
                ]);
            }
        };

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }

    /**
     * Test validation with different data types in values.
     * Should properly handle mixed data types in uniqueness comparison.
     */
    public function testValidateWithMixedDataTypes(): void
    {
        $constraint = new UniqueInCollection('[value]');
        $collection = [
            ['value' => '123'],
            ['value' => 123], // Different type, should be considered different
            ['value' => '123'] // Duplicate string
        ];

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->getMessage())
            ->willReturn($violationBuilder);

        $this->validator->validate($collection, $constraint);
    }
}