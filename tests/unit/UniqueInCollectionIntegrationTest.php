<?php

declare(strict_types=1);

namespace GryfOSS\SymfonyUniqueInCollectionConstraint\Tests\Unit;

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Integration tests demonstrating real-world usage of the UniqueInCollection constraint.
 *
 * These tests show how the constraint works with Symfony's Validator component
 * in practical scenarios, validating collections of data.
 */
class UniqueInCollectionIntegrationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * Test validation of a user collection with unique email addresses.
     */
    public function testValidateUserCollectionWithUniqueEmails(): void
    {
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com']
        ];

        $constraint = new UniqueInCollection('[email]');
        $violations = $this->validator->validate($users, $constraint);

        $this->assertCount(0, $violations);
    }

    /**
     * Test validation of a user collection with duplicate email addresses.
     */
    public function testValidateUserCollectionWithDuplicateEmails(): void
    {
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Bob Wilson', 'email' => 'john@example.com'] // Duplicate email
        ];

        $constraint = new UniqueInCollection('[email]');
        $violations = $this->validator->validate($users, $constraint);

        $this->assertCount(1, $violations);
        $this->assertEquals('Must be unique within collection.', $violations[0]->getMessage());
    }

    /**
     * Test validation with composite uniqueness (multiple fields).
     */
    public function testValidateWithCompositeUniqueness(): void
    {
        $products = [
            ['category' => 'electronics', 'code' => 'TV001'],
            ['category' => 'electronics', 'code' => 'TV002'],
            ['category' => 'furniture', 'code' => 'TV001'], // Same code, different category - OK
            ['category' => 'electronics', 'code' => 'TV001'] // Duplicate combination
        ];

        $constraint = new UniqueInCollection(['fields' => ['[category]', '[code]']]);
        $violations = $this->validator->validate($products, $constraint);

        $this->assertCount(1, $violations);
    }

    /**
     * Test validation with custom error message.
     */
    public function testValidateWithCustomMessage(): void
    {
        $users = [
            ['username' => 'john123'],
            ['username' => 'jane456'],
            ['username' => 'john123'] // Duplicate
        ];

        $constraint = new UniqueInCollection([
            'fields' => ['[username]'],
            'message' => 'Username must be unique in the collection.'
        ]);
        $violations = $this->validator->validate($users, $constraint);

        $this->assertCount(1, $violations);
        $this->assertEquals('Username must be unique in the collection.', $violations[0]->getMessage());
    }

    /**
     * Test validation with objects instead of arrays.
     */
    public function testValidateObjectCollection(): void
    {
        $user1 = new class {
            public function getEmail(): string { return 'user1@example.com'; }
        };

        $user2 = new class {
            public function getEmail(): string { return 'user2@example.com'; }
        };

        $user3 = new class {
            public function getEmail(): string { return 'user1@example.com'; } // Duplicate
        };

        $users = [$user1, $user2, $user3];
        $constraint = new UniqueInCollection('email');
        $violations = $this->validator->validate($users, $constraint);

        $this->assertCount(1, $violations);
    }

    /**
     * Test validation with nested properties.
     */
    public function testValidateNestedProperties(): void
    {
        $orders = [
            ['customer' => ['email' => 'john@example.com'], 'total' => 100.00],
            ['customer' => ['email' => 'jane@example.com'], 'total' => 150.00],
            ['customer' => ['email' => 'john@example.com'], 'total' => 75.00] // Duplicate customer email
        ];

        $constraint = new UniqueInCollection('[customer][email]');
        $violations = $this->validator->validate($orders, $constraint);

        $this->assertCount(1, $violations);
    }

    /**
     * Test validation with empty collection (should pass).
     */
    public function testValidateEmptyCollection(): void
    {
        $constraint = new UniqueInCollection('[email]');
        $violations = $this->validator->validate([], $constraint);

        $this->assertCount(0, $violations);
    }

    /**
     * Test that null values are handled correctly.
     */
    public function testValidateWithNullValues(): void
    {
        $users = [
            ['email' => 'john@example.com'],
            ['email' => null],
            ['email' => 'jane@example.com'],
            ['email' => null] // Duplicate null
        ];

        $constraint = new UniqueInCollection('[email]');
        $violations = $this->validator->validate($users, $constraint);

        $this->assertCount(1, $violations);
    }
}