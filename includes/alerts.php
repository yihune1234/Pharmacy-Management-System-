<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message to be displayed on the next page load.
 */
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Render the flash message if one exists.
 */
function render_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $message = $flash['message'];
        $type = $flash['type'];
        unset($_SESSION['flash_message']);

        $colors = [
            'success' => 'bg-emerald-500 border-emerald-600 text-white',
            'error' => 'bg-rose-500 border-rose-600 text-white',
            'warning' => 'bg-amber-500 border-amber-600 text-white',
            'info' => 'bg-sky-500 border-sky-600 text-white'
        ];

        $icons = [
            'success' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'error' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'warning' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'info' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        ];

        $colorClass = $colors[$type] ?? $colors['info'];
        $icon = $icons[$type] ?? $icons['info'];

        echo "
        <div id='toast' class='fixed top-4 right-4 z-[9999] transform transition-all duration-500 translate-x-full'>
            <div class='flex items-center {$colorClass} px-6 py-4 rounded-2xl shadow-2xl border min-w-[300px]'>
                <span class='mr-3'>{$icon}</span>
                <span class='font-bold tracking-tight'>{$message}</span>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast');
                toast.classList.remove('translate-x-full');
                
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 500);
                }, 4000);
            }, 100);
        </script>
        ";
    }
}
