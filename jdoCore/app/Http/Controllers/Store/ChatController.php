<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return redirect()->route('admin.dashboard')->with('error', 'Admin menggunakan menu Pesan di Dashboard.');
        }

        $conversation = Conversation::firstOrCreate(
            ['user_id' => auth()->id()]
        );

        // Mark admin messages as read
        $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->update(['is_read' => true]);

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return view('theme::chat.index', compact('conversation', 'messages'));
    }

    public function store(Request $request)
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gunakan menu Admin untuk membalas.']);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Gunakan menu Admin untuk membalas.');
        }

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $conversation = Conversation::firstOrCreate(
            ['user_id' => auth()->id()]
        );

        $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('chat.index');
    }
}
