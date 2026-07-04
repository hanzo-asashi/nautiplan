<?php

namespace App\Actions\Budget;

use App\Models\ActivityBudget;

class CreateBudgetAction
{
    /**
     * Create a new activity budget.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): ActivityBudget
    {
        return ActivityBudget::create($data);
    }
}
