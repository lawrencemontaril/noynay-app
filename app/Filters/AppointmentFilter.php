<?php

namespace App\Filters;

use Illuminate\Http\Request;

class AppointmentFilter
{
    protected Request $request;
    protected $user;
    protected array $labTypes = ['pregnancy_test', 'papsmear', 'cbc', 'urinalysis', 'fecalysis'];

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = auth()->user();
    }

    public function apply($query)
    {
        return $query
            ->when($this->request->filled('q'), function ($q) {
                $q->whereHas('patient', fn ($q) =>
                    $q->search($this->request->input('q'))
                );
            })
            ->when($this->request->filled('status') && $this->request->input('status') !== 'all', function ($q) {
                $q->where('status', $this->request->input('status'));
            })
            ->when($this->request->filled('id') && $this->request->input('id') !== '', function ($q) {
                $q->where('appointments.id', $this->request->input('id'));
            })
            ->when($this->user->hasRole('laboratory_staff'), function ($q) {
                $q->whereIn('type', $this->labTypes);

                if ($this->request->filled('type') && in_array($this->request->input('type'), $this->labTypes)) {
                    $q->where('type', $this->request->input('type'));
                }
            })
            ->when($this->user->hasRole('doctor') && ! $this->user->hasRole('laboratory_staff'), function ($q) {
                $q->whereNotIn('type', $this->labTypes);

                if ($this->request->filled('type') && ! in_array($this->request->input('type'), $this->labTypes) && $this->request->input('type') !== 'all') {
                    $q->where('type', $this->request->input('type'));
                }
            })
            ->when(! $this->user->hasAnyRole(['doctor', 'laboratory_staff']), function ($q) {
                if ($this->request->filled('type') && $this->request->input('type') !== 'all') {
                    $q->where('type', $this->request->input('type'));
                }
            })
            ->when($this->user->can('appointments:delete'), function ($q): void {
                if ($this->request->boolean('archived')) {
                    $q->onlyTrashed();
                }
            });
    }
}
