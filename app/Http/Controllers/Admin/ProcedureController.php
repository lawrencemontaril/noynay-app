<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcedureRequest;
use App\Models\Procedure;
use Illuminate\Http\Request;

class ProcedureController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProcedureRequest $request)
    {
        Procedure::create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Procedure created successfully.');
    }
}
