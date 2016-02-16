<?php
  error_reporting(E_ALL);
  ini_set('display_errors', true);
  header('Content-type: text/plain;charset=utf-8');
  mysql_connect('localhost', '', '');
  mysql_select_db('db');
  mysql_query("SET NAMES utf8;");
  
  /*
    vytahne z db texty, najde v nich odkazy na PDF a prida jim class pdf
    pokud jiz ma odkaz s tridou no-pdf, tak se nic nepridava
  */
  
  $q = mysql_query("SELECT id, culture, annotation, text FROM psyo_article_i18n");
  
  while($r = mysql_fetch_assoc($q))
  {
    $text = mysql_real_escape_string(replace_pdf_links($r['text']));
    $annotation = mysql_real_escape_string(replace_pdf_links($r['annotation']));
    
    $uq = mysql_query("UPDATE psyo_article_i18n SET text = '{$text}', annotation = '{$annotation}' WHERE id = '{$r['id']}' AND culture LIKE '{$r['culture']}'");
    if (!$uq) echo mysql_error()."\n";
  }
  
  
  function replace_pdf_links($text)
  {
    $i = 0;
    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
    if (preg_match_all("/$regexp/siU", $text, $matches))
    {
      if (!empty($matches[2]))
      {
        foreach($matches[0] as $k=>$v)
        {
          if (substr($matches[2][$k], -4)=='.pdf')
          {
            if (strpos($v, 'no-pdf')===false)
            {
              $new = $v;
              if (strpos($new, 'class="'))
                $new = str_replace('class="', 'class="pdf ', $new);
              else
                $new = str_replace('pdf"', 'pdf" class="pdf"', $new);
              
              $text = str_replace($v, $new, $text);
              $i++;
            }              
          }
        }
      }
    }
    var_dump($i);
    return $text;
  }