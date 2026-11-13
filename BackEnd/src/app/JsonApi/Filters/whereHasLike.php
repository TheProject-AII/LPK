<?php

namespace App\JsonApi\Filters;

use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;
use Illuminate\Support\Facades\Log;

class whereHasLike implements Filter
{
    use DeserializesValue;
    use IsSingular;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $column;

     /**
     * @var string
     */
    private string $relationship;

    /**
     * Create a new filter.
     *
     * @param string $name
     * @param string|null $column
     * @return ContractFilterDriverName
     */
    public static function make(string $name, string $relationship = null, string $column = null ): self
    {
        return new static($name, $relationship, $column,  );
    }

    /**
     * ContractFilterDriverName constructor.
     *
     * @param string $name
     * @param string|null $column
     */
    public function __construct(string $name, string $relationship = null, string $column = null)
    {
        $this->name = $name;
        $this->column = $column ?: Str::underscore($name);
        $this->relationship = $relationship;
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->name;
    }

    /**
     * Apply the filter to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $value)
    {
        $query->whereHas($this->relationship,fn($query) =>
            $query->where($this->column, 'like', "%{$value}%"));

    }
}
