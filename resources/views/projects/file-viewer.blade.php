<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $file->original_name }} — EPAKSI GIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }

        /* ── Topbar ───────────────────────────────────── */
        #topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 52px;
            background: #1a3c5e;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            gap: .75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }

        .file-title {
            color: #fff;
            font-weight: 600;
            font-size: .9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 400px;
        }

        .file-meta {
            color: #90b4d4;
            font-size: .72rem;
            white-space: nowrap;
        }

        /* ── PDF Container ────────────────────────────── */
        #pdfContainer {
            position: absolute;
            top: 52px;
            left: 0; right: 0; bottom: 0;
            width: 100%;
            background: #525659;
        }

        #pdfEmbed {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* ── Fallback (browser tidak support embed PDF) ─ */
        #pdfFallback {
            display: none;
            height: 100%;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #fff;
            text-align: center;
            padding: 2rem;
        }

        /* ── Mobile: iframe tidak ideal ──────────────── */
        @media (max-width: 768px) {
            .file-title { max-width: 180px; }
        }
    </style>
</head>
<body>

{{-- ── Topbar ────────────────────────────────────────── --}}
<div id="topbar">

    {{-- Kembali ke project --}}
    <a href="{{ route('projects.show', $project) }}"
       style="color:#fff;text-decoration:none;flex-shrink:0"
       title="Kembali ke project">
        <i class="bi bi-arrow-left fs-5"></i>
    </a>

    <div style="width:1px;height:24px;background:rgba(255,255,255,.2)"></div>

    {{-- Ikon PDF --}}
    <div style="width:32px;height:32px;background:rgba(220,53,69,.8);
                border-radius:6px;display:flex;align-items:center;
                justify-content:center;flex-shrink:0">
        <i class="bi bi-file-earmark-pdf text-white" style="font-size:.9rem"></i>
    </div>

    {{-- Nama file & project --}}
    <div style="overflow:hidden;flex:1;min-width:0">
        <div class="file-title">{{ $file->original_name }}</div>
        <div class="file-meta">
            {{ $project->code }} — {{ $project->name }}
            <span class="mx-1">·</span>
            {{ $file->file_size_human }}
        </div>
    </div>

    {{-- Tombol Download --}}
    <a href="{{ $file->public_url }}"
       download="{{ $file->original_name }}"
       class="btn btn-sm"
       style="background:rgba(255,255,255,.15);color:#fff;
              border:1px solid rgba(255,255,255,.2);font-size:.8rem"
       title="Download file">
        <i class="bi bi-download me-1"></i>
        <span class="d-none d-md-inline">Download</span>
    </a>

    {{-- Tombol Buka di Tab Baru --}}
    <a href="{{ $file->public_url }}"
       target="_blank"
       class="btn btn-sm"
       style="background:rgba(255,255,255,.15);color:#fff;
              border:1px solid rgba(255,255,255,.2);font-size:.8rem"
       title="Buka di tab baru">
        <i class="bi bi-box-arrow-up-right"></i>
    </a>

</div>

{{-- ── PDF Container ───────────────────────────────────── --}}
<div id="pdfContainer">

    {{-- Primary: embed langsung via iframe --}}
    <iframe
        id="pdfEmbed"
        src="{{ $file->public_url }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
        type="application/pdf"
        title="{{ $file->original_name }}"
        onload="handleIframeLoad()"
        onerror="showFallback()">
    </iframe>

    {{-- Fallback jika iframe gagal --}}
    <div id="pdfFallback">
        <i class="bi bi-file-earmark-pdf" style="font-size:5rem;opacity:.5;color:#fff"></i>
        <h5 class="mt-3 text-white">Browser Tidak Mendukung Preview PDF</h5>
        <p style="color:rgba(255,255,255,.6);max-width:400px" class="mt-2">
            Browser kamu tidak mendukung tampilan PDF langsung.
            Gunakan tombol di bawah untuk membuka atau mendownload file.
        </p>
        <div class="d-flex gap-2 mt-3 justify-content-center flex-wrap">
            <a href="{{ $file->public_url }}"
               target="_blank"
               class="btn btn-primary">
                <i class="bi bi-box-arrow-up-right me-2"></i>Buka di Tab Baru
            </a>
            <a href="{{ $file->public_url }}"
               download="{{ $file->original_name }}"
               class="btn btn-outline-light">
                <i class="bi bi-download me-2"></i>Download PDF
            </a>
        </div>
        <div class="mt-4 p-3 rounded"
             style="background:rgba(255,255,255,.1);max-width:400px">
            <div class="text-white-50 small mb-2">Info File</div>
            <div class="text-white small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-white-50">Nama</span>
                    <span class="text-truncate ms-3">{{ $file->original_name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-white-50">Ukuran</span>
                    <span>{{ $file->file_size_human }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-white-50">Diupload</span>
                    <span>{{ $file->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Cek apakah iframe berhasil load PDF
function handleIframeLoad() {
    const iframe = document.getElementById('pdfEmbed');
    try {
        // Jika iframe kosong atau error, tampilkan fallback
        if (iframe.contentDocument &&
            iframe.contentDocument.body &&
            iframe.contentDocument.body.innerHTML === '') {
            showFallback();
        }
    } catch(e) {
        // Cross-origin error = PDF berhasil load (browser block akses)
        // Ini normal, artinya PDF tampil dengan baik
    }
}

// Tampilkan fallback UI
function showFallback() {
    document.getElementById('pdfEmbed').style.display    = 'none';
    document.getElementById('pdfFallback').style.display = 'flex';
}

// Deteksi mobile — sarankan buka di tab baru
(function() {
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    if (isMobile) {
        // Mobile browser sering tidak render PDF via iframe
        // Langsung tampilkan fallback dengan opsi buka di tab baru
        document.getElementById('pdfEmbed').style.display    = 'none';
        document.getElementById('pdfFallback').style.display = 'flex';
    }
})();
</script>

</body>
</html>