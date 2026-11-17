<?php

namespace App\Filament\Pages;

use App\Models\ChatMessage;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerChats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Customer chat';

    protected static ?string $slug = 'customer-chats';

    protected static string $view = 'filament.pages.customer-chats';

    public ?int $selectedCustomerId = null;

    public string $body = '';

    public function mount(): void
    {
        $this->selectedCustomerId = $this->customers()->first()?->id;
    }

    public function selectCustomer(int $customerId): void
    {
        abort_unless($this->customers()->contains('id', $customerId), 404);

        $this->selectedCustomerId = $customerId;
        $this->reset('body');
    }

    public function sendMessage(): void
    {
        $validated = $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $customer = $this->customers()->firstWhere('id', $this->selectedCustomerId);

        if (! $customer) {
            throw ValidationException::withMessages([
                'body' => 'Select a customer conversation before replying.',
            ]);
        }

        ChatMessage::create([
            'customer_id' => $customer->id,
            'sender_id' => Auth::id(),
            'body' => trim($validated['body']),
        ]);

        $this->reset('body');
    }

    protected function getViewData(): array
    {
        $customers = $this->customers();

        if ($this->selectedCustomerId === null) {
            $this->selectedCustomerId = $customers->first()?->id;
        }

        $selectedCustomer = $customers->firstWhere('id', $this->selectedCustomerId);
        $messages = $selectedCustomer
            ? ChatMessage::query()
                ->where('customer_id', $selectedCustomer->id)
                ->with('sender')
                ->latest('id')
                ->limit(100)
                ->get()
            : collect();

        return [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'messages' => $messages,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function customers(): Collection
    {
        return User::query()
            ->where('is_admin', false)
            ->whereHas('chatMessages')
            ->with('latestChatMessage')
            ->withMax('chatMessages', 'created_at')
            ->orderByDesc('chat_messages_max_created_at')
            ->get();
    }
}
