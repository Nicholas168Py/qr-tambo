    <div id="toast-container"></div>
    <div id="loading-overlay" class="loading-overlay" style="display:none">
        <div class="spinner"></div>
    </div>
    <div id="debug-panel" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#1a0a2e;color:#0f0;font-family:monospace;font-size:11px;padding:8px;max-height:40vh;overflow-y:auto;border-top:2px solid #f0f;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <strong style="color:#fff;">🐛 DEBUG</strong>
            <button onclick="var p=document.getElementById('debug-panel');p.style.display='none'" style="background:none;border:none;color:#f66;cursor:pointer;font-size:14px;">✕</button>
        </div>
        <div id="debug-logs" style="white-space:pre-wrap;word-break:break-all;"></div>
    </div>
    <script src="<?= $basePath ?>assets/js/app.js"></script>
</body>
</html>
