<div class="error-content">
    <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <h2>Internal Server Error</h2>
    <p>Sorry, something went wrong on our servers.</p>
    <div class="error-actions">
        <button onclick="window.location.reload()" class="button">Try Again</button>
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
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .button:hover {
        background-color: var(--error-color-600);
    }
</style>