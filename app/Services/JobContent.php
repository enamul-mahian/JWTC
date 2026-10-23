<?php
namespace App\Services;
class JobContent
{
    private const ALLOWED = ['p','div','br','ul','ol','li','strong','b','em','i','u','h2','h3','h4','blockquote','a'];
    public function render(?string $value, array $hiddenEmails = []): string
    {
        $value = $value ?? '';
        $list = json_decode($value, true);
        if (is_array($list) && array_is_list($list)) {
            $value = '<ul>'.implode('', array_map(static fn ($item) => '<li>'.htmlspecialchars(ucwords(str_replace('_', ' ', is_scalar($item) ? (string)$item : '')), ENT_QUOTES, 'UTF-8').'</li>', $list)).'</ul>';
        } elseif (!str_contains($value, '<')) {
            $value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        }
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$value.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $walk = function ($node) use (&$walk, $hiddenEmails): string {
                if ($node instanceof \DOMText) {
                    $text = $node->nodeValue;
                    foreach ($hiddenEmails as $email) if (is_string($email) && $email !== '') $text = str_ireplace($email, '[contact hidden]', $text);
                    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                }
                if (!($node instanceof \DOMElement)) return '';
                $tag = strtolower($node->tagName);
                if (in_array($tag, ['script','style','iframe','object','embed','svg','math','template','form','input','button'], true)) return '';
                $inside = '';
                foreach ($node->childNodes as $child) $inside .= $walk($child);
                if (!in_array($tag, self::ALLOWED, true)) return $inside;
                if ($tag === 'br') return '<br>';
                $attributes = '';
                if ($tag === 'a') {
                    $href = trim($node->getAttribute('href'));
                    $hide = false;
                    foreach ($hiddenEmails as $email) if (is_string($email) && $email !== '' && stripos(rawurldecode($href), $email) !== false) $hide = true;
                    if (preg_match('#^(https?://|mailto:)#i', $href) && !$hide && !preg_match('/[\x00-\x20]/', $href)) $attributes = ' href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'" rel="nofollow noopener noreferrer"';
                    else return $inside;
                }
                return '<'.$tag.$attributes.'>'.$inside.'</'.$tag.'>';
            };
            $html = '';
            foreach ($document->getElementsByTagName('body')->item(0)?->childNodes ?? [] as $child) $html .= $walk($child);
            return $html;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
    public function text(?string $value, array $hiddenEmails = []): string
    {
        $html = $this->render($value, $hiddenEmails);
        $html = preg_replace('#<li>#i', "\n• ", $html);
        $html = preg_replace('#<br\s*/?>|</(?:p|div|li|ul|ol|h[234]|blockquote)>#i', "\n", $html);
        return trim(preg_replace('/\n{2,}/', "\n", html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    }
}
