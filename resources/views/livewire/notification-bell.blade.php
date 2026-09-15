<div x-data="{ open: false }" @click.away="open = false" class="relative" wire:poll.30s>
    <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-ink-100/40 transition text-ink-500 hover:text-ink-900">
        <x-app-icon name="bell" class="w-5 h-5" />
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full bg-terracotta-600 text-cream-50 text-[10px] font-semibold leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open"
         x-transition
         class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-cream-50 rounded-lg shadow-lg border border-ink-100 z-50"
         style="display: none;">

        <div class="flex items-center justify-between px-4 py-3 border-b border-ink-100">
            <p class="font-semibold text-ink-900">Notifications</p>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-terracotta-600 hover:text-terracotta-700 transition">
                    Tout marquer comme lu
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-ink-100">
            @forelse($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}"
                   wire:click="markAsRead('{{ $notification->id }}')"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-ink-100/30 transition {{ is_null($notification->read_at) ? 'bg-terracotta-50/40' : '' }}">
                    <div class="w-9 h-9 rounded-full bg-terracotta-50 flex items-center justify-center text-terracotta-700 flex-shrink-0">
                        <x-app-icon :name="$notification->data['icon'] ?? 'chat'" class="w-4 h-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-ink-500 line-clamp-2">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="text-xs text-ink-300 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(is_null($notification->read_at))
                        <span class="w-2 h-2 rounded-full bg-terracotta-600 flex-shrink-0 mt-1.5"></span>
                    @endif
                </a>
            @empty
                <div class="px-4 py-10 text-center">
                    <x-app-icon name="bell" class="w-8 h-8 text-ink-200 mx-auto mb-2" />
                    <p class="text-sm text-ink-400">Aucune notification pour le moment.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
