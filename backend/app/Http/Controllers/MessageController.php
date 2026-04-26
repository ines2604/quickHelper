<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Request as RequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:requests,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB max
        ]);

        $requestData = RequestModel::findOrFail($request->request_id);

        $isCreator  = $requestData->user_id === Auth::id();
        $isHelper   = $requestData->accepted_helper_id === Auth::id();
        $hasOffered = $requestData->helpOffers()->where('helper_id', Auth::id())->exists();

        if (!$isCreator && !$isHelper && !$hasOffered) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à discuter sur cette demande'], 403);
        }

        if (!$request->content && !$request->hasFile('file')) {
            return response()->json(['message' => 'Un message ou un fichier est requis'], 400);
        }

        $filePath = null;
        $fileName = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getMimeType();
            $filePath = $file->store('messages', 'public');
        }

        $message = Message::create([
            'request_id'  => $request->request_id,
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content ?? '',
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_type'   => $fileType,
        ]);

        $message->load('sender');

        return response()->json($message, 201);
    }

    public function index($requestId)
    {
        $requestData = RequestModel::findOrFail($requestId);

        if ($requestData->user_id !== Auth::id() && $requestData->accepted_helper_id !== Auth::id()) {
            if (!$requestData->helpOffers()->where('helper_id', Auth::id())->exists()) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }
        }

        $messages = Message::with('sender')
            ->where('request_id', $requestId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                if ($msg->file_path) {
                    $msg->file_url = Storage::url($msg->file_path);
                }
                return $msg;
            });

        return response()->json($messages);
    }
}
