<?php

namespace App\Actions\Report;

use App\Models\Program;

class BuildPokTreeAction
{
    /**
     * Build the POK tree structure.
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(?int $selectedYearId): array
    {
        if (! $selectedYearId) {
            return [];
        }

        // Fetch Program tree
        $programs = Program::where('fiscal_year_id', $selectedYearId)
            ->with([
                'activities.outputs.subOutputs.components.subComponents.activityBudgets.realizations',
            ])
            ->get();

        $tree = [];
        foreach ($programs as $prog) {
            $activities = [];
            foreach ($prog->activities as $act) {
                $outputs = [];
                foreach ($act->outputs as $out) {
                    $subOutputs = [];
                    foreach ($out->subOutputs as $subOut) {
                        $components = [];
                        foreach ($subOut->components as $comp) {
                            $subComponents = [];
                            foreach ($comp->subComponents as $subComp) {
                                $budgets = [];
                                foreach ($subComp->activityBudgets as $budget) {
                                    $pagu = (float) $budget->amount;
                                    $realisasi = (float) $budget->realizations->sum('amount');
                                    $budgets[] = [
                                        'id' => $budget->id,
                                        'type' => 'budget',
                                        'code' => $budget->account_code,
                                        'name' => $budget->account_name,
                                        'pagu' => $pagu,
                                        'realisasi' => $realisasi,
                                        'sisa' => $pagu - $realisasi,
                                        'children' => [],
                                    ];
                                }

                                $pagu = (float) array_sum(array_column($budgets, 'pagu'));
                                $realisasi = (float) array_sum(array_column($budgets, 'realisasi'));

                                $subComponents[] = [
                                    'id' => $subComp->id,
                                    'type' => 'sub_component',
                                    'code' => $subComp->code,
                                    'name' => $subComp->name,
                                    'pagu' => $pagu,
                                    'realisasi' => $realisasi,
                                    'sisa' => $pagu - $realisasi,
                                    'children' => $budgets,
                                ];
                            }

                            $pagu = (float) array_sum(array_column($subComponents, 'pagu'));
                            $realisasi = (float) array_sum(array_column($subComponents, 'realisasi'));

                            $components[] = [
                                'id' => $comp->id,
                                'type' => 'component',
                                'code' => $comp->code,
                                'name' => $comp->name,
                                'pagu' => $pagu,
                                'realisasi' => $realisasi,
                                'sisa' => $pagu - $realisasi,
                                'children' => $subComponents,
                            ];
                        }

                        $pagu = (float) array_sum(array_column($components, 'pagu'));
                        $realisasi = (float) array_sum(array_column($components, 'realisasi'));

                        $subOutputs[] = [
                            'id' => $subOut->id,
                            'type' => 'sub_output',
                            'code' => $subOut->code,
                            'name' => $subOut->name,
                            'pagu' => $pagu,
                            'realisasi' => $realisasi,
                            'sisa' => $pagu - $realisasi,
                            'children' => $components,
                        ];
                    }

                    $pagu = (float) array_sum(array_column($subOutputs, 'pagu'));
                    $realisasi = (float) array_sum(array_column($subOutputs, 'realisasi'));

                    $outputs[] = [
                        'id' => $out->id,
                        'type' => 'output',
                        'code' => $out->code,
                        'name' => $out->name,
                        'pagu' => $pagu,
                        'realisasi' => $realisasi,
                        'sisa' => $pagu - $realisasi,
                        'children' => $subOutputs,
                    ];
                }

                $pagu = (float) array_sum(array_column($outputs, 'pagu'));
                $realisasi = (float) array_sum(array_column($outputs, 'realisasi'));

                $activities[] = [
                    'id' => $act->id,
                    'type' => 'activity',
                    'code' => $act->code,
                    'name' => $act->name,
                    'pagu' => $pagu,
                    'realisasi' => $realisasi,
                    'sisa' => $pagu - $realisasi,
                    'children' => $outputs,
                ];
            }

            $pagu = (float) array_sum(array_column($activities, 'pagu'));
            $realisasi = (float) array_sum(array_column($activities, 'realisasi'));

            $tree[] = [
                'id' => $prog->id,
                'type' => 'program',
                'code' => $prog->code,
                'name' => $prog->name,
                'pagu' => $pagu,
                'realisasi' => $realisasi,
                'sisa' => $pagu - $realisasi,
                'children' => $activities,
            ];
        }

        return $tree;
    }
}
