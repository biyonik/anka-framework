<div class="error-content">
    <div class="error-section">
        <h3>Request Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">URL:</span>
                <span class="info-value"><?= htmlspecialchars($request->getUri()) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Method:</span>
                <span class="info-value"><?= htmlspecialchars($request->getMethod()) ?></span>
            </div>
            <?php if ($referrer = $request->getHeaderLine('referer')): ?>
                <div class="info-row">
                    <span class="info-label">Referrer:</span>
                    <span class="info-value"><?= htmlspecialchars($referrer) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($routes)): ?>
        <div class="error-section">
            <h3>Available Routes</h3>
            <div class="routes-list">
                <?php foreach ($routes as $route): ?>
                    <div class="route-item">
                        <span class="route-method"><?= $route['method'] ?></span>
                        <span class="route-path"><?= $route['path'] ?></span>
                        <span class="route-name"><?= $route['name'] ?? '-' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($suggestedRoutes)): ?>
        <div class="error-section">
            <h3>Did you mean?</h3>
            <ul class="suggestions-list">
                <?php foreach ($suggestedRoutes as $route): ?>
                    <li><?= htmlspecialchars($route) ?></li>
                <?php endforeach; ?>
            </ul>
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

    .error-section:last-child {
        margin-bottom: 0;
    }

    h3 {
        color: #1f2937;
        font-size: 1.25rem;
        margin: 0 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-grid {
        display: grid;
        gap: 0.75rem;
    }

    .info-row {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 1rem;
    }

    .info-label {
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        color: #1f2937;
        word-break: break-all;
    }

    .routes-list {
        background: #f8fafc;
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .route-item {
        display: grid;
        grid-template-columns: 100px 1fr 120px;
        gap: 1rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .route-item:last-child {
        border-bottom: none;
    }

    .route-method {
        color: var(--info-color);
        font-weight: 600;
    }

    .route-path {
        color: #1f2937;
    }

    .route-name {
        color: #6b7280;
        text-align: right;
    }

    .suggestions-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .suggestions-list li {
        padding: 0.5rem 0;
        color: var(--info-color);
        cursor: pointer;
    }

    .suggestions-list li:hover {
        text-decoration: underline;
    }
</style>