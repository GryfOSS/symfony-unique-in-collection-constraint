#!/bin/bash

# Script to check if test coverage is 100%
# Usage: ./scripts/check-coverage.sh

echo "🧪 Running tests with coverage..."
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover=coverage/clover.xml --coverage-text

if [ ! -f "coverage/clover.xml" ]; then
    echo "❌ Coverage report not found!"
    exit 1
fi

echo ""
echo "📊 Checking coverage requirements..."

php -r "
\$xml = simplexml_load_file('coverage/clover.xml');
\$metrics = \$xml->project->metrics;
\$statements = (int)\$metrics['statements'];
\$coveredstatements = (int)\$metrics['coveredstatements'];
\$coverage = \$statements > 0 ? (\$coveredstatements / \$statements) * 100 : 100;

echo \"\\n📈 Coverage Report:\\n\";
echo \"- Total statements: \$statements\\n\";
echo \"- Covered statements: \$coveredstatements\\n\";
echo \"- Coverage: \" . number_format(\$coverage, 2) . \"%\\n\\n\";

if (\$coverage < 100) {
    echo \"❌ Coverage is \" . number_format(\$coverage, 2) . \"%, but 100% is required\\n\";
    echo \"💡 Run 'XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html=coverage/html' to see detailed report\\n\";
    exit(1);
}

echo \"✅ Coverage requirement met: 100%\\n\";
"