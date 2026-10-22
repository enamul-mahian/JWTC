@extends('secure.layout')
@section('title', 'Visual Email Studio')
@push('styles')
    <link rel="stylesheet" href="{{ asset('email-studio/studio.css') }}">
@endpush
@section('content')
<div class="studio-shell">
    <h1 class="studio-heading">Visual Email Studio</h1>
    <p class="studio-subtitle">Design, preview, save, send and manage SMTP profiles. Old templates and email logs remain available.</p>
    @if(session('smtp_error'))<div class="alert error" role="alert">{{ session('smtp_error') }}</div>@endif

    <div class="studio-layout">
        <section class="studio-card" aria-labelledby="builder-label">
            <div class="studio-between">
                <h2 id="builder-label">1. Template Builder</h2>
                <a href="{{ route('email-studio.create') }}" class="studio-button secondary">+ New</a>
            </div>
            <form id="studio-template-form" method="POST"
                action="{{ $template ? route('email-studio.update',$template->id) : route('email-studio.store') }}"
                enctype="multipart/form-data">
                @csrf
                @if($template) @method('PATCH') @endif
                <label for="name">Template name</label>
                <input type="text" name="name" id="name" maxlength="180" required
                    value="{{ old('name', $template->name ?? '') }}" placeholder="Application Update">

                <label for="studio-saved-template">Saved Templates</label>
                <select id="studio-saved-template" aria-label="Choose saved template">
                    <option value="{{ route('email-studio.create') }}" @selected(!$template)>New template</option>
                    @foreach($templates as $saved)
                        <option value="{{ route('email-studio.edit',$saved->id) }}" @selected($template && $saved->id == $template->id)>{{ $saved->name }}</option>
                    @endforeach
                </select>

                <div class="studio-toolbar" id="editor-toolbar" role="toolbar" aria-label="Rich formatting">
                    <select id="font-family" aria-label="Font family" data-format="fontName">
                        @foreach($availableFonts as $font)<option value="{{ $font }}">{{ $font }}</option>@endforeach
                    </select>
                    <select id="font-size" aria-label="Font size" data-format="fontSize">
                        @foreach([12,14,16,18,20,24,28] as $px)<option value="{{ $px }}" @selected($px===16)>{{ $px }}px</option>@endforeach
                    </select>
                    <select id="paragraph-format" aria-label="Paragraph style">
                        <option value="p">Paragraph</option>
                        <option value="h1">Heading 1</option>
                        <option value="h2">Heading 2</option>
                        <option value="h3">Heading 3</option>
                        <option value="blockquote">Quote</option>
                    </select>
                    <button type="button" data-editor-command="bold" aria-label="Bold"><b>B</b></button>
                    <button type="button" data-editor-command="italic" aria-label="Italic"><i>I</i></button>
                    <button type="button" data-editor-command="underline" aria-label="Underline"><u>U</u></button>
                    <button type="button" data-editor-command="strikeThrough" aria-label="Strike">S</button>
                    <input type="color" id="font-color" value="#192a40" aria-label="Text color">
                    <button type="button" data-editor-command="justifyLeft" title="Align left">≡</button>
                    <button type="button" data-editor-command="justifyCenter" title="Align center">☷</button>
                    <button type="button" data-editor-command="justifyRight" title="Align right">≡</button>
                    <button type="button" data-editor-command="insertUnorderedList" title="Bullet list">• List</button>
                    <button type="button" data-editor-command="insertOrderedList" title="Number list">1. List</button>
                    <select id="studio-spacing" aria-label="Line spacing">
                        <option value="1.3">Spacing 1.3</option>
                        <option value="1.5" selected>Spacing 1.5</option>
                        <option value="1.8">Spacing 1.8</option>
                        <option value="2">Spacing 2</option>
                    </select>
                    <button type="button" id="studio-insert-link">Link</button>
                    <button type="button" data-editor-command="unlink">Unlink</button>
                    <button type="button" id="studio-insert-table">Table</button>
                    <button type="button" data-editor-command="removeFormat">Clear format</button>
                    <button type="button" id="studio-insert-image">+ Image</button>
                    <button type="button" id="studio-import-button">Paste HTML</button>
                </div>

                <div class="studio-two">
                    <div>
                        <label for="studio-import-html">Import HTML file (.html or .htm)</label>
                        <input id="studio-import-html" type="file" accept=".html,.htm,text/html">
                    </div>
                    <div>
                        <label for="studio-import-mode">Import mode</label>
                        <select id="studio-import-mode">
                            <option value="replace">Replace</option>
                            <option value="append">Append</option>
                        </select>
                    </div>
                </div>
                <p id="studio-import-feedback" class="studio-muted" role="status" aria-live="polite">Supported HTML formatting is preserved. Unsafe scripts and remote images are removed before saving.</p>

                <div class="studio-toolbar">
                    <label for="width-top" style="margin:0">Image width</label>
                    <input type="range" id="width-top" name="width_top" min="80" max="600" step="10"
                        value="{{ old('width_top', $images['top']['width'] ?? 600) }}"
                        data-slot-width="top" style="flex:1;min-width:90px;">
                    <label for="image-align-top" style="margin:0">Image alignment</label>
                    <select name="align_top" id="image-align-top" data-slot-align="top">
                        @foreach(['left'=>'Left','center'=>'Center','right'=>'Right'] as $v=>$label)
                            <option value="{{ $v }}" @selected(old('align_top', $images['top']['align'] ?? 'center')===$v)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="file" id="image-top" data-image-slot="top" name="image_top"
                        accept="image/jpeg,image/png,image/webp" style="width:auto;max-width:160px;">
                </div>

                <label for="email-body-editor">Email content</label>
                <div id="email-body-editor" contenteditable="true" role="textbox" aria-label="Email body"
                    aria-multiline="true">{!! app(\App\Services\EmailStudioHtml::class)->sanitize(old('html_body', $template->html_body ?? '<p>Status update</p><p>Dear Applicant,</p><p>Please review the latest information regarding your application.</p>')) !!}</div>
                <input type="hidden" name="html_body" id="html-body-value">

                <details style="margin-top:8px">
                    <summary class="studio-muted">Other images and footer</summary>
                    <div class="studio-three">
                        @foreach(['top'=>'Top banner','bottom_left'=>'Bottom left','bottom_right'=>'Bottom right'] as $slot=>$label)
                            <div class="card studio-drop">
                                <strong>{{ $label }}</strong>
                                @if(isset($images[$slot]['path']) && $template)
                                    <img src="{{ route('email-studio.image', [$template->id,$slot]) }}" alt="Saved {{ $label }}">
                                @endif
                                <img class="slot-preview" data-preview="{{ $slot }}" alt="New {{ $label }}" style="display:none;">
                                @if($slot !== 'top')
                                    <input type="file" id="image-{{ $slot }}" data-image-slot="{{ $slot }}"
                                        name="image_{{ $slot }}" accept="image/jpeg,image/png,image/webp">
                                    <label for="width-{{ $slot }}">Width</label>
                                    <input type="range" name="width_{{ $slot }}" id="width-{{ $slot }}"
                                        min="80" max="600" step="10" value="{{ old('width_'.$slot, $images[$slot]['width'] ?? 250) }}" data-slot-width="{{ $slot }}">
                                @endif
                                @if($slot !== 'top')
                                <label for="image-align-{{ $slot }}">Alignment</label>
                                <select id="image-align-{{ $slot }}" name="align_{{ $slot }}" data-slot-align="{{ $slot }}">
                                    @foreach(['left'=>'Left','center'=>'Center','right'=>'Right'] as $v=>$label2)
                                        <option value="{{ $v }}" @selected(old('align_'.$slot, $images[$slot]['align'] ?? 'center')===$v)>{{ $label2 }}</option>
                                    @endforeach
                                </select>
                                @endif
                                @if(isset($images[$slot]['path']))
                                    <label><input type="checkbox" name="delete_{{ $slot }}" value="1" style="width:auto"> Remove</label>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <label for="footer">Signature / footer</label>
                    <textarea id="footer" name="footer" maxlength="2000" rows="2">{{ old('footer', $template->footer ?? '') }}</textarea>
                </details>

                <label for="subject">Template subject</label>
                <input type="text" id="subject" name="subject" maxlength="250" required
                    value="{{ old('subject', $template->subject ?? '') }}">
                <p class="studio-muted">Personalization: @{{client_name}}, @{{reference}}, @{{status}}, @{{message}}, @{{date}}.</p>
                <div class="studio-row">
                    <button type="submit">Save Template</button>
                    <button type="button" class="secondary" id="preview-refresh">Refresh Preview</button>
                </div>
            </form>
            @if($template)
                <div class="studio-row" style="margin-top:9px">
                    <form action="{{ route('email-studio.duplicate',$template->id) }}" method="POST">@csrf<button class="secondary">Duplicate</button></form>
                    <form action="{{ route('email-studio.destroy',$template->id) }}" method="POST" data-confirm="Delete this template? Send logs will be preserved.">
                        @csrf @method('DELETE')
                        <button class="danger" type="submit">Delete Template</button>
                    </form>
                </div>
            @endif
        </section>

        <section class="studio-card" aria-labelledby="preview-label">
            <div class="studio-between">
                <h2 id="preview-label">2. Live Preview</h2>
                <div class="studio-row">
                    <button type="button" class="secondary" id="preview-desktop">Desktop</button>
                    <button type="button" class="secondary" id="preview-mobile">Mobile</button>
                </div>
            </div>
            <p class="studio-muted">Preview uses the same email content and design as SMTP delivery. Browser rendering may differ from Gmail.</p>
            <iframe id="studio-preview" title="Sandboxed email preview" sandbox="allow-same-origin"
                referrerpolicy="no-referrer"></iframe>
            <p class="studio-preview-caption">
                {{ $visualPages->count() }} imported PDF visual page(s) · 3 supported image slots.
                Save before sending. Gmail inbox formatting requires a real test.
            </p>

            @if($template)
            <details style="margin-top:13px" @if($visualPages->isNotEmpty()) open @endif>
                <summary><strong>PDF → Email Visual Pages (1–3 pages)</strong></summary>
                <p class="studio-muted">Converts uploaded PDF to inline CID images, or use PNG/JPG fallback when PDF tools are unavailable. These pages appear in email, not as public file links.</p>
                <form method="POST" action="{{ route('email-studio.visual.import',$template->id) }}" enctype="multipart/form-data">
                    @csrf
                    <label for="pdf-visual">Import 1–3 page PDF (max 8MB)</label>
                    <input type="file" name="pdf_visual" id="pdf-visual" accept=".pdf,application/pdf">
                    <div class="studio-three">
                        @foreach([1,2,3] as $page)
                            <div>
                                <label for="visual-page-{{ $page }}">Page {{ $page }} PNG/JPG (optional)</label>
                                <input type="file" id="visual-page-{{ $page }}" name="visual_page_{{ $page }}" accept="image/png,image/jpeg">
                            </div>
                        @endforeach
                    </div>
                    <button type="submit">Import Visual Pages</button>
                </form>
                @if($visualPages->isNotEmpty())
                    <div class="studio-three">
                    @foreach($visualPages as $visual)
                        <div class="studio-drop">
                            <div class="studio-muted">Page {{ $visual->page_number }}</div>
                            <img data-visual-page="{{ $visual->page_number }}"
                                 src="{{ route('email-studio.visual.page', [$template->id, $visual->page_number]) }}"
                                 alt="Imported page {{ $visual->page_number }}" style="width:100%;max-height:160px;">
                        </div>
                    @endforeach
                    </div>
                    <form method="POST" action="{{ route('email-studio.visual.remove', $template->id) }}"
                        data-confirm="Remove imported visual pages?">@csrf @method('DELETE')
                        <button type="submit" class="danger">Remove Pages</button>
                    </form>
                @endif
            </details>
            @endif
        </section>

        <section class="studio-card" aria-labelledby="send-label">
            <h2 id="send-label">3. Send Email</h2>
            @if($template)
            <form method="POST" action="{{ route('email-studio.send',$template->id) }}" enctype="multipart/form-data">
                @csrf
                <label for="email-send-template">Template</label>
                <select name="send_template" id="email-send-template">
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" data-send-url="{{ route('email-studio.send',$t->id) }}" @selected($template->id == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
                <label for="recipient">Recipient</label>
                <input type="email" id="recipient" name="recipient" required maxlength="254"
                    value="{{ old('recipient', auth()->user()->email) }}" placeholder="client@example.com">
                <label for="send-subject">Subject (optional override)</label>
                <input type="text" id="send-subject" name="send_subject" maxlength="250"
                    value="{{ old('send_subject', $template->subject) }}">
                <label for="smtp-profile-send">SMTP Profile</label>
                <select id="smtp-profile-send" name="smtp_profile_id">
                    <option value="">Environment SMTP (or default saved profile)</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" @selected(old('smtp_profile_id', $defaultProfileId)==$profile->id)>
                            {{ $profile->name }}{{ $profile->is_default ? ' — Default' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="studio-two">
                    <div>
                        <label for="client_name">Client name</label>
                        <input id="client_name" name="client_name" maxlength="180" placeholder="Client name">
                    </div>
                    <div>
                        <label for="reference">Reference</label>
                        <input id="reference" name="reference" maxlength="100" placeholder="Reference no.">
                    </div>
                </div>
                <label for="status">Status</label>
                <input id="status" name="status" maxlength="150" placeholder="Issued">
                <label for="message">Message</label>
                <textarea id="message" name="message" maxlength="4000" rows="2"></textarea>
                <label for="pdf_mode">PDF attachment (optional)</label>
                <select id="pdf_mode" name="pdf_mode">
                    <option value="none">No PDF attachment</option>
                    <option value="upload">Attach my PDF</option>
                    <option value="generate">Generate PDF from template</option>
                </select>
                <input id="pdf" name="pdf" type="file" accept=".pdf,application/pdf">
                <p class="studio-muted">Email visuals are embedded inline. An attached PDF is separate. Send only authorized client correspondence.</p>
                <button type="submit">Send Email</button>
                <p class="studio-muted">A successful response means accepted by SMTP, not guaranteed Gmail Inbox delivery.</p>
            </form>
            @else
                <p class="studio-help">Save a template first, then send it through your configured SMTP profile with optional PDF attachment.</p>
            @endif
        </section>

        <section class="studio-card" aria-labelledby="smtp-label">
            <h2 id="smtp-label">4. Custom SMTP Manager</h2>
            <form method="POST" id="studio-smtp-form" action="{{ route('email-studio.smtp.store') }}">
                @csrf
                <input type="hidden" name="_method" id="studio-smtp-method" value="POST">
                <label for="smtp-manager-select">Edit existing / New profile</label>
                <select id="smtp-manager-select">
                    <option value="">+ New SMTP Profile</option>
                    @foreach($profiles as $p)
                        <option value="{{ $p->id }}"
                            data-edit-url="{{ route('email-studio.smtp.update',$p->id) }}"
                            data-name="{{ $p->name }}" data-host="{{ $p->host }}"
                            data-port="{{ $p->port }}" data-encryption="{{ $p->encryption }}"
                            data-username="{{ $p->username }}" data-from-address="{{ $p->from_address }}"
                            data-from-name="{{ $p->from_name }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <div class="studio-two">
                    <div><label for="smtp-name">Profile Name</label><input id="smtp-name" name="name" required maxlength="120" placeholder="Office SMTP"></div>
                    <div><label for="smtp-host">SMTP Host</label><input id="smtp-host" name="host" required maxlength="253" placeholder="smtp.gmail.com"></div>
                    <div><label for="smtp-port">Port</label><select id="smtp-port" name="port"><option value="465">465</option><option value="587" selected>587</option></select></div>
                    <div><label for="smtp-encryption">Encryption</label><select id="smtp-encryption" name="encryption"><option value="tls" selected>STARTTLS (587)</option><option value="ssl">SSL/TLS (465)</option></select></div>
                </div>
                <label for="smtp-username">Username</label>
                <input id="smtp-username" name="username" required maxlength="254" autocomplete="off">
                <label for="smtp-password">Password (leave blank on edit to retain)</label>
                <input type="password" id="smtp-password" name="password" autocomplete="new-password" maxlength="2000">
                <div class="studio-two">
                    <div><label for="smtp-from-address">Sender email</label><input id="smtp-from-address" type="email" name="from_address" maxlength="254" required></div>
                    <div><label for="smtp-from-name">Sender display name</label><input id="smtp-from-name" name="from_name" maxlength="180" value="Join Gallagher" required></div>
                </div>
                <div class="studio-row"><button type="submit">Save SMTP Profile</button><button type="button" class="secondary" id="smtp-new-profile">New Profile</button></div>
                <p class="studio-muted">Passwords are encrypted at rest and never returned to this page. Supports STARTTLS 587 and SSL/TLS 465.</p>
            </form>

            <h3>Available Profiles</h3>
            <div class="studio-help">Environment SMTP from protected server settings is always available when configured.</div>
            @foreach($profiles as $p)
                <div class="studio-between" style="border-bottom:1px solid #e4ebf4;padding:8px 0">
                    <div>
                        <strong>{{ $p->name }}</strong>
                        @if($p->is_default)<span class="studio-badge">Default</span>@endif
                        <div class="studio-muted">{{ $p->host }}:{{ $p->port }} · {{ strtoupper($p->encryption) }}</div>
                    </div>
                    <div class="studio-row">
                        <button type="button" class="secondary studio-smtp-edit" data-id="{{ $p->id }}">Edit</button>
                        <form method="POST" action="{{ route('email-studio.smtp.test',$p->id) }}">@csrf<button type="submit" class="secondary">Test</button></form>
                        @unless($p->is_default)
                            <form method="POST" action="{{ route('email-studio.smtp.default',$p->id) }}">@csrf<button type="submit" class="secondary">Set Default</button></form>
                        @endunless
                        <form method="POST" action="{{ route('email-studio.smtp.destroy',$p->id) }}"
                            data-confirm="Delete this SMTP profile?">@csrf @method('DELETE')
                            <button type="submit" class="danger">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
            @if($profiles->isEmpty())<p class="studio-muted">No saved custom SMTP profiles yet.</p>@endif
        </section>

        <section class="studio-card studio-full" aria-labelledby="logs-label">
            <h2 id="logs-label">5. Recent Email Attempts</h2>
            <div class="table-wrap">
                <table class="studio-logs">
                    <thead><tr><th>UTC</th><th>Recipient</th><th>Subject</th><th>Result</th><th>PDF</th></tr></thead>
                    <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($log->created_at)->utc()->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->recipient }}</td><td>{{ $log->subject }}</td>
                            <td><span class="studio-stat">{{ $log->result==='accepted' ? 'Accepted by SMTP server' : 'Failed' }}</span></td>
                            <td>{{ $log->pdf_attached ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No SMTP attempts recorded yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<script src="{{ asset('email-studio/editor.js') }}" defer></script>
@endsection
