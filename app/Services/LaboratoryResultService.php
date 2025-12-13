<?php

namespace App\Services;

use App\Models\LaboratoryResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class LaboratoryResultService
{
    /**
     * Create a laboratory result
     */
    public function create(array $data): LaboratoryResult
    {
        if (request()->hasFile('results_file')) {
            $path = request()->file('results_file')->store('laboratory_results', 'public');
            $data['results_file_path'] = $path;
        }

        return LaboratoryResult::create(Arr::except($data, ['results_file']));
    }

    /**
     * Update a laboratory result
     */
    public function update(LaboratoryResult $laboratoryResult, array $data): LaboratoryResult
    {
        if (request()->hasFile('results_file')) {
            if ($laboratoryResult->results_file_path) {
                Storage::disk('public')->delete($laboratoryResult->results_file_path);
            }

            $path = request()->file('results_file')->store('laboratory_results', 'public');
            $data['results_file_path'] = $path;
        }

        $laboratoryResult->update([
            'description' => $data['description'],
            'results_file_path' => $data['results_file_path'] ?? null,
        ]);

        return $laboratoryResult;
    }
}
