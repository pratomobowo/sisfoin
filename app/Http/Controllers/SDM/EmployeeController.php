<?php

namespace App\Http\Controllers\SDM;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display the employee management page.
     */
    public function index()
    {
        return view('sdm.employees.index');
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        return view('sdm.employees.create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $validatedData = $request->validated();

        // Create employee
        $employee = Employee::create($validatedData);

        return redirect()->route('sdm.employees.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Display the specified employee.
     */
    public function show($id)
    {
        $employee = Employee::findOrFail($id);

        return view('sdm.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        return view('sdm.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(EmployeeRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validatedData = $request->validated();

        $employee->update($validatedData);

        return redirect()->route('sdm.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('sdm.employees.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }
}
