# Symfony UniqueInCollection Constraint

[![PHP Version](https://img.shields.io/packagist/php-v/gryfoss/symfony-unique-in-collection-constraint)](https://packagist.org/packages/gryfoss/symfony-unique-in-collection-constraint)
[![Tests](https://github.com/GryfOSS/symfony-unique-in-collection-constraint/workflows/Tests/badge.svg)](https://github.com/GryfOSS/symfony-unique-in-collection-constraint/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/GryfOSS/symfony-unique-in-collection-constraint)](https://codecov.io/gh/GryfOSS/symfony-unique-in-collection-constraint)
[![Latest Stable Version](https://img.shields.io/packagist/v/gryfoss/symfony-unique-in-collection-constraint)](https://packagist.org/packages/gryfoss/symfony-unique-in-collection-constraint)
[![License](https://img.shields.io/packagist/l/gryfoss/symfony-unique-in-collection-constraint)](https://github.com/GryfOSS/symfony-unique-in-collection-constraint/blob/main/LICENSE)

A Symfony validation constraint that ensures uniqueness of specific fields within a collection. This constraint validates that the specified field(s) have unique values across all items in a collection, making it perfect for preventing duplicate entries based on certain properties.

## 🚀 Features

- **Field Uniqueness**: Validate uniqueness of single or multiple fields within collections
- **Composite Uniqueness**: Support for checking uniqueness across multiple field combinations
- **Symfony Integration**: Native Symfony Validator component integration
- **PHP 8+ Attributes**: Modern attribute-based constraint definition
- **100% Test Coverage**: Comprehensive unit and functional test suite

## 📋 Requirements

- PHP 8.2 or higher
- Symfony Validator Component 7.3+
- Symfony PropertyAccess Component 7.3+

## 📦 Installation

Install the constraint via Composer:

```bash
composer require gryfoss/symfony-unique-in-collection-constraint
```

## 🛠️ Usage

### Basic Usage with PHP Attributes

You can use the constraint directly on collection properties in your entities:

```php
<?php

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;

class Team
{
    #[UniqueInCollection('email')]
    private array $members = [];

    // Constructor, getters, setters...
}
```

### Advanced Usage Examples

#### Single Field Uniqueness

```php
<?php

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;

class Company
{
    #[UniqueInCollection('email')]
    private array $employees = [];
}

// Usage
$employees = [
    ['name' => 'John Doe', 'email' => 'john@company.com'],
    ['name' => 'Jane Smith', 'email' => 'jane@company.com'],
    ['name' => 'Bob Wilson', 'email' => 'john@company.com'], // ❌ Duplicate email!
];
```

#### Multiple Field Uniqueness (Composite)

```php
<?php

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;

class ProductCatalog
{
    #[UniqueInCollection(['category', 'sku'])]
    private array $products = [];
}

// Usage
$products = [
    ['name' => 'Laptop', 'category' => 'electronics', 'sku' => 'LAP001'],
    ['name' => 'Phone', 'category' => 'electronics', 'sku' => 'PHN001'],
    ['name' => 'Tablet', 'category' => 'electronics', 'sku' => 'LAP001'], // ❌ Same category+sku!
];
```

#### Manual Validation

```php
<?php

use GryfOSS\SymfonyUniqueInCollectionConstraint\UniqueInCollection;
use Symfony\Component\Validator\Validation;

$validator = Validation::createValidator();

$data = [
    ['id' => 1, 'code' => 'ABC123'],
    ['id' => 2, 'code' => 'DEF456'],
    ['id' => 3, 'code' => 'ABC123'], // Duplicate code
];

$constraint = new UniqueInCollection('code');
$violations = $validator->validate($data, $constraint);

if (count($violations) > 0) {
    foreach ($violations as $violation) {
        echo $violation->getMessage(); // "Must be unique within collection."
    }
}
```

## 🧪 Running Tests

This project includes both unit tests and functional tests to ensure reliability:

### Run All Tests

```bash
# Run the complete test suite (unit + functional)
./scripts/run-all-tests.sh
```

### Run Unit Tests Only

```bash
# Basic unit tests
./vendor/bin/phpunit

# Unit tests with coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### Run Unit Tests with Coverage Check

```bash
# Ensures 100% test coverage
./scripts/check-coverage.sh
```

### Run Functional Tests (Behat)

```bash
cd tests/functional
composer install
./vendor/bin/behat
```

### Test Structure

- **Unit Tests** (`tests/unit/`): Test individual components and constraint logic
- **Functional Tests** (`tests/functional/`): End-to-end Behat scenarios testing real-world usage
- **Coverage**: Maintained at 100% to ensure reliability

## 🤝 Contributing

We welcome contributions to improve this constraint! Here's how you can help:

### Reporting Issues

- 🐛 **Bug Reports**: [Create an issue](https://github.com/GryfOSS/symfony-unique-in-collection-constraint/issues/new) with detailed reproduction steps
- 💡 **Feature Requests**: [Submit an enhancement request](https://github.com/GryfOSS/symfony-unique-in-collection-constraint/issues/new) with your use case
- 📚 **Documentation**: Help improve documentation and examples

### Pull Requests

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Write tests** for your changes
4. **Ensure** all tests pass: `./scripts/run-all-tests.sh`
5. **Commit** your changes: `git commit -m 'Add amazing feature'`
6. **Push** to your branch: `git push origin feature/amazing-feature`
7. **Submit** a pull request

### Development Guidelines

- Maintain **100% test coverage**
- Follow **PSR-12** coding standards
- Add **comprehensive documentation** for new features
- Ensure **backward compatibility** when possible
- Write **clear commit messages**

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**IDCT Bartosz Pachołek**
- Email: bartosz+github@idct.tech
- GitHub: [@GryfOSS](https://github.com/GryfOSS)

---

Made with ❤️ for the Symfony community