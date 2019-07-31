<?php
  //$handle = @fopen("./905617DAK9sl9W.csv", "r");
$csv_class = new CSV();
$row = 0;
$csv_datas = array();
$csv_keys = array();
if (($handle = @fopen("./905617DAK9sl9W.csv", "r")) !== FALSE) {
    //while (($data = fgetcsv($handle))) {
    while ($line = fgets($handle)) {
      $data = str_getcsv($line, ",", '"');
      if($row == 0 || $data[17] == 'Development'){
        foreach ($data as $index => $value) {
            if($row == 0) {
              $csv_keys[$index] = $value;
            }else{
              if($index == 22){
var_dump($data);
die();
              }elseif($csv_keys[$index] == 'Comments'){
                //var_dump(json_decode($value));
                $csv_datas[$row][$csv_keys[$index]] = json_decode($value);
              }elseif ($csv_keys[$index] == 'Attachments'){
                $csv_datas[$row][$csv_keys[$index]] = json_decode($value);
              }else{
                $csv_datas[$row][$csv_keys[$index]] = $value;
              }
              
            }
        }
        if($row == 10) break;
        $row++;
      }
    }
    fclose($handle);
}
var_dump($csv_datas);
die();

  //$data = _fgetcsv_reg($handle);
  fclose($handle);

class CSV {
  public $csv_datas = array();
  function _fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
      $d = preg_quote($d);
      $e = preg_quote($e);
      $_line = "";
      $eof = false; // Added for PHP Warning.
      while ( $eof != true ) {
        $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
        $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
        if ($itemcnt % 2 == 0) $eof = true;
      }
      $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
      $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';

      preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);

      $_csv_data = $_csv_matches[1];

      for ( $_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++ ) {
        $_csv_data[$_csv_i] = preg_replace('/^'.$e.'(.*)'.$e.'$/s', '$1', $_csv_data[$_csv_i]);
        $_csv_data[$_csv_i] = str_replace($e.$e, $e, $_csv_data[$_csv_i]);
      }
//var_dump($_csv_data);
die();
      //return empty($_line) ? false : $_csv_data;
      $this->csv_datas[] = $_csv_data;
      return empty($_line) ? false : true;
  }
}
?>
