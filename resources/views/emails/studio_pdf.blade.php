<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body {font-family:DejaVu Sans,sans-serif;font-size:12px;color:#1b2b3e;line-height:1.6;}
    .wrap{margin:12mm auto;width:100%;} .banner{max-width:100%;height:auto;display:block;margin:0 auto 18px;}
    .content{margin:10px 0 24px;} footer{border-top:1px solid #cbd6e2;color:#596779;font-size:10px;padding-top:10px;text-align:center;}
    table{border-collapse:collapse;} td,th{padding:6px;}
</style></head><body>
<div class="wrap">
    @if(!empty($pdfImages['top']))
        <img class="banner" src="{{ $pdfImages['top'] }}" alt="">
    @endif
    <div class="content">{!! $bodyHtml !!}</div>
    @if(!empty($pdfImages['bottom_left']) || !empty($pdfImages['bottom_right']))
        <table width="100%"><tr>
            @foreach(['bottom_left','bottom_right'] as $slot)
                <td width="50%" align="center">
                    @if(!empty($pdfImages[$slot]))
                        <img src="{{ $pdfImages[$slot] }}" width="220" alt="">
                    @endif
                </td>
            @endforeach
        </tr></table>
    @endif
    @if($footerText !== '')
        <footer>{!! nl2br(e($footerText)) !!}</footer>
    @endif
</div>
</body></html>
