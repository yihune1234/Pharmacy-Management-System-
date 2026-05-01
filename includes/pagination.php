<?php
/**
 * Pagination Class
 * Handles pagination for list views with configurable page size
 * 
 * Usage:
 * $pagination = new Pagination($total_records, $page, $limit);
 * $offset = $pagination->get_offset();
 * $limit = $pagination->get_limit();
 * $html = $pagination->generate_html('view.php');
 */

class Pagination {
    private $total_records;
    private $current_page;
    private $limit;
    private $total_pages;
    
    /**
     * Constructor
     * 
     * @param int $total_records Total number of records
     * @param int $current_page Current page number (default: 1)
     * @param int $limit Records per page (default: 10)
     */
    public function __construct($total_records, $current_page = 1, $limit = 10) {
        $this->total_records = max(0, (int)$total_records);
        $this->current_page = max(1, (int)$current_page);
        $this->limit = max(1, (int)$limit);
        
        // Calculate total pages
        $this->total_pages = ceil($this->total_records / $this->limit);
        
        // Ensure current page doesn't exceed total pages
        if ($this->current_page > $this->total_pages && $this->total_pages > 0) {
            $this->current_page = $this->total_pages;
        }
    }
    
    /**
     * Get current page number
     * 
     * @return int Current page
     */
    public function get_page() {
        return $this->current_page;
    }
    
    /**
     * Get records per page limit
     * 
     * @return int Limit
     */
    public function get_limit() {
        return $this->limit;
    }
    
    /**
     * Get offset for SQL LIMIT clause
     * 
     * @return int Offset
     */
    public function get_offset() {
        return ($this->current_page - 1) * $this->limit;
    }
    
    /**
     * Get total number of pages
     * 
     * @return int Total pages
     */
    public function get_total_pages() {
        return $this->total_pages;
    }
    
    /**
     * Get total number of records
     * 
     * @return int Total records
     */
    public function get_total_records() {
        return $this->total_records;
    }
    
    /**
     * Check if there is a previous page
     * 
     * @return bool True if previous page exists
     */
    public function has_previous() {
        return $this->current_page > 1;
    }
    
    /**
     * Check if there is a next page
     * 
     * @return bool True if next page exists
     */
    public function has_next() {
        return $this->current_page < $this->total_pages;
    }
    
    /**
     * Get previous page number
     * 
     * @return int Previous page number or 1
     */
    public function get_previous_page() {
        return max(1, $this->current_page - 1);
    }
    
    /**
     * Get next page number
     * 
     * @return int Next page number or total pages
     */
    public function get_next_page() {
        return min($this->total_pages, $this->current_page + 1);
    }
    
    /**
     * Generate pagination HTML
     * 
     * @param string $base_url Base URL for pagination links
     * @param string $query_param Query parameter name (default: 'page')
     * @return string HTML pagination markup
     */
    public function generate_html($base_url, $query_param = 'page') {
        if ($this->total_pages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination-container">';
        $html .= '<nav class="pagination" role="navigation" aria-label="Pagination">';
        $html .= '<ul class="pagination-list">';
        
        // Previous button
        if ($this->has_previous()) {
            $prev_url = $this->build_url($base_url, $this->get_previous_page(), $query_param);
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars($prev_url) . '" class="pagination-link" aria-label="Previous page">';
            $html .= '<i class="fas fa-chevron-left"></i> Previous';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="pagination-item disabled">';
            $html .= '<span class="pagination-link"><i class="fas fa-chevron-left"></i> Previous</span>';
            $html .= '</li>';
        }
        
        // Page numbers
        $start_page = max(1, $this->current_page - 2);
        $end_page = min($this->total_pages, $this->current_page + 2);
        
        if ($start_page > 1) {
            $url = $this->build_url($base_url, 1, $query_param);
            $html .= '<li class="pagination-item"><a href="' . htmlspecialchars($url) . '" class="pagination-link">1</a></li>';
            if ($start_page > 2) {
                $html .= '<li class="pagination-item disabled"><span class="pagination-link">...</span></li>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $this->current_page) {
                $html .= '<li class="pagination-item active">';
                $html .= '<span class="pagination-link" aria-current="page">' . $i . '</span>';
                $html .= '</li>';
            } else {
                $url = $this->build_url($base_url, $i, $query_param);
                $html .= '<li class="pagination-item">';
                $html .= '<a href="' . htmlspecialchars($url) . '" class="pagination-link">' . $i . '</a>';
                $html .= '</li>';
            }
        }
        
        if ($end_page < $this->total_pages) {
            if ($end_page < $this->total_pages - 1) {
                $html .= '<li class="pagination-item disabled"><span class="pagination-link">...</span></li>';
            }
            $url = $this->build_url($base_url, $this->total_pages, $query_param);
            $html .= '<li class="pagination-item"><a href="' . htmlspecialchars($url) . '" class="pagination-link">' . $this->total_pages . '</a></li>';
        }
        
        // Next button
        if ($this->has_next()) {
            $next_url = $this->build_url($base_url, $this->get_next_page(), $query_param);
            $html .= '<li class="pagination-item">';
            $html .= '<a href="' . htmlspecialchars($next_url) . '" class="pagination-link" aria-label="Next page">';
            $html .= 'Next <i class="fas fa-chevron-right"></i>';
            $html .= '</a></li>';
        } else {
            $html .= '<li class="pagination-item disabled">';
            $html .= '<span class="pagination-link">Next <i class="fas fa-chevron-right"></i></span>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        // Pagination info
        $start_record = ($this->current_page - 1) * $this->limit + 1;
        $end_record = min($this->current_page * $this->limit, $this->total_records);
        $html .= '<div class="pagination-info">';
        $html .= 'Showing ' . $start_record . ' to ' . $end_record . ' of ' . $this->total_records . ' records';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Build URL with page parameter
     * 
     * @param string $base_url Base URL
     * @param int $page Page number
     * @param string $query_param Query parameter name
     * @return string Complete URL
     */
    private function build_url($base_url, $page, $query_param) {
        $separator = (strpos($base_url, '?') === false) ? '?' : '&';
        return $base_url . $separator . $query_param . '=' . $page;
    }
    
    /**
     * Get pagination info as array
     * 
     * @return array Pagination information
     */
    public function get_info() {
        return [
            'current_page' => $this->current_page,
            'total_pages' => $this->total_pages,
            'total_records' => $this->total_records,
            'limit' => $this->limit,
            'offset' => $this->get_offset(),
            'has_previous' => $this->has_previous(),
            'has_next' => $this->has_next(),
            'start_record' => ($this->current_page - 1) * $this->limit + 1,
            'end_record' => min($this->current_page * $this->limit, $this->total_records)
        ];
    }
}

?>
