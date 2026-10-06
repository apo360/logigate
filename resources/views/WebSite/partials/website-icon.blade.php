<svg class="website-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($icon)
    @case('home')<path d="m3 10 9-7 9 7v11h-6v-7H9v7H3Z"/>@break
    @case('file')<path d="M14 2H5v20h14V7Zm0 0v5h5M8 12h8M8 16h8"/>@break
    @case('store')<path d="M3 10h18l-2-6H5Zm1 0v11h16V10M9 21v-7h6v7M8 4v6m8-6v6"/>@break
    @case('signature')<path d="M12 3H4v18h16v-8M8 15l2-5 9-9 4 4-9 9Zm10-13 4 4M8 18h8"/>@break
    @case('user')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
    @case('user-plus')<circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2m3-13v6m-3-3h6"/>@break
    @case('login')<path d="M14 3h7v18h-7M2 12h13m-5-5 5 5-5 5"/>@break
    @default<path d="M3 6h18M3 12h18M3 18h18"/>
@endswitch
</svg>
