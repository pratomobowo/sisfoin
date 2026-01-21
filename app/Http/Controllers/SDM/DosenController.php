<?php

namespace App\Http\Controllers\SDM;

use App\Http\Controllers\Controller;
use App\Http\Requests\DosenRequest;
use App\Models\Dosen;
use Illuminate\Support\Facades\Storage;

class DosenController extends Controller
{
    /**
     * Display the dosen management page.
     */
    public function index()
    {
        return view('sdm.dosens.index');
    }

    /**
     * Show the form for creating a new dosen.
     */
    public function create()
    {
        return view('sdm.dosens.create');
    }

    /**
     * Store a newly created dosen in storage.
     */
    public function store(DosenRequest $request)
    {
        $validatedData = $request->validated();

        // Create dosen
        $dosen = Dosen::create($validatedData);

        return redirect()->route('sdm.dosens.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    /**
     * Display the specified dosen.
     */
    public function show($id)
    {
        $dosen = Dosen::findOrFail($id);

        return view('sdm.dosens.show', compact('dosen'));
    }

    /**
     * Show the form for editing the specified dosen.
     */
    public function edit($id)
    {
        $dosen = Dosen::findOrFail($id);

        return view('sdm.dosens.edit', compact('dosen'));
    }

    /**
     * Update the specified dosen in storage.
     */
    public function update(DosenRequest $request, $id)
    {
        $dosen = Dosen::findOrFail($id);
        $validatedData = $request->validated();

        $dosen->update($validatedData);

        return redirect()->route('sdm.dosens.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Remove the specified dosen from storage.
     */
    public function destroy($id)
    {
        $dosen = Dosen::findOrFail($id);
        $dosen->delete();

        return redirect()->route('sdm.dosens.index')
            ->with('success', 'Data dosen berhasil dihapus.');
    }
}
