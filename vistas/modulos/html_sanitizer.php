<?php

function sanitizeQuillHTML($html) {
    if (empty($html)) {
        return '';
    }
    
    $allowed_tags = '<p><br><strong><b><em><i><u><s><strike><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><a><span><div><sub><sup>';
    
    $clean_html = strip_tags($html, $allowed_tags);
    
    $clean_html = preg_replace('/<a[^>]*href="javascript:[^"]*"[^>]*>/i', '<span>', $clean_html);
    $clean_html = preg_replace('/<a[^>]*onclick[^>]*>/i', '<a>', $clean_html);
    $clean_html = preg_replace('/<a[^>]*onmouseover[^>]*>/i', '<a>', $clean_html);
    
    $clean_html = preg_replace('/\son\w+="[^"]*"/i', '', $clean_html);
    
    return $clean_html;
}


function getPlainTextFromQuill($html) {
    return strip_tags($html);
}
?>
