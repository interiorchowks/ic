@extends('layouts.back-end.app-seller')

@section('title', \App\CPU\translate('Product Bulk Import'))

@push('css_or_js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')

<div class="d-flex justify-content-end">
    <button type="button" class="btn btn--primary">
        <a style="color:#fff" href="{{ route('seller.product.bulk-images-url-export') }}">
            {{ \App\CPU\translate('Export all images URL') }}
        </a>
    </button>
</div>

<h2 style="padding:20px 50px;">Bulk Image Upload (Excel URLs → R2 → DB)</h2>

<form id="bulkUploadForm" style="padding:0px 50px;" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="seller_id" value="{{ auth('seller')->id() }}" required />

    <div style="margin-bottom: 10px;">
        <label><b>Excel File (.xlsx)</b></label><br>
        <input type="file" name="file" accept=".xlsx" required class="form-control"/>
    </div>

    <button type="submit" class="btn btn--primary">Upload & Import</button>
</form>

{{-- Progress Bar --}}
<div style="padding:0px 50px; margin-top:15px;">
    <div style="background:#eee; border-radius:8px; overflow:hidden; height:18px;">
        <div id="progressBar" style="height:18px; width:0%; background:#4caf50;"></div>
    </div>
    <div id="progressText" style="margin-top:8px;">0%</div>
</div>

<hr>



<script>
(function () {
    const form = document.getElementById('bulkUploadForm');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const submitBtn = form.querySelector('button[type="submit"]');

    let uploadPct = 0;
    let processPct = 0;
    let polling = false;

    function clamp(n){ return Math.max(0, Math.min(100, n)); }

    function renderProgress(stageText) {
        const total = Math.round((clamp(uploadPct) * 0.5) + (clamp(processPct) * 0.5));
        progressBar.style.width = total + '%';
        progressText.innerText = total + '% ' + (stageText ? '- ' + stageText : '');
    }

    function progressUrl(jobId) {
        const tpl = @json(route('seller.product.bulk-images-progress', ['jobId' => '___JOB___']));
        return tpl.replace('___JOB___', jobId);
    }

    function setBusy(state, text){
        submitBtn.disabled = state;
        submitBtn.innerText = text;
    }

    const sleep = (ms)=> new Promise(r=>setTimeout(r, ms));

    async function pollProgress(jobId) {
        polling = true;
        let delay = 1200;
        let lastHeartbeat = null;
        let staleCount = 0;

        while (polling) {
            try {
                const resp = await fetch(progressUrl(jobId), { cache: 'no-store' });

                if (resp.status === 401) {
                    polling = false;
                    renderProgress('Session expired');
                    setBusy(false, 'Upload & Import');
                    alert('Session expired. Login again.');
                    location.reload();
                    return;
                }

                if (!resp.ok) {
                    renderProgress('Reconnecting...');
                    delay = Math.min(5000, Math.floor(delay * 1.5));
                    await sleep(delay);
                    continue;
                }

                const data = await resp.json();
                processPct = data.percent ?? 0;

                // ✅ heartbeat stale detect (if backend stuck)
                if (data.updated_at) {
                    if (lastHeartbeat === data.updated_at) staleCount++;
                    else staleCount = 0;
                    lastHeartbeat = data.updated_at;
                }

                if (staleCount >= 15) { // ~18-20 sec with 1200ms
                    renderProgress('Still working... (slow)');
                } else {
                    renderProgress(data.message || (data.status === 'processing' ? 'Processing...' : ''));
                }

                if (data.status === 'done') {
                    polling = false;
                    uploadPct = 100;
                    processPct = 100;
                    renderProgress('Completed');
                    setBusy(false, 'Upload & Import');
                    setTimeout(() => location.reload(), 1200);
                    return;
                }

                // ✅ only REAL failed stops
                if (data.status === 'failed') {
                    polling = false;
                    renderProgress('Failed');
                    setBusy(false, 'Upload & Import');
                    alert(data.message || 'Job failed');
                    return;
                }

                delay = 1200;
                await sleep(delay);

            } catch (e) {
                renderProgress('Reconnecting...');
                delay = Math.min(5000, Math.floor(delay * 1.5));
                await sleep(delay);
            }
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        uploadPct = 0;
        processPct = 0;
        renderProgress('Uploading...');
        setBusy(true, 'Uploading...');

        const fd = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', @json(route('seller.product.bulk-images-url-upload-ajax')), true);
        xhr.setRequestHeader('X-CSRF-TOKEN', @json(csrf_token()));

        xhr.upload.onprogress = function (evt) {
            if (evt.lengthComputable) {
                uploadPct = Math.round((evt.loaded / evt.total) * 100);
                renderProgress('Uploading...');
            }
        };

        xhr.onload = function () {
            if (xhr.status !== 200 && xhr.status !== 202) {
                renderProgress('Upload Failed');
                setBusy(false, 'Upload & Import');
                alert('Upload failed');
                return;
            }

            uploadPct = 100;
            renderProgress('Queued...');
            setBusy(true, 'Processing...');

            const res = JSON.parse(xhr.responseText);
            pollProgress(res.job_id);
        };

        xhr.onerror = function () {
            renderProgress('Upload Error');
            setBusy(false, 'Upload & Import');
        };

        xhr.send(fd);
    });
})();
</script>


@endsection
