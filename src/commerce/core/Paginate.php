<?php
namespace Commerce\Core;

class Paginate
{
    protected $has_previous = true;

    protected $previous_page;

    
    protected $has_next = true;
    
    protected $next_page;

    public $page_number;

    public $count;

    public $show_page_num;

    protected $present;

    protected $page_start = 0;

    protected $page_end = 0;

    protected $per_page;

    public $current_class = "current";
    public $current_color = "#ff0000";
    public $prev_class = "prev";
    public $next_class = "next";
    public $open_tag = "div";
    public $page_tag = "";
    public $use_prev = true;
    public $use_next = true;
    public $separater = ' | ';
    public $current_tag = 'a';

    public $prevLabel = "&lt;&lt;前へ";

    public $nextLabel = "次へ&gt;&gt;";
    public $pager_id = "pager";
    public $pager_class = "pager";
    public $pager_align = "center";

    public function __construct($count, $per_page, $present = 1, $pagenum = 20)
    {
        if ($count === 0) {
            $this->count = 0;
            return null;
        }
        //Set page status
        $this->per_page = $per_page;
        $this->present = ($present > 1 && $present !== null) ? (int)$present : 1;
        $this->show_page_num = $pagenum;
        $this->previous_page = $this->present - 1;
        $this->next_page = $this->present + 1;
        $this->count = (int)$count;

        //Determine the number of pages
        if ($count % $per_page === 0) {
            $this->page_number = (int)floor($count / $per_page);
        } else {
            $this->page_number = (int)floor($count / $per_page) + 1;
        }

        //Set if there is a previous or next page
        if ($this->page_number === 1) {
            $this->has_previous = false;
            $this->has_next = false;
        } else {
            if ($present <= 1) {
                $this->has_previous = false;
            }
            if ($this->page_number == $present) {
                $this->has_next = false;
            }
        }

        //Set the center of the pager
        $page_center = (int)($this->show_page_num / 2);
        if ($this->show_page_num % 2 == 0) {
            $add = -1;
        } else {
            $add = 0;
        }

        //If the page count is greater than the number of pages displayed, set max to the number of pages displayed.
        //Otherwise max to page count
        if ($this->page_number >= $this->show_page_num) {
            $this->page_end = $this->show_page_num;
        } else {
            $this->page_end = $this->page_number;
        }

        //Display start page number setting, thereby changing display end
        if ($this->present <= $page_center) {
            $this->page_start = 1;
        } else if ($this->present + $page_center > $this->page_number) {
            $this->page_start = $this->page_number - $this->show_page_num;
            $this->page_end = $this->page_number;
        } else {
            $this->page_end = $this->present + $page_center;
            $this->page_start = $this->present - ($page_center + $add);
        }
    }

    /**
     * Is there a previous page
     * @access    public
     * @return    bool
     */
    public function hasPreviou()
    {
        return $this->has_previous;
    }

    /**
     * Is there the next page
     * @access    public
     * @return    bool
     */
    public function hasNext()
    {
        return $this->has_next;
    }

    /**
     * Returns the number of pages
     * @access    public
     * @return    int
     */
    public function getPageNumber()
    {
        return $this->page_number;
    }

    public function getCurrent()
    {
        return $this->present;
    }

    public function getPrevious()
    {
        return $this->previous_page;
    }

    public function getNext()
    {
        return $this->next_page;
    }

    public function getCount()
    {
        return $this->count;
    }

    /**
     * Show pager
     * @param $url
     * @param $admin
     * @return string
     */
    public function show($url = "")
    {

        //If the count number is 0, nothing is displayed
        if ($this->count === 0) {
            return "";
        }
        $ua = Useragent::getInstance();
        //Initialization of in-method editing
        $pageNumber = $this->page_start;
        $string = "<{$this->open_tag} class=\"{$this->pager_class}\" id=\"{$this->pager_id}\">";

        //Parameter connection
        $context = Config::getEnv('CONTEXT');
        $url = (!empty($context)) ? $context . $url : $url;
        $amp = (preg_match('/\?/', $url)) ? "&" : "?";

        $pagelinks = [];

        //Generate tag if there is a previous page
        if ($this->has_previous && $this->use_prev) {
            $pagelinks[] = "<a href=\"{$url}{$amp}page={$this->previous_page}\" class=\"{$this->prev_class}\">{$this->prevLabel}</a>";
        }
        //Generate page number links
        while ($pageNumber <= $this->page_end) {
            if ($pageNumber > 0) {
                $page_link = (!empty($this->page_tag)) ? "<{$this->page_tag}>" : "";
                if ($pageNumber == $this->present) {
                    $page_link .= "<{$this->current_tag} class=\"{$this->current_class}\">" . $pageNumber . "</{$this->current_tag}>";
                } else {
                    $page_link .= "<a href=\"{$url}{$amp}page={$pageNumber}\">" . $pageNumber . "</a>";
                }
                $page_link .= (!empty($this->page_tag)) ? "</{$this->page_tag}>" : "";
                $pagelinks[] = $page_link;
            }
            $pageNumber++;
        }
        //Generate tag if there is next page
        if ($this->has_next && $this->use_next) {
            $pagelinks[] = "<a href=\"{$url}{$amp}page={$this->next_page}\" class=\"{$this->next_class}\">{$this->nextLabel}</a>";
        }
        $string .= implode($this->separater, $pagelinks);
        $string .= "</{$this->open_tag}>\n";
        return $string;
    }
}