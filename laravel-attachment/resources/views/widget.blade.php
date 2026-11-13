@once
<style>
    .rft-attachment-widget {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background-color: #ffffff;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .rft-attachment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f3f5;
    }
    .rft-attachment-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: #212529;
        margin: 0;
    }
    .rft-attachment-dropzone {
        border: 2px dashed #adb5bd;
        border-radius: 0.5rem;
        padding: 1.5rem 1rem;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        margin-bottom: 1rem;
    }
    .rft-attachment-dropzone:hover,
    .rft-attachment-dropzone.rft-dragover {
        border-color: #0d6efd;
        background-color: #eef4ff;
    }
    .rft-attachment-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .rft-attachment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.6rem 0.85rem;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        background-color: #fff;
        transition: background-color 0.15s ease;
    }
    .rft-attachment-item:hover {
        background-color: #f8f9fa;
    }
    .rft-attachment-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }
    .rft-attachment-thumb {
        width: 42px;
        height: 42px;
        border-radius: 0.25rem;
        object-fit: cover;
        border: 1px solid #dee2e6;
        flex-shrink: 0;
        background-color: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        color: #495057;
        text-transform: uppercase;
    }
    .rft-attachment-meta {
        min-width: 0;
        flex: 1;
    }
    .rft-attachment-name {
        font-weight: 500;
        font-size: 0.9rem;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        text-decoration: none;
    }
    .rft-attachment-name:hover {
        color: #0d6efd;
        text-decoration: underline;
    }
    .rft-attachment-date {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .rft-attachment-actions {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
        margin-left: 0.75rem;
    }
    .rft-attachment-empty {
        text-align: center;
        padding: 1rem;
        color: #6c757d;
        font-size: 0.875rem;
        background: #f8f9fa;
        border-radius: 0.375rem;
    }
    .rft-attachment-progress {
        height: 6px;
        margin-bottom: 0.75rem;
        display: none;
    }
</style>
@endonce

<div id="{{ $widgetId }}"
     class="rft-attachment-widget"
     data-model="{{ $modelClass }}"
     data-model-id="{{ $modelId }}"
     data-upload-url="{{ $uploadUrl }}"
     data-auto-upload="{{ $autoUpload ? '1' : '0' }}"
     data-multiple="{{ $multiple ? '1' : '0' }}"
     data-max-size="{{ $maxFileSizeKb }}"
     data-csrf="{{ csrf_token() }}">

    <div class="rft-attachment-header">
        <h6 class="rft-attachment-title">
            {{ $label }}
            <span class="badge bg-secondary ms-1 rft-attachment-count">{{ $attachments->count() }}</span>
        </h6>
        @if(!$readonly)
            <button type="button" class="btn btn-sm btn-outline-primary rft-browse-btn">
                + Browse Files
            </button>
        @endif
    </div>

    <div class="alert alert-danger py-2 px-3 mb-2 d-none rft-attachment-error" role="alert" style="font-size: 0.85rem;"></div>

    @if(!$readonly)
        <div class="rft-attachment-dropzone">
            <input type="file"
                   name="{{ $autoUpload ? '' : $inputName }}"
                   class="d-none rft-file-input"
                   accept="{{ $accept }}"
                   {{ $multiple ? 'multiple' : '' }}>
            <div class="fw-semibold text-secondary mb-1">
                Drag &amp; drop files here or click to select
            </div>
            <div class="small text-muted">
                Max file size: {{ number_format($maxFileSizeKb / 1024, 1) }} MB
                @if(!$autoUpload)
                    &bull; Files will be uploaded when you save the form
                @endif
            </div>
        </div>

        <div class="progress rft-attachment-progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
    @endif

    <div class="rft-attachment-list">
        @forelse($attachments as $attachment)
            <div class="rft-attachment-item" data-file-url="{{ $attachment->file_url }}">
                <div class="rft-attachment-info">
                    @if($attachment->is_image)
                        <img src="{{ $attachment->view_url }}" alt="{{ $attachment->file_name }}" class="rft-attachment-thumb">
                    @else
                        <div class="rft-attachment-thumb">
                            {{ $attachment->extension ?: 'FILE' }}
                        </div>
                    @endif
                    <div class="rft-attachment-meta">
                        <a href="{{ $attachment->view_url }}" target="_blank" rel="noopener" class="rft-attachment-name" title="{{ $attachment->file_name }}">
                            {{ $attachment->file_name }}
                        </a>
                        <div class="rft-attachment-date">
                            Uploaded {{ $attachment->created_at?->format('Y-m-d H:i:s') }}
                        </div>
                    </div>
                </div>
                <div class="rft-attachment-actions">
                    <a href="{{ $attachment->view_url }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-sm btn-outline-secondary"
                       title="View file">
                        View
                    </a>
                    <a href="{{ $attachment->download_url }}"
                       class="btn btn-sm btn-outline-primary"
                       title="Download file">
                        Download
                    </a>
                    @if(!$readonly)
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rft-delete-btn"
                                data-delete-url="{{ $attachment->delete_url }}"
                                data-file-url="{{ $attachment->file_url }}"
                                title="Delete attachment">
                            Delete
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rft-attachment-empty">
                No file attachments found.
            </div>
        @endforelse
    </div>
</div>

@once
<script>
(function () {
    function initRftAttachmentWidget(container) {
        if (!container || container.dataset.rftInitialized === '1') {
            return;
        }
        container.dataset.rftInitialized = '1';

        const fileInput = container.querySelector('.rft-file-input');
        const browseBtn = container.querySelector('.rft-browse-btn');
        const dropzone = container.querySelector('.rft-attachment-dropzone');
        const listEl = container.querySelector('.rft-attachment-list');
        const countBadge = container.querySelector('.rft-attachment-count');
        const errorBox = container.querySelector('.rft-attachment-error');
        const progressWrap = container.querySelector('.rft-attachment-progress');
        const progressBar = progressWrap ? progressWrap.querySelector('.progress-bar') : null;

        const modelClass = container.dataset.model || '';
        const modelId = container.dataset.modelId || '';
        const uploadUrl = container.dataset.uploadUrl || '';
        const autoUpload = container.dataset.autoUpload === '1';
        const maxSizeKb = parseInt(container.dataset.maxSize || '10240', 10);
        const csrfToken = container.dataset.csrf || (document.querySelector('meta[name="csrf-token"]')?.content ?? '');

        let stagedFiles = new DataTransfer();

        function showError(msg) {
            if (!errorBox) return;
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        }

        function clearError() {
            if (!errorBox) return;
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
        }

        function updateCount() {
            const items = listEl.querySelectorAll('.rft-attachment-item');
            if (countBadge) {
                countBadge.textContent = items.length;
            }
            const emptyEl = listEl.querySelector('.rft-attachment-empty');
            if (items.length === 0) {
                if (!emptyEl) {
                    const div = document.createElement('div');
                    div.className = 'rft-attachment-empty';
                    div.textContent = 'No file attachments found.';
                    listEl.appendChild(div);
                }
            } else if (emptyEl) {
                emptyEl.remove();
            }
        }

        function escapeHtml(str) {
            const p = document.createElement('p');
            p.textContent = str;
            return p.innerHTML;
        }

        function renderAttachmentItem(item) {
            const div = document.createElement('div');
            div.className = 'rft-attachment-item';
            div.dataset.fileUrl = item.file_url;

            const thumbHtml = item.is_image
                ? `<img src="${escapeHtml(item.view_url)}" alt="${escapeHtml(item.file_name)}" class="rft-attachment-thumb">`
                : `<div class="rft-attachment-thumb">${escapeHtml(item.extension || 'FILE')}</div>`;

            div.innerHTML = `
                <div class="rft-attachment-info">
                    ${thumbHtml}
                    <div class="rft-attachment-meta">
                        <a href="${escapeHtml(item.view_url)}" target="_blank" rel="noopener" class="rft-attachment-name" title="${escapeHtml(item.file_name)}">
                            ${escapeHtml(item.file_name)}
                        </a>
                        <div class="rft-attachment-date">
                            Uploaded ${escapeHtml(item.created_at || 'just now')}
                        </div>
                    </div>
                </div>
                <div class="rft-attachment-actions">
                    <a href="${escapeHtml(item.view_url)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="View file">View</a>
                    <a href="${escapeHtml(item.download_url)}" class="btn btn-sm btn-outline-primary" title="Download file">Download</a>
                    <button type="button" class="btn btn-sm btn-outline-danger rft-delete-btn" data-delete-url="${escapeHtml(item.delete_url)}" data-file-url="${escapeHtml(item.file_url)}" title="Delete attachment">Delete</button>
                </div>
            `;
            return div;
        }

        function renderStagedItem(file, index) {
            const ext = (file.name.split('.').pop() || 'FILE').toLowerCase();
            const div = document.createElement('div');
            div.className = 'rft-attachment-item rft-staged-item';
            div.dataset.stagedIndex = index;

            div.innerHTML = `
                <div class="rft-attachment-info">
                    <div class="rft-attachment-thumb">${escapeHtml(ext)}</div>
                    <div class="rft-attachment-meta">
                        <span class="rft-attachment-name">${escapeHtml(file.name)}</span>
                        <div class="rft-attachment-date text-primary">Ready to upload with form (${(file.size / 1024).toFixed(1)} KB)</div>
                    </div>
                </div>
                <div class="rft-attachment-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger rft-remove-staged-btn" data-index="${index}">Remove</button>
                </div>
            `;
            return div;
        }

        function refreshStagedView() {
            listEl.querySelectorAll('.rft-staged-item').forEach(el => el.remove());
            Array.from(stagedFiles.files).forEach((file, idx) => {
                listEl.appendChild(renderStagedItem(file, idx));
            });
            updateCount();
        }

        function handleFiles(files) {
            clearError();
            if (!files || files.length === 0) return;

            for (const file of files) {
                if (file.size > maxSizeKb * 1024) {
                    showError(`File "${file.name}" exceeds maximum allowed size of ${(maxSizeKb / 1024).toFixed(1)} MB.`);
                    return;
                }
            }

            if (!autoUpload) {
                for (const file of files) {
                    stagedFiles.items.add(file);
                }
                fileInput.files = stagedFiles.files;
                refreshStagedView();
                return;
            }

            const formData = new FormData();
            formData.append('model', modelClass);
            if (modelId !== '') {
                formData.append('model_id', modelId);
            }
            for (const file of files) {
                formData.append('files[]', file);
            }

            if (progressWrap && progressBar) {
                progressWrap.style.display = 'flex';
                progressBar.style.width = '40%';
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable && progressBar) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                }
            };

            xhr.onload = function () {
                if (progressWrap && progressBar) {
                    progressWrap.style.display = 'none';
                    progressBar.style.width = '0%';
                }
                fileInput.value = '';

                let response = {};
                try {
                    response = JSON.parse(xhr.responseText);
                } catch (err) {
                    showError('Unexpected server response during file upload.');
                    return;
                }

                if (xhr.status >= 200 && xhr.status < 300 && response.data) {
                    const emptyEl = listEl.querySelector('.rft-attachment-empty');
                    if (emptyEl) emptyEl.remove();

                    response.data.forEach(item => {
                        listEl.prepend(renderAttachmentItem(item));
                    });
                    updateCount();
                } else {
                    const errMsg = response.message || 'Failed to upload file(s).';
                    showError(errMsg);
                }
            };

            xhr.onerror = function () {
                if (progressWrap) progressWrap.style.display = 'none';
                showError('Network error while uploading file(s).');
            };

            xhr.send(formData);
        }

        if (browseBtn && fileInput) {
            browseBtn.addEventListener('click', () => fileInput.click());
        }

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', (e) => {
                if (e.target !== fileInput) {
                    fileInput.click();
                }
            });

            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('rft-dragover');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('rft-dragover');
            });

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('rft-dragover');
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    handleFiles(e.dataTransfer.files);
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    handleFiles(fileInput.files);
                }
            });
        }

        listEl.addEventListener('click', function (e) {
            const removeStagedBtn = e.target.closest('.rft-remove-staged-btn');
            if (removeStagedBtn) {
                const rmIdx = parseInt(removeStagedBtn.dataset.index, 10);
                const nextDt = new DataTransfer();
                Array.from(stagedFiles.files).forEach((f, idx) => {
                    if (idx !== rmIdx) nextDt.items.add(f);
                });
                stagedFiles = nextDt;
                if (fileInput) fileInput.files = stagedFiles.files;
                refreshStagedView();
                return;
            }

            const deleteBtn = e.target.closest('.rft-delete-btn');
            if (!deleteBtn) return;

            const deleteUrl = deleteBtn.dataset.deleteUrl;
            if (!deleteUrl) return;

            if (!window.confirm('Are you sure you want to permanently delete this attachment?')) {
                return;
            }

            clearError();
            deleteBtn.disabled = true;

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Failed to delete attachment.');
                }
                const row = deleteBtn.closest('.rft-attachment-item');
                if (row) row.remove();
                updateCount();
            })
            .catch((err) => {
                deleteBtn.disabled = false;
                showError(err.message || 'Error deleting attachment.');
            });
        });
    }

    function initAllRftWidgets() {
        document.querySelectorAll('.rft-attachment-widget').forEach(initRftAttachmentWidget);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllRftWidgets);
    } else {
        initAllRftWidgets();
    }
})();
</script>
@endonce
