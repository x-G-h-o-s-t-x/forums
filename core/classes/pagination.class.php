<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'pagination.class.php')): // input your class file name here
    header('Location:../../index.php'); // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class pagination {
 
    protected function __construct() { /** Thou shalt not construct that which is unconstructable! */ }
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private $file_name;
    private $rows_per_page = 10;
    private $total_rows = 0;
    private $links_per_page = 5;
    private $append = '';
    private $page = 1;
    private $max_pages = 0;
    private $offset = 0;
    private $results;
    private $count = 0;
    private static $instance;

    // call to start the pagination instance
    public static function init(){
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    // function to get seo data to work with
    public function seo($url) {
        return seo($url);
    }

    // function that does all the magic, creates a list of pages from the results
    public function paginator($query, $bind = array(), $rows_per_page = 10, $links_per_page = 5, $append = '') {
        $this->rows_per_page = (int)$rows_per_page;
        $this->append = $append;
        $this->file_name = sanitize($_SERVER['SCRIPT_NAME']);
            if(isset($_GET['page'])):
                $this->page = intval($_GET['page']);
            endif;
        if(intval($links_per_page) > 0):
            $this->links_per_page = (int)$links_per_page;
        else:
            $this->links_per_page = 5;
        endif;
        db::pdo()->query($query);
            if(!empty(is_array($bind))):
                db::pdo()->bind($bind);
            endif;
        db::pdo()->execute();
            if(db::pdo()->count() > 0):
                $this->total_rows = db::pdo()->count();
                $this->max_pages = ceil($this->total_rows / $this->rows_per_page);
                    if($this->links_per_page > $this->max_pages):
                        $this->links_per_page = $this->max_pages;
                    endif;
                if($this->page > $this->max_pages || $this->page <= 0):
                    $this->page = 1;
                endif;
                $this->offset = $this->rows_per_page * ($this->page-1);
                db::pdo()->query($query." LIMIT {$this->offset}, {$this->rows_per_page}");
                    if(!empty(is_array($bind))):
                        db::pdo()->bind($bind);
                    endif;
                db::pdo()->execute();
                $this->count = db::pdo()->count();
                $this->results = db::pdo()->result();
                return true;
            else:
                return false;
            endif;
    }

    //function to count the results found from query
    public function count() {
        return $this->count;
    }

    //function to display the results found from query
    public function result() {
        return $this->results;
    }

    // function to create the first link
    public function first() {
        $first = '<div class="pagination-first"><div class="pagination-arrow-left"></div><div class="pagination-arrow-left"></div></div>';
            if($this->total_rows == 0):
                return false;
            endif;
        if($this->page == 1):
            return '';
        else:
            return '<a href="'.$this->seo($this->file_name.'?'.$this->append.'page=1').'">'.$first.'</a>';
        endif;
    }

    // function to create the previous link
    public function prev() {
        $prev = '<div class="pagination-arrow-left"></div>';
            if($this->total_rows == 0):
                return false;
            endif;
        if($this->page > 1):
            return '<a href="'.$this->seo($this->file_name.'?'.$this->append.'page='.($this->page - 1)).'">'.$prev.'</a>';
            else:
            return '';
        endif;
    }

    // function to create the next link
    public function next() {
        $next = '<div class="pagination-arrow-right"></div>';
            if($this->total_rows == 0):
                return false;
            endif;
        if($this->page < $this->max_pages):
            return '<a href="'.$this->seo($this->file_name.'?'.$this->append.'page='.($this->page + 1)).'">'.$next.'</a>';
        else:
            return '';
        endif;
    }

    // function to create the last link
    public function last() {
        $last = '<div class="pagination-last"><div class="pagination-arrow-right"></div><div class="pagination-arrow-right"></div></div>';
            if($this->total_rows == 0):
                return false;
            endif;
        if($this->page == $this->max_pages):
            return '';
        else:
            return '<a href="'.$this->seo($this->file_name.'?'.$this->append.'page='.$this->max_pages).'">'.$last.'</a>';
        endif;
    }

    // function that creates a list of links in a numerical order
    public function nav() {
        if($this->total_rows == 0):
            return false;
        endif;
        $collected = ceil($this->page / $this->links_per_page);
        $end = $collected * $this->links_per_page;
            if($end > $this->max_pages):
                $end = $this->max_pages;
            endif;
        $start = $end - $this->links_per_page + 1;
        $links = '';
            if($this->max_pages == 1):
                return '';
            else:
                for($i = $start; $i <= $end; $i ++):
                    if($i == $this->page):
                        $links .= '<span class="pagination_link_active">'.$i.'</span> ';
                    else:
                        $links .= '<a class="pagination_link" href="'.$this->seo($this->file_name.'?'.$this->append.'page='.$i).'">'.$i.'</a> ';
                    endif;
                endfor;
            endif;
        return $links;
    }

    // function that display the pagination links
    public function links() {
        return $this->first().' '.$this->prev().' '.$this->nav().$this->next().' '.$this->last();
    }
}
?>