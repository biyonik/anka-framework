<div class="error-content">
    <div class="error-section rate-limit-details">
        <div class="details-header">
            <h3>Rate Limit Details</h3>
            <?php if (isset($rateLimiter)): ?>
                <span class="rate-type"><?= htmlspecialchars($rateLimiter) ?></span>
            <?php endif; ?>
        </div>

        <div class="rate-info">
            <div class="info-grid">
                <?php if (isset($attempts)): ?>
                    <div class="info-row">
                        <span class="info-label">Attempts:</span>
                        <span class="info-value"><?= $attempts ?> / <?= $maxAttempts ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($retryAfter)): ?>
                    <div class="info-row">
                        <span class="info-label">Retry After:</span>
                        <span class="info-value"><?= $retryAfter ?> seconds</span>
                    </div>
                <?php endif; ?>

                <?php if (isset($decayMinutes)): ?>
                    <div class="info-row">
                        <span class="info-label">Decay Time:</span>
                        <span class="info-value"><?= $decayMinutes ?> minutes</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($requestInfo)): ?>
            <div class="request-info">
                <h4>Request Information</h4>
                <div class="info-grid">
                    <?php foreach ($requestInfo as $key => $value): ?>
                        <div class="info-row">
                            <span class="info-label"><?= htmlspecialchars($key) ?>:</span>
                            <span class="info-value"><?= htmlspecialchars($value) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($limiters)): ?>
        <div class="error-section">
            <h3>Active Rate Limiters</h3>
            <div class="limiters-list">
                <?php foreach ($limiters as $name => $info): ?>
                    <div class="limiter-item">
                        <div class="limiter-header">
                            <span class="limiter-name"><?= htmlspecialchars($name) ?></span>
                            <span class="limiter-status <?= $info['exceeded'] ? 'exceeded' : 'ok' ?>">
                                <?= $info['exceeded'] ? 'Exceeded' : 'OK' ?>
                            </span>
                        </div>
                        <div class="limiter-details">
                            <div>Limit: <?= $info['limit'] ?></div>
                            <div>Current: <?= $info['current'] ?></div>
                            <div>Remaining: <?= $info['remaining'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="error-section debug-tips">
        <h3>Debug Tips</h3>
        <ul class="tips-list">
            <li>Check your request frequency</li>
            <li>Consider implementing caching</li>
            <li>Use bulk operations where possible</li>
            <li>Implement request throttling on the client side</li>
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

    .rate-limit-details {
        background: #fffbeb;
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
        color: #92400e;
    }

    .rate-type {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        background: #fef3c7;
        color: #92400e;
    }

    h4 {
        color: #92400e;
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
        color: #92400e;
        font-weight: 500;
    }

    .info-value {
        color: #78350f;
    }

    .limiters-list {
        display: grid;
        gap: 1rem;
    }

    .limiter-item {
        background: #fef3c7;
        border-radius: 0.375rem;
        padding: 1rem;
    }

    .limiter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .limiter-name {
        font-weight: 500;
        color: #92400e;
    }

    .limiter-status {
        font-size: 0.875rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
    }

    .limiter-status.exceeded {
        background: #fee2e2;
        color: #991b1b;
    }

    .limiter-status.ok {
        background: #dcfce7;
        color: #166534;
    }

    .limiter-details {
        font-size: 0.875rem;
        color: #92400e;
        display: grid;
        gap: 0.25rem;
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