<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            <circle cx="12" cy="16" r="1"/>
        </svg>
    </div>
    <h2>Access Denied</h2>
    <p>You don't have permission to access this page.</p>
    <div class="error-actions">
        <a href="/" class="button">Return Home</a>
        <?php if (!isset($isAuthenticated) || !$isAuthenticated): ?>
            <a href="/login" class="button button-outline">Sign In</a>
        <?php endif; ?>
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
        margin-bottom: 2rem;
    }

    .error-actions {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: all 0.2s;
        font-size: 1rem;
    }

    .button {
        background-color: var(--warning-color);
        color: white;
        border: 2px solid var(--warning-color);
    }

    .button:hover {
        background-color: #d97706;
        border-color: #d97706;
    }

    .button-outline {
        background-color: transparent;
        color: var(--warning-color);
    }

    .button-outline:hover {
        background-color: var(--warning-color);
        color: white;
    }
</style>