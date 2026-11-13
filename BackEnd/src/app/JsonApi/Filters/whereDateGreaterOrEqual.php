<?php

namespace App\JsonApi\Filters;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Support\Str;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;

class whereDateGreaterOrEqual implements Filter
{
    use DeserializesValue;
    use IsSingular;

    private string $name;
    private string $column;

    public static function make(string $name, string $column = null): self
    {
        return new static($name, $column);
    }

    public function __construct(string $name, string $column = null)
    {
        $this->name = $name;
        $this->column = $column ?: Str::underscore($name);
    }

    public function key(): string
    {
        return $this->name;
    }

    public function apply($query, $value)
    {
        $timezone = Auth::user()->timezone ?? 'UTC';
        $date = Carbon::parse($value, $timezone)->startOfDay()->utc();
        return $query->where($this->column, '>=', $date);
    }
}
