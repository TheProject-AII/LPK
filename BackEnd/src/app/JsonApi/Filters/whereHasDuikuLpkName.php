<?php

namespace App\JsonApi\Filters;

use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Eloquent\Contracts\Filter;
use LaravelJsonApi\Eloquent\Filters\Concerns\DeserializesValue;
use LaravelJsonApi\Eloquent\Filters\Concerns\IsSingular;

class whereHasDuikuLpkName implements Filter
{
    use DeserializesValue;
    use IsSingular;

    private string $name;

    public static function make(string $name): self
    {
        return new static($name);
    }

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function key(): string
    {
        return $this->name;
    }

    public function apply($query, $value)
    {
        $table = $query->getModel()->getTable();

        return $query->whereExists(function ($sub) use ($value, $table) {
            $sub->select(DB::raw(1))
                ->from('installments')
                ->join('contracts', 'contracts.id', '=', 'installments.installment_contract_id')
                ->join('lpks', 'lpks.id', '=', 'contracts.lpk_id')
                ->whereRaw("JSON_CONTAINS({$table}.installment_ids, JSON_ARRAY(installments.id))")
                ->where('lpks.lpk_name', 'like', "%{$value}%");
        });
    }
}
