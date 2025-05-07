<?php
    /**
     *
     * @copyright(c) 2009
     * @author Ledudu
     * 2009-12-31
     */
    $dlang = array('prev' => '上一页', 'nextpage' => '下一页', 'date' => '前,天,昨天,前天,小时,半,分钟,秒,刚才');

    function dhtmlspecialchars($string)
    {
        if (is_array($string))
        {
            foreach ($string as $key => $val)
            {
                $string[$key] = dhtmlspecialchars($val);
            }
        }
        else
        {
            $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', //$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
                str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
        }

        return $string;
    }

    function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = true, $simple = false)
    {
        global $maxpage, $dlang;
        $ajaxtarget = !empty($_GET['ajaxtarget']) ? " ajaxtarget=\"" . dhtmlspecialchars($_GET['ajaxtarget']) . "\" " : '';

        if (defined('IN_ADMINCP'))
        {
            $shownum      = $showkbd = true;
            $lang['prev'] = '&lsaquo;&lsaquo;';
            $lang['next'] = '&rsaquo;&rsaquo;';
        }
        else
        {
            $shownum      = $showkbd = true;
            $lang['prev'] = $GLOBALS['dlang']['prev'];
            $lang['next'] = $GLOBALS['dlang']['nextpage'];
        }

        $multipage = '';
        $mpurl     .= strpos($mpurl, '?') ? '&amp;' : '?';
        $realpages = 1;
        if ($num > $perpage)
        {
            $offset = 2;

            $realpages = @ceil($num / $perpage);
            $pages     = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

            if ($page > $pages)
            {
                $from = 1;
                $to   = $pages;
            }
            else
            {
                $from = $curpage - $offset;
                $to   = $from + $page - 1;
                if ($from < 1)
                {
                    $to   = $curpage + 1 - $from;
                    $from = 1;
                    if ($to - $from < $page)
                    {
                        $to = $page;
                    }
                }
                elseif ($to > $pages)
                {
                    $from = $pages - $page + 1;
                    $to   = $pages;
                }
            }

            $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="' . $mpurl . 'page=1" class="first"' . $ajaxtarget . '>首页</a>' : '') . ($curpage > 1 && !$simple ? '<a href="' . $mpurl . 'page=' . ($curpage - 1) . '" class="prev"' . $ajaxtarget . '>' . $lang['prev'] . '</a>' : '');
            for ($i = $from; $i <= $to; $i++)
            {
                $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' : '<a href="' . $mpurl . 'page=' . $i . ($ajaxtarget && $i == $pages && $autogoto ? '#' : '') . '"' . $ajaxtarget . '>' . $i . '</a>';
            }

            $multipage .= ($to < $pages ? '<a href="' . $mpurl . 'page=' . $pages . '" class="last"' . $ajaxtarget . '>... ' . $realpages . '</a>' : '') . ($curpage < $pages && !$simple ? '<a href="' . $mpurl . 'page=' . ($curpage + 1) . '" class="next"' . $ajaxtarget . '>' . $lang['next'] . '</a>' : '') . ($showkbd && !$simple && $pages > $page && !$ajaxtarget ? '<input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\'' . $mpurl . 'page=\'+this.value; return false;}" />' : '');

            //$multipage = $multipage ? '<div class="pages">'.($shownum && !$simple ? '<em>&nbsp;'.$num.'&nbsp;</em>' : '').$multipage.'</div>' : '';
            $multipage = $multipage ? '<div class="pages">' . ($shownum && !$simple ? '' : '') . $multipage . '</div>' : '';
        }
        $maxpage = $realpages;

        return $multipage;
    }

    function default_css()
    {
        $css = <<<EOF
            <style>
                .pages a,.pages strong{float:left;padding:0 6px;margin-right:2px;height:30px;border:1px solid;line-height:30px;overflow:hidden;text-align: center;border-radius: .25rem;}
                .pages a{border-color:#E6E7E1;background-color:#FFF;color:#09C;width:30px;}
                .pages a:link,.pages a:hover{text-decoration:none;}
                .pages strong{border-color:#09C;background-color:#09C;color:#FFF;font-weight:700; width:30px;}
                .pages a.first{background-repeat:no-repeat; width:60px;}
                .pages a.prev,.pages a.next{background-repeat:no-repeat; width:60px;}
                .pages a.prev{background-position:30% 50%;padding:0;}
                .pages a.next{padding:0;background-position:90% 50%;}
                .pages a:hover{border-color:#09C;}
            </style>
EOF;

        return $css;
    }

?>