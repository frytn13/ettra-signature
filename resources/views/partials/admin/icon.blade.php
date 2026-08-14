@php($icon = $icon ?? 'dashboard')
@switch($icon)
    @case('dashboard')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="4" rx="2"/><rect x="14" y="11" width="7" height="10" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
        @break
    @case('sales')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V8l8-5 8 5v11"/><path d="M7 19h10M8 9h8M9 13h6"/><circle cx="18" cy="6" r="3"/></svg>
        @break
    @case('orders')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg>
        @break
    @case('return')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 8H4v-5"/><path d="M4 8c2-3 5-5 9-5a8 8 0 1 1-7.2 11.5"/><path d="M8 13h8M8 17h5"/></svg>
        @break
    @case('payment')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M7 15h4"/></svg>
        @break
    @case('shipping')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
        @break
    @case('promotion')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 12 8-8h6l2 2v6l-8 8-8-8Z"/><circle cx="16" cy="8" r="1"/><path d="m9 10 5 5"/></svg>
        @break
    @case('products')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/><path d="M12 11v10"/></svg>
        @break
    @case('category')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
        @break
    @case('attributes')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="8" cy="8" r="4"/><path d="M13 5h8M13 9h5M4 17h16M7 14v6M12 14v6M17 14v6"/></svg>
        @break
    @case('variants')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m8 4 4 2 4-2 4 2v5l-4 2-4-2-4 2-4-2V6l4-2Z"/><path d="M12 6v5M8 13v5l4 2 4-2v-5"/></svg>
        @break
    @case('warehouse')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 9 9-6 9 6v11H3V9Z"/><path d="M8 20v-7h8v7M7 9h.01M12 9h.01M17 9h.01"/></svg>
        @break
    @case('stock')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7 12 3l8 4-8 4-8-4Z"/><path d="M4 12l8 4 8-4M4 17l8 4 8-4"/></svg>
        @break
    @case('movement')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 7h11l-3-3M17 17H6l3 3"/><path d="M18 7 15 4M6 17l3 3"/></svg>
        @break
    @case('adjustment')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 5h14v14H5z"/><path d="M8 9h8M8 13h5"/><path d="m14 16 2 2 4-4"/></svg>
        @break
    @case('damaged')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 9 16H3L12 3Z"/><path d="M12 9v4M12 16h.01"/></svg>
        @break
    @case('transfer')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 7h12l-3-3M19 17H7l3 3"/><path d="M17 7l-3-3M7 17l3 3"/></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><circle cx="17" cy="9" r="2"/><path d="M16 14h1a4 4 0 0 1 4 4v2"/></svg>
        @break
    @case('activity')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 12h4l2-5 4 10 2-5h6"/><circle cx="12" cy="12" r="9"/></svg>
        @break
    @case('purchase')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.6a2 2 0 0 0 2-1.6L20 8H7"/><circle cx="10" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/></svg>
        @break
    @case('report')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 17v-4M12 17v-7M15 17v-2"/></svg>
        @break
    @case('settings')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34A1.7 1.7 0 0 0 14 20.92V21h-4v-.08A1.7 1.7 0 0 0 8.97 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.08 14H3v-4h.08A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.08V3h4v.08A1.7 1.7 0 0 0 15.03 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.2.6.8 1 1.52 1H21v4h-.08c-.7 0-1.31.4-1.52 1Z"/></svg>
        @break
    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
@endswitch
