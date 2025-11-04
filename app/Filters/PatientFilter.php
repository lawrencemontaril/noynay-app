<?php

namespace App\Filters;

use Illuminate\Http\Request;

class PatientFilter
{
    protected Request $request;
    protected $user;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = auth()->user();
    }

    public function apply($query)
    {
        return $query
            ->search($this->request->input('q'))
            ->when(request()->filled('gender') && request('gender') !== 'all', fn ($q) =>
                $q->where('gender', request('gender'))
            )
            ->when($this->request->filled('id') && $this->request->input('id') !== '', fn ($q) =>
                $q->where('patients.id', $this->request->input('id'))
            )
            ->when($this->user->can('patients:delete'), function ($q): void {
                if ($this->request->boolean('archived')) {
                    $q->onlyTrashed();
                }
            });
    }
}
