@extends('secure.layout')
@section('title', $template ? 'Edit Email Template' : 'Create Email Template')
@section('content')
    <a class="back" href="{{ route('email-studio.index') }}">&larr; Email Studio</a>
    <h1>{{ $template ? 'Edit email template' : 'New email template' }}</h1>
    <p class="muted">Design a Gmail-friendly branded message, see a live preview, and attach an existing or generated PDF before sending.</p>

    <form id="studio-template-form" method="POST"
        action="{{ $template ? route('email-studio.update', $template->id) : route('email-studio.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if($template) @method('PATCH') @endif

        <section class="card">
            <h2>Template settings</h2>
            <div class="grid">
                <div>
                    <label for="name">Template name (internal)</label>
                    <input type="text" id="name" name="name" maxlength="180" required
                        value="{{ old('name', $template->name ?? '') }}" placeholder="Document status update">
                </div>
                <div>
                    <label for="subject">Email subject</label>
                    <input type="text" id="subject" name="subject" maxlength="250" required
                        value="{{ old('subject', $template->subject ?? '') }}" placeholder="Update for @{{client_name}}">
                </div>
            </div>
            <p class="small muted">Personalization fields: <code>@{{client_name}}</code>, <code>@{{reference}}</code>, <code>@{{status}}</code>,
                <code>@{{message}}</code>, <code>@{{date}}</code>.
                These are safely replaced on send.</p>
        </section>

        <section class="card">
            <h2>Visual editor</h2>
            <p class="small muted">Pasted text retains supported bold, italic, underline, colors, fonts, lists and alignment. Unsupported or unsafe HTML is automatically stripped before saving.</p>
            <div class="grid">
                <div>
                    <label for="studio-import-html">Import an HTML email template (.html)</label>
                    <input type="file" id="studio-import-html" accept=".html,.htm,text/html">
                </div>
                <div>
                    <label for="studio-import-mode">Import mode</label>
                    <select id="studio-import-mode">
                        <option value="replace">Replace existing editor content</option>
                        <option value="append">Append after existing content</option>
                    </select>
                </div>
            </div>
            <p class="small muted" id="studio-import-feedback" role="status" aria-live="polite">
                Maximum HTML file size: 1 MB. Embedded and remote images are excluded for safety; re-add approved images through the private image slots below. The file is read locally in your browser.
            </p>
            <div class="row" id="editor-toolbar" role="toolbar" aria-label="Text formatting tools">
                <button type="button" class="secondary" data-editor-command="bold" aria-label="Bold">B</button>
                <button type="button" class="secondary" data-editor-command="italic" aria-label="Italic"><em>I</em></button>
                <button type="button" class="secondary" data-editor-command="underline" aria-label="Underline"><u>U</u></button>
                <button type="button" class="secondary" data-editor-command="strikeThrough" aria-label="Strikethrough">S</button>
                <button type="button" class="secondary" data-editor-command="insertUnorderedList">Bullets</button>
                <button type="button" class="secondary" data-editor-command="insertOrderedList">Numbers</button>
                <button type="button" class="secondary" data-editor-command="justifyLeft">Left</button>
                <button type="button" class="secondary" data-editor-command="justifyCenter">Center</button>
                <button type="button" class="secondary" data-editor-command="justifyRight">Right</button>
            </div>
            <div class="grid" style="margin:1rem 0">
                <div>
                    <label for="font-family">Font</label>
                    <select id="font-family" data-format="fontName">
                        @foreach($availableFonts as $font)
                            <option value="{{ $font }}">{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="font-size">Size</label>
                    <select id="font-size" data-format="fontSize">
                        @foreach([12,14,16,18,20,24,28] as $px)
                            <option value="{{ $px }}" @selected($px===16)>{{ $px }} px</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="font-color">Text color</label>
                    <input type="color" id="font-color" value="#192a40">
                </div>
            </div>

            <label for="email-body-editor">Email body</label>
            <div id="email-body-editor" contenteditable="true" role="textbox"
                aria-label="Email content editor" aria-multiline="true"
                style="background:#fff;border:1px solid #9dadbd;border-radius:8px;min-height:230px;padding:16px;line-height:1.6;overflow-wrap:anywhere">
                {!! app(\App\Services\EmailStudioHtml::class)->sanitize(old('html_body', $template->html_body ?? '<p>Dear {{client_name}},</p><p>We have an update regarding reference <strong>{{reference}}</strong>.</p><p>{{message}}</p>')) !!}
            </div>
            <input type="hidden" id="html-body-value" name="html_body" value="">
        </section>

        <section class="card">
            <h2>Image placement and size</h2>
            <p class="small muted">Use one top banner and up to two bottom images. Drag an image file onto a slot or choose a file. Email positioning uses table cells, not exact PDF-style X/Y coordinates.</p>
            <div class="grid">
                @foreach(['top'=>'Top banner','bottom_left'=>'Bottom left','bottom_right'=>'Bottom right'] as $slot=>$label)
                    <div class="card" style="margin:0">
                        <h3 style="margin-top:0">{{ $label }}</h3>
                        @if(isset($images[$slot]['path']) && $template)
                            <img src="{{ route('email-studio.image',[$template->id,$slot]) }}"
                                alt="{{ $label }} image currently saved"
                                style="max-width:100%;max-height:140px;object-fit:contain">
                        @endif
                        <img class="slot-preview" data-preview="{{ $slot }}" alt="New {{ $label }} preview"
                            style="display:none;max-width:100%;max-height:140px;object-fit:contain">
                        <label for="image-{{ $slot }}">Choose image (max 2 MB)</label>
                        <input type="file" id="image-{{ $slot }}" data-image-slot="{{ $slot }}"
                            name="image_{{ $slot }}" accept="image/jpeg,image/png,image/webp">
                        <label for="width-{{ $slot }}">Image width (px)</label>
                        <input type="range" id="width-{{ $slot }}" name="width_{{ $slot }}"
                            min="80" max="600" step="10" value="{{ old('width_'.$slot, $images[$slot]['width'] ?? ($slot==='top'?600:250)) }}"
                            data-slot-width="{{ $slot }}">
                        @if(isset($images[$slot]['path']))
                            <label class="row">
                                <input type="checkbox" name="delete_{{ $slot }}" value="1" style="width:auto">
                                Remove this image when saving
                            </label>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2>Footer and signature</h2>
            <label for="footer">Company footer</label>
            <textarea id="footer" name="footer" maxlength="2000" rows="3" placeholder="Join Gallagher | Your registered contact details">{{ old('footer', $template->footer ?? '') }}</textarea>
        </section>

        <section class="card">
            <h2>Live email preview</h2>
            <div class="row">
                <button type="button" class="secondary" id="preview-desktop">Desktop 600px</button>
                <button type="button" class="secondary" id="preview-mobile">Mobile 360px</button>
                <button type="button" class="secondary" id="preview-refresh">Refresh preview</button>
            </div>
            <iframe id="studio-preview" title="Sandboxed email preview; scripts disabled"
                sandbox="" referrerpolicy="no-referrer"
                style="display:block;width:100%;height:440px;margin-top:14px;background:#fff;border:1px solid #cbd5e0;border-radius:8px"></iframe>
            <p class="small muted">Preview is an approximation. Gmail may alter fonts and spacing; exact pixel positioning across Gmail and Outlook is not guaranteed.</p>
        </section>

        <button type="submit">Save template</button>
    </form>

    @if($template)
    <section class="card">
        <h2>SMTP send or test email</h2>
        <p class="small muted">The saved template is sent via your server's configured SMTP connection, not browser JavaScript. Save any edits above before sending.</p>
        <form method="POST" action="{{ route('email-studio.send',$template->id) }}"
            enctype="multipart/form-data">
            @csrf
            <div class="grid">
                <div>
                    <label for="recipient">Recipient email</label>
                    <input type="email" name="recipient" id="recipient" maxlength="254" required
                        value="{{ old('recipient', auth()->user()->email) }}">
                </div>
                <div>
                    <label for="client_name">Client name</label>
                    <input type="text" name="client_name" id="client_name" maxlength="180"
                        value="{{ old('client_name') }}">
                </div>
                <div>
                    <label for="reference">Reference number</label>
                    <input type="text" name="reference" id="reference" maxlength="100"
                        value="{{ old('reference') }}">
                </div>
                <div>
                    <label for="status">Application status</label>
                    <input type="text" name="status" id="status" maxlength="150"
                        value="{{ old('status') }}">
                </div>
            </div>
            <label for="message">Personal message</label>
            <textarea name="message" id="message" maxlength="4000" placeholder="Personalize this email...">{{ old('message') }}</textarea>
            <label for="pdf_mode">PDF attachment</label>
            <select name="pdf_mode" id="pdf_mode" required>
                <option value="none">No PDF</option>
                <option value="upload">Attach my PDF (max 8 MB)</option>
                <option value="generate">Generate styled PDF from email template</option>
            </select>
            <label for="pdf">PDF file (only if attaching a PDF)</label>
            <input type="file" name="pdf" id="pdf" accept=".pdf,application/pdf">
            <p class="small muted">Only send transactional emails to intended recipients. Marketing/bulk email requires separate consent and unsubscribe controls.</p>
            <button type="submit">Send through SMTP</button>
        </form>
    </section>
    @else
        <div class="alert" role="status">Save this template to enable SMTP test sends and PDF attachments.</div>
    @endif

    <script src="{{ asset('email-studio/editor.js') }}" defer></script>
@endsection
