<div class="error-content">
    <div class="error-section authentication-details">
        <div class="details-header">
            <h3>Authentication Details</h3>
            <?php if (isset($authType)): ?>
                <span class="auth-type"><?= htmlspecialchars($authType) ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($headers)): ?>
            <div class="headers-info">
                <h4>Authentication Headers</h4>
                <div class="info-grid">
                    <?php foreach ($headers as $key => $value): ?>
                        <div class="info-row">
                            <span class="info-label"><?= htmlspecialchars($key) ?>:</span>
                            <span class="info-value"><?= htmlspecialchars($value) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($guards)): ?>
            <div class="guards-info">
                <h4>Authentication Guards</h4>
                <ul class="guards-list">
                    <?php foreach ($guards as $guard => $status): ?>
                        <li class="guard-item">
                            <span class="guard-name"><?= htmlspecialchars($guard) ?></span>
                            <span class="guard-status <?= $status ? 'active' : 'inactive' ?>">
                                <?= $status ? 'Active' : 'Inactive' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($sessionData)): ?>
            <div class="session-info">
                <h4>Session Information</h4>
                <div class="info-grid">
                    <?php foreach ($sessionData as $key => $value): ?>
                        <div class="info-row">
                            <span class="info-label"><?= htmlspecialchars($key) ?>:</span>
                            <span class="info-value"><?= htmlspecialchars(print_r($value, true)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($route)): ?>
        <div class="error-section">
            <h3>Protected Route Information</h3>
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
                        <span class="info-label">Auth Middleware:</span>
                        <span class="info-value"><?= htmlspecialchars(implode(', ', $route['middleware'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="error-section debug-tips">
        <h3>Debug Tips</h3>
        <ul class="tips-list">
            <li>Check if the authentication token is present and valid</li>
            <li>Verify that the correct authentication guard is being used</li>
            <li>Ensure session is configured correctly</li>
            <li>Check for CORS issues if making API requests</li>
        </ul>
    </div>
</div>

<style>
    .error-content {
        font-family: ui-monospace, monospace;
    }

    .error-section {
        margin-bottom: 2rem;
    }

    .authentication-details {
        background: #eff6ff;
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
        color: #1e40af;
    }

    .auth-type {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        background: #dbeafe;
        color: #1e40af;
    }

    h4 {
        color: #1e40af;
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
        color: #1e40af;
        font-weight: 500;
    }

    .info-value {
        color: #1e3a8a;
        word-break: break-all;
    }

    .guards-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0.5rem;
    }

    .guard-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        background: #dbeafe;
        border-radius: 0.25rem;
    }

    .guard-name {
        color: #1e3a8a;
    }

    .guard-status {
        font-size: 0.875rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
    }

    .guard-status.active {
        background: #dcfce7;
        color: #166534;
    }

    .guard-status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .debug-tips {
        background: #f8fafc;
        border-radius: 0.375rem;
        padding: 1.5rem;
    }

    .tips-list {
        margin: 0;
        padding-left: 1.5rem;
        color: #475569;
    }

    .tips-list li {
        margin-bottom: 0.5rem;
    }

    .tips-list li:last-child {
        margin-bottom: 0;
    }
</style>