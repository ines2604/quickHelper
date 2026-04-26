<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    // List open requests - EXCLUDE current user's own requests (fix #7)
    public function index()
    {
        $requests = RequestModel::with('user')
            ->where('status', 'open')
            ->where('user_id', '!=', Auth::id())
            ->latest()
            ->get();
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'budget'      => 'nullable|numeric',
            'category'    => 'required|string',
            'urgency'     => 'required|string',
            'deadline'    => 'required|date',
            'location'    => 'nullable|string',
        ]);

        $newRequest = RequestModel::create([
            ...$request->only(['title', 'description', 'budget', 'category', 'urgency', 'deadline', 'location']),
            'user_id' => Auth::id(),
            'status'  => 'open',
        ]);

        return response()->json($newRequest, 201);
    }

    public function show($id)
    {
        $request = RequestModel::with(['user', 'helpOffers.helper', 'acceptedHelper', 'messages.sender'])->findOrFail($id);
        return response()->json($request);
    }

    public function update(Request $request, $id)
    {
        $requestData = RequestModel::findOrFail($id);

        if ($requestData->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if (!$requestData->isOpen()) {
            return response()->json(['message' => 'Impossible de modifier une demande en cours ou terminée'], 403);
        }

        $requestData->update($request->only(['title', 'description', 'budget', 'category', 'urgency', 'deadline', 'location']));
        return response()->json($requestData);
    }

    public function destroy($id)
    {
        $requestData = RequestModel::findOrFail($id);

        if ($requestData->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if (!$requestData->isOpen()) {
            return response()->json(['message' => 'Impossible de supprimer une demande en cours ou terminée'], 403);
        }

        $requestData->delete();
        return response()->json(['message' => 'Demande supprimée avec succès']);
    }

    public function history()
    {
        $myRequests   = RequestModel::where('user_id', Auth::id())->with('user')->latest()->get();
        $acceptedHelp = RequestModel::where('accepted_helper_id', Auth::id())->with('user')->latest()->get();

        return response()->json([
            'my_requests'     => $myRequests,
            'helped_requests' => $acceptedHelp,
        ]);
    }

    public function markCompleted($id)
    {
        $requestData = RequestModel::findOrFail($id);
        $user        = Auth::user();

        if ($requestData->user_id !== $user->id && $requestData->accepted_helper_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $requestData->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json(['message' => 'Demande marquée comme terminée']);
    }

    // Manual status update by owner (fix #5)
    public function updateStatus(Request $request, $id)
    {
        $requestData = RequestModel::findOrFail($id);

        if ($requestData->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,completed',
        ]);

        $requestData->update(['status' => $request->status]);

        return response()->json(['message' => 'Statut mis à jour', 'status' => $request->status]);
    }
}
