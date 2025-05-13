<?php
/**
 * Sanitize input data
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific URL
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Format currency
 * @param float $amount The amount to format
 * @param string $currency The currency code
 * @return string The formatted amount
 */
function formatCurrency($amount, $currency = 'USD') {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Format date
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Get status badge HTML
 * @param string $status The status to format
 * @return string The HTML badge
 */
function getStatusBadge($status) {
    $badges = [
        'Active' => 'success',
        'Inactive' => 'danger',
        'Pending' => 'warning',
        'Paid' => 'success',
        'Cancelled' => 'danger'
    ];
    
    $color = isset($badges[$status]) ? $badges[$status] : 'secondary';
    return '<span class="badge bg-' . $color . '">' . $status . '</span>';
} 