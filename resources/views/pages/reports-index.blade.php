<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Inbox</title>
    @livewireStyles
</head>
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    <main style="max-width:72rem;margin:0 auto;padding:2rem 1rem 3rem;">
        <header style="margin-bottom:2rem;">
            <div>
                <p style="margin:0;color:#475569;font-size:0.95rem;">corepine/reportable</p>
                <h1 style="margin:0.35rem 0 0;font-size:2rem;line-height:1.1;">Report Inbox</h1>
                <p style="margin:0.75rem 0 0;color:#475569;max-width:42rem;line-height:1.6;">
                    Review submitted reports across posts, comments, reviews, and any other models using the package.
                </p>
            </div>
        </header>

        @if (session('reportable.status'))
            <div style="margin-bottom:1.25rem;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;border-radius:0.9rem;padding:0.9rem 1rem;">
                {{ session('reportable.status') }}
            </div>
        @endif

        @livewire('corepine-reports-index')
    </main>

    @livewireScripts
</body>
</html>
