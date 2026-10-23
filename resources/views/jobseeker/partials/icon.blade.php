@php
$paths = [
'file'=>'M7 3h7l4 4v14H7z M14 3v5h5 M10 12h5 M10 16h5',
'briefcase'=>'M3 7h18v13H3z M8 7V4h8v3 M3 12h18 M10 12v3h4v-3',
'education'=>'M2 8l10-5 10 5-10 5z M6 10v7c4 3 8 3 12 0v-7 M22 8v8',
'location'=>'M19 10c0 6-7 11-7 11S5 16 5 10a7 7 0 1 1 14 0z M9 10a3 3 0 1 0 6 0 3 3 0 1 0-6 0',
'mail'=>'M3 5h18v14H3z M3 6l9 7 9-7',
'phone'=>'M7 3l3 5-3 3c1 3 3 5 6 6l3-3 5 3-1 4C10 23 1 14 3 4z',
'download'=>'M12 3v12 M7 10l5 5 5-5 M4 16v5h16v-5',
'edit'=>'M15 4l5 5 M4 20l5-1L21 7l-5-5L4 14z',
'chart'=>'M5 20V12 M12 20V7 M19 20V3',
'skills'=>'M12 3l3 5 6 1-4 5 1 6-6-3-6 3 1-6-4-5 6-1z',
'globe'=>'M3 12h18 M12 3c6 5 6 13 0 18-6-5-6-13 0-18 M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0',
'lock'=>'M5 10h14v11H5z M8 10V7a4 4 0 0 1 8 0v3 M12 14v3',
'search'=>'M16 16l5 5 M3 10a7 7 0 1 0 14 0 7 7 0 1 0-14 0',
'user'=>'M4 21v-3a8 6 0 0 1 16 0v3 M8 7a4 4 0 1 0 8 0 4 4 0 1 0-8 0',
'calendar'=>'M3 5h18v16H3z M7 2v6 M17 2v6 M3 10h18',
'code'=>'M8 5L2 12l6 7 M16 5l6 7-6 7 M14 3l-4 18',
'support'=>'M4 14V11a8 8 0 0 1 16 0v3 M4 11H2v7h5v-7z M20 11h2v7h-5v-7z M20 18v3h-7',
];
@endphp
<svg class="jg-ui-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$icon] ?? $paths['file'] }}"/></svg>
