<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomerChat extends Component
{
    public string $body = '';

    public function mount(): void
    {
        abort_if(Auth::user()->is_admin, 403);
    }

    public function sendMessage(): void
    {
        abort_unless(Auth::check() && ! Auth::user()->is_admin, 403);

        $validated = $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        ChatMessage::create([
            'customer_id' => Auth::id(),
            'sender_id' => Auth::id(),
            'body' => trim($validated['body']),
        ]);

        $this->reset('body');
    }

    public function render(): View
    {
        $messages = ChatMessage::query()
            ->where('customer_id', Auth::id())
            ->with('sender')
            ->latest('id')
            ->limit(100)
            ->get();

        return view('livewire.customer-chat', [
            'messages' => $messages,
        ]);
    }
}
