    <div class="error-content">
        <div class="error-section service-details">
            <div class="details-header">
                <h3>Service Status</h3>
                <?php if (isset($maintenanceMode)): ?>
                    <span class="status-badge maintenance">Maintenance Mode</span>
                <?php else: ?>
                    <span class="status-badge unavailable">Service Unavailable</span>
                <?php endif; ?>
            </div>

            <div class="status-info">
                <div class="info-grid">
                    <?php if (isset($startTime)): ?>
                        <div class="info-row">
                            <span class="info-label">Start Time:</span>
                            <span class="info-value"><?= $startTime ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($estimatedDuration)): ?>
                        <div class="info-row">
                            <span class="info-label">Estimated Duration:</span>
                            <span class="info-value"><?= $estimatedDuration ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($retryAfter)): ?>
                        <div class="info-row">
                            <span class="info-label">Retry After:</span>
                            <span class="info-value"><?= $retryAfter ?> seconds</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($services)): ?>
                <div class="services-info">
                    <h4>Service Health Check</h4>
                    <div class="services-list">
                        <?php foreach ($services as $service => $status): ?>
                            <div class="service-item">
                                <span class="service-name"><?= htmlspecialchars($service) ?></span>
                                <span class="service-status <?= $status ? 'up' : 'down' ?>">
                                <?= $status ? 'Operational' : 'Down' ?>
                            </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($lastError)): ?>
            <div class="error-section">
                <h3>Last Known Error</h3>
                <div class="error-details">
                    <pre><code><?= htmlspecialchars($lastError) ?></code></pre>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($maintenanceConfig)): ?>
            <div class="error-section">
                <h3>Maintenance Configuration</h3>
                <div class="maintenance-info">
                    <pre><code><?= htmlspecialchars(print_r($maintenanceConfig, true)) ?></code></pre>
                </div>
            </div>
        <?php endif; ?>

        <div class="error-section system-info">
            <h3>System Information</h3>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Server Time:</span>
                    <span class="info-value"><?= date('Y-m-d H:i:s') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PHP Version:</span>
                    <span class="info-value"><?= PHP_VERSION ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Memory Usage:</span>
                    <span class="info-value"><?= formatBytes(memory_get_usage()) ?></span>
                </div>
            </div>
        </div>

        <div class="error-section debug-tips">
            <h3>Debug Tips</h3>
            <ul class="tips-list">
                <li>Check system resources and server load</li>
                <li>Verify all required services are running</li>
                <li>Check maintenance schedule</li>
                <li>Review error logs for detailed information</li>
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

        .service-details {
            background: #fef2f2;
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
            color: #991b1b;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
        }

        .status-badge.maintenance {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        h4 {
            color: #991b1b;
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
            color: #991b1b;
            font-weight: 500;
        }

        .info-value {
            color: #7f1d1d;
        }

        .services-list {
            display: grid;
            gap: 0.5rem;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #fff1f2;
            border-radius: 0.25rem;
        }

        .service-name {
            color: #7f1d1d;
        }

        .service-status {
            font-size: 0.875rem;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
        }

        .service-status.up {
            background: #dcfce7;
            color: #166534;
        }

        .service-status.down {
            background: #fee2e2;
            color: #991b1b;
        }

        .error-details,
        .maintenance-info {
            background: #1a1a1a;
            color: #e5e7eb;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
        }

        .system-info {
            background: #f8fafc;
            border-radius: 0.375rem;
            padding: 1.5rem;
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

<?php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
}
?>