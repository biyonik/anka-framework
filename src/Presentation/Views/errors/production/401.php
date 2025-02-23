<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="M8 11h8"/>
            <path d="M12 15V7"/>
        </svg>
    </div>
    <h2>Authentication Required</h2>
    <p>You need to sign in to access this page.</p>
    <div class="error-actions">
        <a href="/login" class="button">Sign In</a>
        <a href="/" class="button button-outline">Return Home</a>
    </div>
</div>

<style>
    .error-content {
        text-align: center;
    }

    .error-icon {
        color: var(--info-color);
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
        background-color: var(--info-color);
        color: white;
        border: 2px solid var(--info-color);
    }

    .button:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .button-outline {
        background-color: transparent;
        color: var(--info-color);
    }

    .button-outline:hover {
        background-color: var(--info-color);
        color: white;
    }
</style>