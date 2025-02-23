<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M16 16s-1.5-2-4-2-4 2-4 2"/>
            <line x1="9" y1="9" x2="9.01" y2="9"/>
            <line x1="15" y1="9" x2="15.01" y2="9"/>
        </svg>
    </div>
    <h2>Page Not Found</h2>
    <p>The page you are looking for could not be found.</p>
    <div class="error-actions">
        <a href="/" class="button">Return Home</a>
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
        margin-bottom: 2rem;
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
    }

    .button:hover {
        background-color: var(--error-color-600);
    }
</style>