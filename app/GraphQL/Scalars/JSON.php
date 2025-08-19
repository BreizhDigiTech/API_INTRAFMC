<?php

namespace App\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Value;

/**
 * Custom JSON scalar to handle arbitrary JSON values in the schema.
 */
class JSON extends ScalarType
{
    public string $name = 'JSON';

    public ?string $description = 'Arbitrary JSON value.';

    /**
     * Serialize the internal value to include in a response.
     *
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Parse externally provided variable values (from the client) to use internally.
     *
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        return $value;
    }

    /**
     * Parse literal values provided in the GraphQL query.
     *
     * @param Node $valueNode
     * @param array<string,mixed>|null $variables
     * @return mixed
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // Use graphql-php utility to transform AST to PHP value without type hints
        return Value::valueFromASTUntyped($valueNode, $variables ?? []);
    }
}
