<div class="error-content">
    <div class="error-section exception-details">
        <div class="exception-type">
            <span class="label">Exception:</span>
            <span class="value"><?= htmlspecialchars(get_class($exception)) ?></span>
        </div>

        <div class="exception-message">
            <?= htmlspecialchars($exception->getMessage()) ?>
        </div>
    </div>

    <div class="error-section">
        <h3>Stack Trace</h3>
        <div class="stack-frames">
            <?php foreach ($exception->getTrace() as $i => $frame): ?>
                <div class="stack-frame">
                    <div class="frame-header">
                        <?php if (isset($frame['class'])): ?>
                            <span class="frame-class"><?= htmlspecialchars($frame['class']) ?></span>
                            <span class="frame-separator"><?= $frame['type'] ?></span>
                        <?php endif; ?>
                        <span class="frame-function"><?= htmlspecialchars($frame['function']) ?></span>
                    </div>

                    <?php if (isset($frame['file'])): ?>
                        <div class="frame-file">
                            <span class="file-path"><?= htmlspecialchars($frame['file']) ?></span>
                            <span class="file-line">:<?= $frame['line'] ?></span>
                        </div>

                        <?php if (isset($frame['snippet'])): ?>
                            <pre class="code-snippet"><code><?= $frame['snippet'] ?></code></pre>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($frame['args'])): ?>
                        <div class="frame-args">
                            <div class="args-header">Arguments:</div>
                            <pre class="args-content"><?= htmlspecialchars(print_r($frame['args'], true)) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($exception->getPrevious()): ?>
        <div class="error-section">
            <h3>Previous Exception</h3>
            <div class="previous-exception">
                <div class="exception-type">
                    <span class="label">Type:</span>
                    <span class="value"><?= htmlspecialchars(get_class($exception->getPrevious())) ?></span>
                </div>
                <div class="exception-message">
                    <?= htmlspecialchars($exception->getPrevious()->getMessage()) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($server)): ?>
        <div class="error-section">
            <h3>Server Information</h3>
            <div class="server-info">
                <?php foreach ($server as $key => $value): ?>
                    <div class="info-row">
                        <span class="info-key"><?= htmlspecialchars($key) ?></span>
                        <span class="info-value"><?= htmlspecialchars(print_r($value, true)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
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

    .exception-details {
        background: #fee2e2;
        border-radius: 0.375rem;
        padding: 1.5rem;
    }

    .exception-type {
        margin-bottom: 1rem;
    }

    .exception-type .label {
        color: #991b1b;
        font-weight: 600;
    }

    .exception-type .value {
        color: #dc2626;
        margin-left: 0.5rem;
    }

    .exception-message {
        color: #991b1b;
        font-size: 1.125rem;
        font-weight: 500;
    }

    .stack-frames {
        background: #1a1a1a;
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .stack-frame {
        padding: 1rem;
        border-bottom: 1px solid #374151;
    }

    .frame-header {
        color: #e5e7eb;
        margin-bottom: 0.5rem;
    }

    .frame-class {
        color: #93c5fd;
    }

    .frame-separator {
        color: #6b7280;
        margin: 0 0.25rem;
    }

    .frame-function {
        color: #fde68a;
    }

    .frame-file {
        color: #9ca3af;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .file-line {
        color: #f87171;
    }

    .code-snippet {
        background: #111827;
        padding: 1rem;
        border-radius: 0.25rem;
        margin: 0.5rem 0;
        overflow-x: auto;
    }

    .frame-args {
        margin-top: 0.5rem;
    }

    .args-header {
        color: #9ca3af;
        margin-bottom: 0.25rem;
    }

    .args-content {
        color: #d1d5db;
        background: #111827;
        padding: 0.5rem;
        border-radius: 0.25rem;
        margin: 0;
        overflow-x: auto;
    }

    .server-info {
        background: #f8fafc;
        border-radius: 0.375rem;
        padding: 1rem;
    }

    .info-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 1rem;
        padding: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-key {
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        color: #1f2937;
        word-break: break-all;
    }
</style>