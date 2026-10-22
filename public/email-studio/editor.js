/* JWTC Email Studio visual builder. No external libraries, telemetry or network calls. */
(function () {
    'use strict';

    const editor = document.getElementById('email-body-editor');
    if (!editor) return;

    const form = document.getElementById('studio-template-form');
    const hiddenBody = document.getElementById('html-body-value');
    const iframe = document.getElementById('studio-preview');
    const footer = document.getElementById('footer');
    const toolbar = document.getElementById('editor-toolbar');
    const slots = ['top', 'bottom_left', 'bottom_right'];
    const localImages = Object.create(null);

    const escapeText = (value) => String(value)
        .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;').replaceAll('"', '&quot;');

    function normalizeFormatting() {
        // execCommand may use <font> tags; normalize those into portable inline CSS.
        editor.querySelectorAll('font').forEach((node) => {
            const span = document.createElement('span');
            const face = node.getAttribute('face');
            const color = node.getAttribute('color');
            const size = node.getAttribute('size');
            if (face && /^(Arial|Georgia|Verdana|Tahoma|Helvetica|Times New Roman)$/i.test(face)) {
                span.style.fontFamily = face;
            }
            if (color && /^#[0-9a-f]{3,6}$/i.test(color)) {
                span.style.color = color;
            }
            if (size && /^[1-7]$/.test(size)) {
                span.style.fontSize = ({1: '12px', 2: '14px', 3: '16px', 4: '18px',
                    5: '20px', 6: '24px', 7: '28px'})[size];
            }
            while (node.firstChild) span.appendChild(node.firstChild);
            node.replaceWith(span);
        });
    }

    function execute(command, value) {
        editor.focus();
        document.execCommand(command, false, value);
        normalizeFormatting();
        drawPreview();
    }

    if (toolbar) {
        toolbar.querySelectorAll('[data-editor-command]').forEach((button) => {
            // Keep the selection instead of letting the button take focus.
            button.addEventListener('mousedown', (e) => e.preventDefault());
            button.addEventListener('click', () => {
                execute(button.dataset.editorCommand, null);
            });
        });
    }

    const font = document.getElementById('font-family');
    if (font) font.addEventListener('change', () => execute('fontName', font.value));

    const size = document.getElementById('font-size');
    if (size) size.addEventListener('change', () => {
        const x = Number(size.value);
        const cmd = ({12:'1',14:'2',16:'3',18:'4',20:'5',24:'6',28:'7'})[x] || '3';
        execute('fontSize', cmd);
    });

    const color = document.getElementById('font-color');
    if (color) color.addEventListener('input', () => execute('foreColor', color.value));

    function slotImage(slot) {
        const input = document.querySelector('[data-image-slot="' + slot + '"]');
        const preview = document.querySelector('[data-preview="' + slot + '"]');
        const existing = input?.closest('.card')?.querySelector('img:not(.slot-preview)');
        const width = document.querySelector('[data-slot-width="' + slot + '"]');
        if (localImages[slot]) {
            return {url:localImages[slot], width:Number(width?.value || 250)};
        }
        if (existing && !input?.closest('.card')?.querySelector('input[type="checkbox"]')?.checked) {
            return {url:existing.src, width:Number(width?.value || 250)};
        }
        if (preview && preview.style.display !== 'none' && preview.src) {
            return {url:preview.src, width:Number(width?.value || 250)};
        }
        return null;
    }

    function imageHtml(slot) {
        const img = slotImage(slot);
        if (!img) return '';
        // src is a browser-generated blob: URL or our session-protected asset URL.
        const url = img.url.startsWith('blob:') || new URL(img.url, location.origin).origin === location.origin
            ? img.url : '';
        if (!url) return '';
        const px = Math.min(slot === 'top' ? 600 : 270, Math.max(80, img.width));
        return '<img alt="Branded email image" src="' + escapeText(url) +
            '" width="' + px +
            '" style="display:block;width:100%;max-width:' + px +
            'px;height:auto;border:0;margin:auto">';
    }

    function drawPreview() {
        normalizeFormatting();
        hiddenBody.value = editor.innerHTML;
        const main = editor.innerHTML;
        const foot = escapeText(footer?.value || '').replaceAll('\n', '<br>');
        const top = imageHtml('top');
        const lower1 = imageHtml('bottom_left');
        const lower2 = imageHtml('bottom_right');

        iframe.srcdoc = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
            '<style>body{margin:0;padding:0;background:#f2f5f9;font-family:Arial,sans-serif;word-break:break-word}table{border-collapse:collapse}img{max-width:100%;height:auto}</style></head>' +
            '<body><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="padding:16px 8px">' +
            '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#fff;">' +
            (top ? '<tr><td align="center">' + top + '</td></tr>' : '') +
            '<tr><td style="padding:30px 28px;font-size:15px;line-height:1.65;color:#192a40;">' + main + '</td></tr>' +
            ((lower1 || lower2) ? '<tr><td style="padding:0 24px 20px"><table width="100%"><tr>' +
                '<td width="50%" align="center">' + lower1 + '</td>' +
                '<td width="50%" align="center">' + lower2 + '</td>' +
                '</tr></table></td></tr>' : '') +
            (foot ? '<tr><td style="border-top:1px solid #ddd;color:#687689;text-align:center;font-size:12px;padding:16px 28px">' +
                foot + '</td></tr>' : '') +
            '</table></td></tr></table></body></html>';
    }

    slots.forEach((slot) => {
        const input = document.querySelector('[data-image-slot="' + slot + '"]');
        const preview = document.querySelector('[data-preview="' + slot + '"]');
        const slider = document.querySelector('[data-slot-width="' + slot + '"]');
        if (!input) return;

        function displayChosen() {
            if (localImages[slot]) URL.revokeObjectURL(localImages[slot]);
            localImages[slot] = input.files?.[0] ? URL.createObjectURL(input.files[0]) : null;
            if (localImages[slot]) {
                preview.src = localImages[slot];
                preview.style.display = 'block';
            } else {
                preview.removeAttribute('src');
                preview.style.display = 'none';
            }
            drawPreview();
        }

        input.addEventListener('change', displayChosen);
        slider?.addEventListener('input', drawPreview);
        input.closest('.card')?.querySelector('input[type="checkbox"]')
            ?.addEventListener('change', drawPreview);
        const container = input.closest('.card');
        container?.addEventListener('dragover', (e) => e.preventDefault());
        container?.addEventListener('drop', (e) => {
            e.preventDefault();
            const files = e.dataTransfer?.files;
            if (!files || files.length !== 1 || !files[0].type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            displayChosen();
        });
    });

    editor.addEventListener('input', drawPreview);
    footer?.addEventListener('input', drawPreview);
    form?.addEventListener('submit', () => {
        normalizeFormatting();
        hiddenBody.value = editor.innerHTML;
    });

    document.getElementById('preview-desktop')?.addEventListener('click', () => {
        iframe.style.maxWidth = '600px';
    });
    document.getElementById('preview-mobile')?.addEventListener('click', () => {
        iframe.style.maxWidth = '360px';
    });
    document.getElementById('preview-refresh')?.addEventListener('click', drawPreview);

    hiddenBody.value = editor.innerHTML;
    drawPreview();
})();
