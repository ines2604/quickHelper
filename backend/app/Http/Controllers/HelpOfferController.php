<?php

namespace App\Http\Controllers;

use App\Models\Help_offer;
use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpOfferController extends Controller
{
    public function store(Request $request, $requestId)
    {
        $requestData = RequestModel::findOrFail($requestId);

        if (!$requestData->isOpen()) {
            return response()->json(['message' => 'Cette demande n\'est plus disponible'], 400);
        }

        if ($requestData->user_id === Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas proposer de l\'aide pour votre propre demande'], 403);
        }

        $existing = Help_offer::where('request_id', $requestId)
            ->where('helper_id', Auth::id())
            ->first();
        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà soumis une offre pour cette demande'], 400);
        }

        $request->validate([
            'message'         => 'nullable|string',
            'proposed_budget' => 'nullable|numeric',
            'file'            => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        $filePath = null;
        $fileName = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getMimeType();
            $filePath = $file->store('offers', 'public');
        }

        $offer = Help_offer::create([
            'request_id'      => $requestData->id,
            'helper_id'       => Auth::id(),
            'message'         => $request->message,
            'proposed_budget' => $request->proposed_budget,
            'status'          => 'pending',
            'file_path'       => $filePath,
            'file_name'       => $fileName,
            'file_type'       => $fileType,
        ]);

        return response()->json($offer, 201);
    }

    public function accept($offerId)
    {
        $offer       = Help_offer::with('request')->findOrFail($offerId);
        $requestData = $offer->request;

        if ($requestData->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if (!$requestData->isOpen()) {
            return response()->json(['message' => 'Cette demande est déjà traitée'], 400);
        }

        $offer->update(['status' => 'accepted', 'responded_at' => now()]);

        $requestData->update([
            'status'             => 'in_progress',
            'accepted_helper_id' => $offer->helper_id,
            'accepted_at'        => now(),
        ]);

        Help_offer::where('request_id', $requestData->id)
            ->where('id', '!=', $offerId)
            ->update(['status' => 'rejected']);

        return response()->json(['message' => 'Aide acceptée avec succès']);
    }

    public function reject($offerId)
    {
        $offer       = Help_offer::with('request')->findOrFail($offerId);
        $requestData = $offer->request;

        if ($requestData->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if ($offer->status !== 'pending') {
            return response()->json(['message' => 'Cette offre ne peut plus être refusée'], 400);
        }

        $offer->update(['status' => 'rejected', 'responded_at' => now()]);

        return response()->json(['message' => 'Offre refusée']);
    }
}
