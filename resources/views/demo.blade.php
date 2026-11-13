<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>rft/laravel-attachment — Test & Playground</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            color: #212529;
        }
        .code-hash {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.78rem;
            background: #f1f3f5;
            padding: 0.15rem 0.4rem;
            border-radius: 0.25rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <span class="badge bg-primary mb-1">Laravel 12.x Package</span>
            <h3 class="fw-bold mb-0">rft/laravel-attachment — Live Test Page</h3>
            <p class="text-muted small mb-0">
                Single source of truth transactional attachment system (Yii2-style Attachment Widget)
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="{{ route('rft.attachments.demo', ['model' => $sampleModel, 'model_id' => $sampleModelId]) }}" class="btn btn-sm btn-outline-secondary">
                Refresh Page / DB Table
            </a>
        </div>
    </div>

    <!-- System Configuration Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold">Database Table</div>
                    <div class="fs-6 fw-bold text-dark mt-1"><code>{{ $tableName }}</code></div>
                    <span class="badge bg-success-subtle text-success mt-1">Insert &amp; Delete Only</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold">Private Storage Location</div>
                    <div class="small fw-semibold text-truncate mt-1" title="{{ $storageRoot }}">
                        <code>storage/app/private</code> (Disk: <code>{{ $diskName }}</code>)
                    </div>
                    <span class="badge bg-info-subtle text-info-emphasis mt-1">Hashed file_url Storage</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold">Active Test Target</div>
                    <div class="small fw-bold text-dark mt-1"><code>{{ $sampleModel }}</code></div>
                    <div class="small text-muted">Record ID: <strong>#{{ $sampleModelId }}</strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small text-uppercase fw-semibold">Total DB Rows</div>
                    <div class="fs-4 fw-bold text-primary mt-1">{{ $allRecords->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Model Switcher -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('rft.attachments.demo') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold mb-1">Test Model Class (`model` column)</label>
                    <input type="text" name="model" class="form-control form-control-sm" value="{{ $sampleModel }}" placeholder="App\Models\Senior">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Test Model ID (`model_id` column)</label>
                    <input type="number" name="model_id" class="form-control form-control-sm" value="{{ $sampleModelId }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">Switch Target Model</button>
                    <a href="{{ route('rft.attachments.demo') }}" class="btn btn-sm btn-light border">Reset Default</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Widgets Comparison Row -->
    <div class="row g-4 mb-4">
        <!-- Interactive Upload & Delete Widget -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">1. Interactive Yii2-Style Attachment Widget (Upload, View &amp; Delete)</h6>
                    <div class="small text-muted">
                        Blade: <code>&lt;x-rft-attachment model="{{ $sampleModel }}" :model-id="{{ $sampleModelId }}" /&gt;</code>
                    </div>
                </div>
                <div class="card-body">
                    <x-rft-attachment
                        :model="$sampleModel"
                        :model-id="$sampleModelId"
                        label="Attachments for {{ class_basename($sampleModel) }} #{{ $sampleModelId }}"
                    />
                </div>
            </div>
        </div>

        <!-- Read-Only Widget Mode -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">2. Read-Only Mode Preview</h6>
                        <div class="small text-muted">
                            Blade: <code>&lt;x-rft-attachment ... :readonly="true" /&gt;</code>
                        </div>
                    </div>
                    <span class="badge bg-secondary">Read-Only</span>
                </div>
                <div class="card-body">
                    <x-rft-attachment
                        :model="$sampleModel"
                        :model-id="$sampleModelId"
                        :readonly="true"
                        label="Read-Only View ({{ class_basename($sampleModel) }} #{{ $sampleModelId }})"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Immutability Alert Feedback -->
    <div id="immutability-feedback" class="alert d-none mb-4" role="alert"></div>

    <!-- Live Database Table Inspector -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">3. Live Database Table Inspector (<code>{{ $tableName }}</code>)</h6>
                <div class="small text-muted">
                    Shows all rows stored in the database and verifies whether the physical hashed file exists in <code>storage/app/private</code>.
                </div>
            </div>
            <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-outline-primary">
                Reload Table Rows
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th>id</th>
                        <th>model</th>
                        <th>model_id</th>
                        <th>file_name</th>
                        <th>file_url (Hashed Access Point)</th>
                        <th>user_id</th>
                        <th>created_at</th>
                        <th>app/private</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($allRecords as $row)
                        <tr>
                            <td class="fw-bold">{{ $row->id }}</td>
                            <td><code>{{ $row->model }}</code></td>
                            <td>{{ $row->model_id ?? 'NULL' }}</td>
                            <td class="fw-medium">{{ $row->file_name }}</td>
                            <td>
                                <span class="code-hash" title="{{ $row->file_url }}">
                                    {{ \Illuminate\Support\Str::limit($row->file_url, 22) }}
                                </span>
                            </td>
                            <td>{{ $row->user_id ?? 'NULL' }}</td>
                            <td>{{ $row->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if($row->fileExists())
                                    <span class="badge bg-success-subtle text-success">Stored</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Missing</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $row->view_url }}" target="_blank" class="btn btn-outline-secondary" title="View via hashed file_url">
                                        View
                                    </a>
                                    <a href="{{ $row->download_url }}" class="btn btn-outline-primary" title="Download via hashed file_url">
                                        Download
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-warning text-dark test-update-btn"
                                            data-test-url="{{ route('rft.attachments.test-update', ['file_url' => $row->file_url]) }}"
                                            title="Verify that UPDATE is strictly blocked">
                                        Test Update Block
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No rows in <code>{{ $tableName }}</code> yet. Upload a file in the widget above to test!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.test-update-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const url = this.dataset.testUrl;
        const feedback = document.getElementById('immutability-feedback');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            feedback.classList.remove('d-none', 'alert-danger', 'alert-success');
            if (data.status === 'blocked') {
                feedback.classList.add('alert-success');
                feedback.innerHTML = `<strong>Transactional Immutability Verified:</strong> Attempting to call <code>$attachment->update()</code> was blocked as expected:<br><code>${data.message}</code>`;
            } else {
                feedback.classList.add('alert-danger');
                feedback.textContent = data.message || 'Unexpected response.';
            }
        });
    });
});
</script>
</body>
</html>
