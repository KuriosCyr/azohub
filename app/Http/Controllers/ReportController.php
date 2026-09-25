<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Service;
use App\Services\AdminNotifier;
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

        if ($service->user_id === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas signaler votre propre service.');
        }

        // Un seul signalement en attente par utilisateur et par service : évite d'inonder la modération.
        $alreadyPending = Report::where('service_id', $service->id)
            ->where('reporter_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if (!$alreadyPending) {
            $report = Report::create([
                'service_id' => $service->id,
                'reporter_id' => Auth::id(),
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'status' => 'pending',
            ]);

            AdminNotifier::actionRequired(
                'Nouveau signalement à examiner',
                "Le service « {$service->title} » a été signalé (" . Report::reasonLabels()[$validated['reason']] . ').',
                route('filament.admin.resources.reports.edit', $report),
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Merci, votre signalement a été transmis à notre équipe.');
    }
}
