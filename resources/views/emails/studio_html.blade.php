<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background-color:#f2f5f9;color:#192a40;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;background-color:#f2f5f9;border-collapse:collapse;">
        <tr><td align="center" style="padding:20px 12px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600"
                   style="width:100%;max-width:600px;border-collapse:collapse;background-color:#ffffff;">
                @if(isset($inlineImages['top']))
                    <tr><td align="{{ $inlineImages['top']['align'] ?? 'center' }}" style="padding:0;">
                        <img src="{{ $message->embed($inlineImages['top']['absolute_path']) }}"
                             alt="Email banner" width="{{ $inlineImages['top']['width'] }}"
                             style="display:block;width:100%;max-width:{{ $inlineImages['top']['width'] }}px;height:auto;border:0;">
                    </td></tr>
                @endif
                <tr><td style="padding:30px 28px;line-height:1.65;font-size:15px;color:#192a40;word-wrap:break-word;">
                    {!! $bodyHtml !!}
                </td></tr>
                @if(isset($inlineImages['bottom_left']) || isset($inlineImages['bottom_right']))
                    <tr><td style="padding:0 24px 20px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;width:100%;">
                            <tr>
                                @foreach(['bottom_left', 'bottom_right'] as $slot)
                                    <td align="{{ $inlineImages[$slot]['align'] ?? 'center' }}" valign="top" width="50%" style="width:50%;padding:4px;">
                                        @if(isset($inlineImages[$slot]))
                                            <img src="{{ $message->embed($inlineImages[$slot]['absolute_path']) }}"
                                                 alt="Supporting image" width="{{ min(270, $inlineImages[$slot]['width']) }}"
                                                 style="display:block;width:100%;max-width:{{ min(270, $inlineImages[$slot]['width']) }}px;height:auto;border:0;">
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </td></tr>
                @endif
                @if(!empty($inlineVisualPages))
                    @foreach($inlineVisualPages as $visual)
                        <tr><td align="center" style="padding:0 12px 12px;">
                            <img src="{{ $message->embed($visual['absolute_path']) }}"
                                 alt="Document page {{ $visual['page_number'] }}" width="576"
                                 style="display:block;width:100%;max-width:576px;height:auto;border:0;">
                        </td></tr>
                    @endforeach
                @endif
                @if($footerText !== '')
                    <tr><td style="border-top:1px solid #e3e8ee;padding:16px 28px 24px;color:#687689;font-size:12px;line-height:1.5;text-align:center;">
                        {!! nl2br(e($footerText)) !!}
                    </td></tr>
                @endif
            </table>
        </td></tr>
    </table>
</body></html>
