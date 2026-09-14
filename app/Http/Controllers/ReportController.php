<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Enregistrer un signalement pour un service.
     */
    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'reason' => 'required|in:' . implode(',', array_keys(Report::reasonLabels())),
            'details' => 'nullable|string|max:1000',
        ]);

        Report::create([
            'service_id' => $service->id,
            'reporter_id' => Auth::id(),
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Merci, votre signalement a été transmis à notre équipe.');
    }
}
