<?php

namespace App\Http\Controllers;

use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdvanceController extends Controller
{
    /**
     * Display a listing of the advances.
     */
    public function index()
    {
        $advances = Advance::with('employee')->orderBy('created_at', 'desc')->get();
        return view('management.newadvance.manage', compact('advances'));
    }

    /**
     * Show the form for creating a new advance.
     */
    public function create()
    {
        $employees = Employee::all();
        return view('management.newadvance.create', compact('employees'));
    }

    /**
     * Store a newly created advance in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employment_ID' => 'required|exists:employees,employee_id',
            'advance_amount' => 'required|numeric|min:0.01',
            'advance_date' => 'required|date',
            'status' => 'required|in:approved,pending,rejected',
            'description' => 'nullable|string|max:1000',
            'advance_documents.*' => 'nullable|file|mimes:pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $documentPaths = [];
        
        // Handle file uploads
        if ($request->hasFile('advance_documents')) {
            foreach ($request->file('advance_documents') as $file) {
                $path = $file->store('advance_documents', 'public');
                $documentPaths[] = $path;
            }
        }

        Advance::create([
            'employment_ID' => $request->employment_ID,
            'advance_amount' => $request->advance_amount,
            'advance_date' => $request->advance_date,
            'status' => $request->status,
            'description' => $request->description,
            'advance_documents' => json_encode($documentPaths)
        ]);

        return redirect()->route('newadvance.manage')
            ->with('success', 'Advance record added successfully!');
    }

    /**
     * Display the specified advance.
     */
    public function show(Advance $advance)
    {
        $advance->load('employee');
        return view('advances.show', compact('advance'));
    }

    /**
     * Show the form for editing the specified advance.
     */
    public function edit($id)
    {
        $advance = Advance::with('employee')->findOrFail($id);
        $employees = Employee::all();
        return view('management.newadvance.edit', compact('advance', 'employees'));
    }

    /**
     * Update the specified advance in storage.
     */
    public function update(Request $request, $id)
    {
        $advance = Advance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employment_ID' => 'required|exists:employees,employee_id',
            'advance_amount' => 'required|numeric|min:0.01',
            'advance_date' => 'required|date',
            'status' => 'required|in:approved,pending,rejected',
            'description' => 'nullable|string|max:1000',
            'advance_documents.*' => 'nullable|file|mimes:pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle existing files
        $existingFiles = [];
        if ($request->has('existing_files')) {
            $existingFiles = json_decode($request->existing_files, true) ?: [];
        }

        // Handle new file uploads
        if ($request->hasFile('advance_documents')) {
            foreach ($request->file('advance_documents') as $file) {
                $path = $file->store('advance_documents', 'public');
                $existingFiles[] = $path;
            }
        }

        // Delete old files that are not in the existing files list
        $oldDocuments = json_decode($advance->advance_documents, true) ?: [];
        foreach ($oldDocuments as $oldDoc) {
            if (!in_array($oldDoc, $existingFiles)) {
                Storage::disk('public')->delete($oldDoc);
            }
        }

        $advance->update([
            'employment_ID' => $request->employment_ID,
            'advance_amount' => $request->advance_amount,
            'advance_date' => $request->advance_date,
            'status' => $request->status,
            'description' => $request->description,
            'advance_documents' => json_encode($existingFiles)
        ]);

        return redirect()->route('newadvance.manage')
            ->with('success', 'Advance record updated successfully!');
    }

    /**
     * Remove the specified advance from storage.
     */
    public function destroy($id)
    {
        $advance = Advance::findOrFail($id);

        // Delete associated documents
        $documents = json_decode($advance->advance_documents, true) ?: [];
        foreach ($documents as $document) {
            Storage::disk('public')->delete($document);
        }

        $advance->delete();

        return redirect()->route('newadvance.manage')
            ->with('success', 'Advance record deleted successfully!');
    }
}