<?php
/**
 * Book format helpers: allowed extensions, labels, and whether we can display in browser.
 */

function get_allowed_book_extensions(): array {
    return defined('BOOK_ALLOWED_EXTENSIONS') ? BOOK_ALLOWED_EXTENSIONS : ['pdf', 'epub', 'txt', 'doc', 'docx', 'mobi', 'rtf'];
}

function get_read_in_browser_extensions(): array {
    return defined('BOOK_READ_IN_BROWSER_EXTENSIONS') ? BOOK_READ_IN_BROWSER_EXTENSIONS : ['pdf', 'epub', 'txt'];
}

function is_allowed_book_extension(string $ext): bool {
    return in_array(strtolower($ext), get_allowed_book_extensions(), true);
}

function can_read_in_browser(string $ext): bool {
    return in_array(strtolower($ext), get_read_in_browser_extensions(), true);
}

function book_format_label(string $ext): string {
    $labels = [
        'pdf' => 'PDF', 'epub' => 'EPUB', 'txt' => 'Text',
        'doc' => 'Word (DOC)', 'docx' => 'Word (DOCX)',
        'mobi' => 'Kindle (MOBI)', 'rtf' => 'RTF',
    ];
    return $labels[strtolower($ext)] ?? strtoupper($ext);
}
