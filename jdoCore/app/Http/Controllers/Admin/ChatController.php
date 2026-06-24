<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Conversation::with([
                'user',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])->select('conversations.*');

            return DataTables::of($query)
                ->addColumn('user_info', function ($conversation) {
                    $initial = strtoupper(substr($conversation->user->name, 0, 1));
                    return '
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width:40px;height:40px">
                                ' . $initial . '
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">' . $conversation->user->name . '</span>
                                <span class="small text-muted">' . $conversation->user->email . '</span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('last_message', function ($conversation) {
                    $lastMessage = $conversation->messages->first();
                    $unreadCount = $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                    $class = $unreadCount > 0 ? 'fw-bold text-dark' : 'text-muted';
                    $msgText = $lastMessage ? htmlspecialchars($lastMessage->message) : 'Belum ada pesan';
                    return '<span class="d-block text-truncate ' . $class . '" style="max-width: 300px;">' . $msgText . '</span>';
                })
                ->addColumn('time', function ($conversation) {
                    $unreadCount = $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                    $class = $unreadCount > 0 ? 'fw-bold text-primary' : 'text-muted';
                    $time = $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : '-';
                    return '<span class="small ' . $class . '">' . $time . '</span>';
                })
                ->addColumn('action', function ($conversation) {
                    $unreadCount = $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                    $showUrl = route('admin.chat.show', $conversation);
                    $badge = $unreadCount > 0 ? '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">' . $unreadCount . '</span>' : '';
                    return '
                        <div class="d-flex justify-content-center">
                            <a href="' . $showUrl . '" class="btn btn-sm btn-light text-primary position-relative rounded-pill px-3" style="font-size: 0.75rem;" title="Balas Pesan">
                                <i class="bi bi-chat-dots me-1"></i> Balas
                                ' . $badge . '
                            </a>
                        </div>
                    ';
                })
                ->setRowClass(function ($conversation) {
                    $unreadCount = $conversation->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                    return $unreadCount > 0 ? 'bg-primary bg-opacity-10' : '';
                })
                ->rawColumns(['user_info', 'last_message', 'time', 'action'])
                ->make(true);
        }

        return view('admin.chat.index');
    }

    public function show(Conversation $conversation)
    {
        // Mark messages as read where sender is not the current admin (i.e. sender is customer)
        $conversation->messages()->where('sender_id', $conversation->user_id)->where('is_read', false)->update(['is_read' => true]);

        $messages = $conversation->messages()->with('sender')->get();
        return view('admin.chat.show', compact('conversation', 'messages'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back();
    }
}
