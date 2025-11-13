<?php

declare(strict_types=1);

namespace GryfOSS\SymfonyUniqueInCollectionConstraint\Tests\Unit;

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollectionValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the UniqueInCollection constraint class.
 *
 * Tests the constraint configuration, field handling, and getter methods
 * to ensure proper behavior in different initialization scenarios.
 */
class UniqueInCollectionTest extends TestCase
{
    /**
     * Test constraint initialization with no options.
     * Should result in null fields and default message.
     */
    public function testConstructorWithNoOptions(): void
    {
        $constraint = new UniqueInCollection();

        $this->assertNull($constraint->getFields());
        $this->assertEquals('Must be unique within collection.', $constraint->getMessage());
        $this->assertNull($constraint->getPropertyPath());
    }

    /**
     * Test constraint initialization with a single field as string.
     * Should convert the string to an array with one element.
     */
    public function testConstructorWithSingleFieldAsString(): void
    {
        $constraint = new UniqueInCollection('email');

        $this->assertEquals(['email'], $constraint->getFields());
        $this->assertEquals('Must be unique within collection.', $constraint->getMessage());
        $this->assertNull($constraint->getPropertyPath());
    }

    /**
     * Test constraint initialization with multiple fields in options array.
     * Should properly set fields from the 'fields' key in options.
     */
    public function testConstructorWithFieldsArray(): void
    {
        $constraint = new UniqueInCollection(['fields' => ['email', 'username']]);

        $this->assertEquals(['email', 'username'], $constraint->getFields());
        $this->assertEquals('Must be unique within collection.', $constraint->getMessage());
        $this->assertNull($constraint->getPropertyPath());
    }

    /**
     * Test constraint initialization with custom message in options.
     * Should use custom message while maintaining other defaults.
     */
    public function testConstructorWithCustomMessage(): void
    {
        $constraint = new UniqueInCollection([
            'fields' => ['email'],
            'message' => 'Email must be unique'
        ]);

        $this->assertEquals(['email'], $constraint->getFields());
        $this->assertEquals('Email must be unique', $constraint->getMessage());
        $this->assertNull($constraint->getPropertyPath());
    }

    /**
     * Test constraint initialization with property path parameter.
     * Should properly set the property path for violation reporting.
     */
    public function testConstructorWithPropertyPath(): void
    {
        $propertyPath = 'user.email';
        $constraint = new UniqueInCollection('email', null, $propertyPath);

        $this->assertEquals(['email'], $constraint->getFields());
        $this->assertEquals($propertyPath, $constraint->getPropertyPath());
    }

    /**
     * Test constraint initialization with validation groups.
     * Should properly handle validation groups parameter.
     */
    public function testConstructorWithValidationGroups(): void
    {
        $groups = ['registration', 'profile'];
        $constraint = new UniqueInCollection('email', $groups);

        $this->assertEquals(['email'], $constraint->getFields());
        $this->assertEquals($groups, $constraint->groups);
    }

    /**
     * Test that the constraint returns the correct validator class.
     * Essential for Symfony's constraint system to work properly.
     */
    public function testValidatedBy(): void
    {
        $constraint = new UniqueInCollection();

        $this->assertEquals(UniqueInCollectionValidator::class, $constraint->validatedBy());
    }

    /**
     * Test constraint initialization with complex options array.
     * Should properly handle all options including message and fields.
     */
    public function testConstructorWithComplexOptions(): void
    {
        $options = [
            'fields' => ['firstName', 'lastName', 'email'],
            'message' => 'This combination must be unique'
        ];
        $groups = ['user_validation'];
        $propertyPath = 'contact.email';

        $constraint = new UniqueInCollection($options, $groups, $propertyPath);

        $this->assertEquals(['firstName', 'lastName', 'email'], $constraint->getFields());
        $this->assertEquals('This combination must be unique', $constraint->getMessage());
        $this->assertEquals($propertyPath, $constraint->getPropertyPath());
        $this->assertEquals($groups, $constraint->groups);
    }

    /**
     * Test that options array without 'fields' key doesn't override fields.
     * Should result in null fields when no 'fields' key is present.
     */
    public function testConstructorWithOptionsArrayButNoFields(): void
    {
        $constraint = new UniqueInCollection(['message' => 'Custom message']);

        $this->assertNull($constraint->getFields());
        $this->assertEquals('Custom message', $constraint->getMessage());
    }

    /**
     * Test constraint with single field in fields array.
     * Should properly handle arrays with single elements.
     */
    public function testConstructorWithSingleFieldInArray(): void
    {
        $constraint = new UniqueInCollection(['fields' => ['email']]);

        $this->assertEquals(['email'], $constraint->getFields());
    }

    /**
     * Test that empty fields array is properly handled.
     * Should maintain empty array rather than converting to null.
     */
    public function testConstructorWithEmptyFieldsArray(): void
    {
        $constraint = new UniqueInCollection(['fields' => []]);

        $this->assertEquals([], $constraint->getFields());
    }
}