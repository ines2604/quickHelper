<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:requests,id',
            'to_user_id' => 'required|exists:users,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string',
        ]);

        $requestData  = RequestModel::findOrFail($request->request_id);
        $currentUser  = Auth::user();

        if ($requestData->user_id !== $currentUser->id) {
            return response()->json(['message' => 'Seul le demandeur peut noter l\'aidant'], 403);
        }

        if ($requestData->accepted_helper_id != $request->to_user_id) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas l\'aidant assigné'], 400);
        }

        if (!$requestData->isCompleted()) {
            return response()->json(['message' => 'La demande doit être terminée pour être notée'], 400);
        }

        $existing = Rating::where('from_user_id', $currentUser->id)
                          ->where('request_id', $requestData->id)
                          ->first();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà noté cette demande'], 400);
        }

        $rating = Rating::create([
            'from_user_id' => $currentUser->id,
            'to_user_id'   => $request->to_user_id,
            'request_id'   => $requestData->id,
            'rating'       => $request->rating,
            'comment'      => $request->comment,
        ]);

        $this->updateUserRating($request->to_user_id);

        return response()->json($rating, 201);
    }

    public function received()
    {
        $ratings = Rating::with('fromUser')
                         ->where('to_user_id', Auth::id())
                         ->latest()
                         ->get();
        return response()->json($ratings);
    }

    private function updateUserRating($userId)
    {
        $user    = \App\Models\User::find($userId);
        $ratings = $user->ratingsReceived;
        $count   = $ratings->count();
        $avg     = $count > 0 ? $ratings->sum('rating') / $count : 0;

        $user->update([
            'rating_count' => $count,
            'rating_avg'   => round($avg, 2),
        ]);
    }
}
