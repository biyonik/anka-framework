<div class="error-content">
    <div class="error-section authorization-details">
        <div class="details-header">
            <h3>Authorization Details</h3>
            <?php if (isset($isAuthenticated)): ?>
                <span class="auth-status <?= $isAuthenticated ? 'authenticated' : 'unauthenticated' ?>">
                    <?= $isAuthenticated ? 'Authenticated' : 'Unauthenticated' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (isset($user)): ?>
            <div class="user-info">
                <h4>User Information</h4>
                <div class="info-grid">
                    <?php foreach ($user as $key => $value): ?>
                        <div class="info-row">
                            <span class="info-label"><?= htmlspecialchars(ucfirst($key)) ?>:</span>
                            <span class="info-value"><?= htmlspecialchars($value) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($requiredPermissions)): ?>
            <div class="permissions-info">
                <h4>Required Permissions</h4>
                <ul class="permissions-list">
                    <?php foreach ($requiredPermissions as $permission): ?>
                        <li class="permission-item">
                            <span class="permission-name"><?= htmlspecialchars($permission) ?></span>
                            <?php if (isset($userPermissions)): ?>
                                <span class="permission-status <?= in_array($permission, $userPermissions) ? 'granted' : 'denied' ?>">
                                    <?= in_array($permission, $userPermissions) ? '✓' : '✕' ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($route)): ?>
        <div class="error-section">
            <h3>Route Information</h3>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Path:</span>
                    <span class="info-value"><?= htmlspecialchars($route['path']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Method:</span>
                    <span class="info-value"><?= htmlspecialchars($route['method']) ?></span>
                </div>
                <?php if (!empty($route['middleware'])): ?>
                    <div class="info-row">
                        <span class="info-label">Middleware:</span>
                        <span class="info-value"><?= htmlspecialchars(implode(', ', $route['middleware'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($stackTrace)): ?>
        <div class="error-section">
            <h3>Stack Trace</h3>
            <pre class="stack-trace"><code><?= htmlspecialchars($stackTrace) ?></code></pre>
        </div>
    <?php endif; ?>
</div>

<style>
    .error-content {
        font-family: ui-monospace, monospace;
    }

    .error-section {
        margin-bottom: 2rem;
    }

    .authorization-details {
        background: #fff7ed;
        border-radius: 0.375rem;
        padding: 1.5rem;
    }

    .details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .details-header h3 {
        margin: 0;
        color: #9a3412;
    }

    .auth-status {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
    }

    .authenticated {
        background: #dcfce7;
        color: #166534;
    }

    .unauthenticated {
        background: #fee2e2;
        color: #991b1b;
    }

    h4 {
        color: #9a3412;
        margin: 1rem 0;
        font-size: 1rem;
    }

    .info-grid {
        display: grid;
        gap: 0.75rem;
    }

    .info-row {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 1rem;
    }

    .info-label {
        color: #9a3412;
        font-weight: 500;
    }

    .info-value {
        color: #431407;
    }

    .permissions-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0.5rem;
    }

    .permission-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        background: #ffedd5;
        border-radius: 0.25rem;
    }

    .permission-name {
        color: #431407;
    }

    .permission-status {
        font-weight: bold;
    }

    .permission-status.granted {
        color: #166534;
    }

    .permission-status.denied {
        color: #991b1b;
    }

    .stack-trace {
        background: #1a1a1a;
        color: #e5e7eb;
        padding: 1rem;
        border-radius: 0.375rem;
        margin: 0;
        overflow-x: auto;
    }
</style>