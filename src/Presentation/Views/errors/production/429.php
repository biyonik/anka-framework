<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="6" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12" y2="18"/>
        </svg>
    </div>
    <h2>Too Many Requests</h2>
    <p>Please slow down. You are making too many requests.</p>
    <?php if (isset($retryAfter)): ?>
        <div class="retry-info">
            Try again in <span class="retry-time"><?= $retryAfter ?></span> seconds
        </div>
    <?php endif; ?>
    <div class="error-actions">
        <button onclick="window.location.reload()" class="button">Try Again</button>
    </div>
</div>

<style>
    .error-content {
        text-align: center;
    }

    .error-icon {
        color: var(--warning-color);
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
        color: var(--warning-color);
        font-weight: bold;
    }

    .error-actions {
        margin-top: 2rem;
    }

    .button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: var(--warning-color);
        color: white;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: background-color 0.2s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .button:hover {
        background-color: #d97706;
    }
</style>