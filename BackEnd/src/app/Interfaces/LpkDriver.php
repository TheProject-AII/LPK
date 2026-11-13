<?php
declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LaravelJsonApi\Eloquent\Contracts\Driver;
use Illuminate\Support\Facades\Log;

class LpkDriver implements Driver
{

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * LpkDriver constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function query(): Builder
    {   
        if(auth()->user()->lpk_id){
            return $this->model::where('lpk_id', auth()->user()->lpk_id)->newQuery();
        }
        else{
            return $this->model->newQuery();
        }
    }

    /**
     * @inheritDoc
     */
    public function queryAll(): Builder
    {
        if(auth()->user()->lpk_id){
            return $this->model::where('lpk_id', auth()->user()->lpk_id)->newQuery();
        }
        else{
            return $this->model->newQuery();
        }
        
    }

    /**
     * @inheritDoc
     */
    public function newInstance(): Model
    {
        return $this->model->newInstance();
    }

    /**
     * @inheritDoc
     */
    public function persist(Model $model): bool
    {
        return (bool) $model->save();
    }

    /**
     * @inheritDoc
     */
    public function destroy(Model $model): bool
    {
        return (bool) $model->delete();
    }

}