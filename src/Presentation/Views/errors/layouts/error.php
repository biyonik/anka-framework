<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Error' ?></title>
    <style>
        :root {
            --error-color-400: #f87171;
            --error-color-500: #ef4444;
            --error-color-600: #dc2626;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f3f4f6;
        }

        .error-container {
            max-width: 90%;
            width: 1000px;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .error-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .error-title {
            margin: 0;
            font-size: 1.5rem;
            color: var(--error-color-600);
        }

        .error-status {
            font-size: 1rem;
            color: #6b7280;
        }

        .error-message {
            font-size: 1.25rem;
            color: #374151;
            margin-bottom: 2rem;
        }

        .error-details {
            background-color: #f8fafc;
            border-radius: 0.375rem;
            padding: 1.5rem;
        }

        <?php if (!$isProduction): ?>
        .stack-trace {
            background: #1a1a1a;
            color: #e5e7eb;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            font-family: ui-monospace, monospace;
            font-size: 0.875rem;
            line-height: 1.7;
            margin-top: 1rem;
        }

        .file-info {
            color: #9ca3af;
            margin-bottom: 0.5rem;
        }

        .line-highlight {
            background-color: rgba(239, 68, 68, 0.2);
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
        }
        <?php endif; ?>
    </style>
</head>
<body>
<div class="error-container">
    <div class="error-header">
        <h1 class="error-title"><?= $title ?? 'Error' ?></h1>
        <?php if (isset($statusCode)): ?>
            <span class="error-status"><?= $statusCode ?></span>
        <?php endif; ?>
    </div>

    <div class="error-message">
        <?= $message ?? 'An error occurred.' ?>
    </div>

    <?php if (isset($content)): ?>
        <div class="error-details">
            <?= $content ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>