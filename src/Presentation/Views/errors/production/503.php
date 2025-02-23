<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
            <polyline points="13 2 13 9 20 9"/>
            <line x1="12" y1="17" x2="12" y2="17.01"/>
            <line x1="12" y1="13" x2="12" y2="15"/>
        </svg>
    </div>
    <h2>Service Unavailable</h2>
    <p>The service is temporarily unavailable. We're working on it.</p>
    <?php if (isset($retryAfter)): ?>
        <div class="retry-info">
            Please try again in <span class="retry-time"><?= $retryAfter ?></span> seconds
        </div>
    <?php endif; ?>
    <div class="error-actions">
        <button onclick="window.location.reload()" class="button">Refresh Page</button>
    </div>
</div>

<style>
    .error-content {
        text-align: center;
    }

    .error-icon {
        color: var(--error-color-500);
        margin-bottom: 1.5rem;
    }

    .error-icon svg {
        width: 64px;
        height: 64px;
    }

    h2 {
        color: #1f2937;
        font-size: 1.875rem;
        margin: 0 0 1rem 0;
    }

    p {
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .retry-info {
        color: #6b7280;
        margin-bottom: 2rem;
    }

    .retry-time {
        color: var(--error-color-500);
        font-weight: bold;
    }

    .error-actions {
        margin-top: 2rem;
    }

    .button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: var(--error-color-500);
        color: white;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: background-color 0.2s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .button:hover {
        background-color: var(--error-color-600);
    }
</style>