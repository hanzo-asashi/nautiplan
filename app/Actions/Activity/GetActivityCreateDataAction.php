<?php

namespace App\Actions\Activity;

use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Renja;
use App\Models\Unit;
use App\Models\User;

class GetActivityCreateDataAction
{
    /**
     * Get data required for the create activity page.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'programs' => Program::where('status', 'active')->get(['id', 'name', 'code']),
            'renjas' => Renja::where('status', 'approved')->get(['id', 'title']),
            'units' => Unit::where('is_active', true)->get(['id', 'name', 'code']),
            'fiscalYears' => FiscalYear::where('is_locked', false)->get(['id', 'year']),
            'users' => User::get(['id', 'name']),
        ];
    }
}
