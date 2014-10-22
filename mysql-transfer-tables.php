<?php
  header('Content-type: text/plain;charset=UTF-8');
  
  // connect to db
  mysql_connect('localhost', '', '');
  mysql_select_db('');
  mysql_query("SET NAMES 'utf8'");
  
  echo "START TRANSACTION;\n\n";
  
  $files = array();
  
  // 1. table - table_xy
  
  // psyo_article
  $items = _mysql_select("SELECT * FROM table_xy");
  foreach($items as $item)
  {
    // do some stuff
    $item['id'] += 500; // id incrase +500
    
    // find links to images, pdf etc in text fields
    _export_links(array($item['text'], $item['description']), $files);
    
    _mysql_insert('table_xy', $item);
  }  

  // 2. table ...  
  // 3. table ...    


  // commit
  echo "COMMIT;\n\n\n\n";  


  // generate sh for file transfer
  echo "#!/bin/sh\n";
  foreach($files as $file)
  {
    
    echo "curl http://currentweb.com{$file} --create-dirs -o .{$file}\n";
    //echo "chown user:group .{$file}\n";
    //echo "chmod 0666 .{$file}\n";
  }
  
  
  function _mysql_insert($table, $array)
  {
    foreach($array as $k=>$v)
    {
        if (preg_match('/^([0-9]+)$/', $v)) $v = (int)$v;
        $array[$k] = var_export($v, 1);
    }
    $sql = "INSERT INTO {$table} (".join(', ', array_keys($array)).") VALUES (".join(', ', $array).");";
    
    echo $sql."\n";
  }
  
  function _mysql_select($sql, $merge=false)
  {
    $out = array();
    $q = mysql_query($sql);
    while($r=mysql_fetch_assoc($q))
    {
      if ($merge)
      {
        foreach($r as $v)
        {
          $out[] = $v;
        }
      }  
      else
        $out[] = $r;
    }
    return $out;
  }
  
  function _export_links($texts, &$files)
  {
    $links = array();
    if (!is_array($texts)) $texts = array($texts);
    
    foreach($texts as $text)
    {
      if (preg_match_all('/(["\']{1})\/(([a-zA-Z0-9_\-\/\:\.]+)\.([a-zA-Z]{3,4}))(["\']{1})/', $text, $matches))
      {
        foreach($matches[2] as $path) 
        {
          $links[] = '/'.$path;
          $files[] = '/'.$path;
        }
      }
    }
    
    $files = array_unique($files);
    
    return $links;
  }
?>