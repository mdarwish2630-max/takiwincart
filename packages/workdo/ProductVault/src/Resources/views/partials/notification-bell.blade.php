<style>
.vault-notif-dropdown {
    position: relative;
    display: inline-block;
}
.vault-notif-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    color: #fff;
    font-size: 20px;
    transition: background 0.2s;
}
.vault-notif-btn:hover {
    background: rgba(255,255,255,0.1);
}
.vault-notif-badge {
    position: absolute;
    top: 2px; right: 2px;
    background: #ff4d4f;
    color: #fff;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.vault-notif-panel {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    width: 380px;
    max-height: 480px;
    overflow: hidden;
    z-index: 9999;
    margin-top: 8px;
}
.vault-notif-panel.show {
    display: block;
    animation: vaultNotifSlide 0.2s ease;
}
@keyframes vaultNotifSlide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.vault-notif-header {
    padding: 16px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.vault-notif-body {
    max-height: 360px;
    overflow-y: auto;
}
.vault-notif-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: inherit;
    display: block;
}
.vault-notif-item:hover { background: #f8f9fa; }
.vault-notif-item.unread { background: #f0f7ff; }
.vault-notif-item.unread:hover { background: #e6f0ff; }
.vault-notif-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    flex-shrink: 0;
}
.vault-notif-time { font-size: 12px; color: #999; }
</style>

<div class="vault-notif-dropdown">
    <button class="vault-notif-btn" onclick="toggleVaultNotif(event)" title="{{ __("Notifications") }}">
        <i class="ti ti-bell"></i>
        @php
            $unreadCount = \Workdo\ProductVault\Entities\VaultNotification::unreadCount(\Illuminate\Support\Facades\Auth::id());
        @endphp
        @if($unreadCount > 0)
            <span class="vault-notif-badge">{{ $unreadCount > 99 ? "99+" : $unreadCount }}</span>
        @endif
    </button>

    <div class="vault-notif-panel" id="vaultNotifPanel">
        <div class="vault-notif-header">
            <strong>{{ __("Notifications") }}</strong>
            @if($unreadCount > 0)
                <a href="javascript:void(0)" onclick="markAllVaultNotifRead()" style="font-size:13px;color:var(--bs-primary);">
                    {{ __("Mark all as read") }}
                </a>
            @endif
        </div>
        <div class="vault-notif-body">
            @php
                $notifications = \Workdo\ProductVault\Entities\VaultNotification::getUserNotifications(\Illuminate\Support\Facades\Auth::id(), 20);
            @endphp
            @forelse($notifications as $notif)
            <a href="{{ $notif->link ?: "javascript:void(0)" }}" class="vault-notif-item {{ $notif->is_read ? "" : "unread" }}"
               onclick="markVaultNotifRead({{ $notif->id }}, event)">
                <div class="d-flex align-items-start gap-3">
                    <div class="vault-notif-icon bg-{{ $notif->icon_color ?? "primary" }}">
                        <i class="ti ti-{{ $notif->icon ?? "bell" }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong style="font-size:13px;">{{ $notif->title }}</strong>
                            @if(!$notif->is_read)
                                <span style="width:8px;height:8px;background:var(--bs-primary);border-radius:50%;display:inline-block;flex-shrink:0;margin-top:6px;"></span>
                            @endif
                        </div>
                        <p style="font-size:12px;color:#666;margin:4px 0 0;">{{ Str::limit($notif->message, 80) }}</p>
                        <span class="vault-notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-4 text-muted" style="font-size:14px;">
                <i class="ti ti-bell-off" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                {{ __("No notifications yet.") }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleVaultNotif(e) {
    e.stopPropagation();
    document.getElementById("vaultNotifPanel").classList.toggle("show");
}
document.addEventListener("click", function() {
    document.getElementById("vaultNotifPanel").classList.remove("show");
});

function markVaultNotifRead(id, e) {
    if (e) e.preventDefault();
    fetch("{{ route("vault-notifications.read") }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").content
        },
        body: JSON.stringify({ notification_id: id })
    }).then(r => r.json()).then(data => {
        if (data.unread_count !== undefined) {
            updateNotifBadge(data.unread_count);
            if (data.unread_count === 0) {
                document.querySelectorAll(".vault-notif-item.unread").forEach(el => el.classList.remove("unread"));
            }
        }
    });
}

function markAllVaultNotifRead() {
    fetch("{{ route("vault-notifications.read-all") }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").content
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            updateNotifBadge(0);
            document.querySelectorAll(".vault-notif-item.unread").forEach(el => el.classList.remove("unread"));
        }
    });
}

function updateNotifBadge(count) {
    const badge = document.querySelector(".vault-notif-badge");
    if (count > 0) {
        if (badge) {
            badge.textContent = count > 99 ? "99+" : count;
        } else {
            const btn = document.querySelector(".vault-notif-btn");
            const newBadge = document.createElement("span");
            newBadge.className = "vault-notif-badge";
            newBadge.textContent = count;
            btn.appendChild(newBadge);
        }
    } else {
        if (badge) badge.remove();
    }
}

// Auto-refresh notifications every 60 seconds
setInterval(function() {
    fetch("{{ route("vault-notifications.read") }}?count=1", {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").content
        }
    });
}, 60000);
</script>