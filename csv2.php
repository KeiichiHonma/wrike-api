<?php
$is_debug = false;

//$folder_code = 'IEACZ5EZI4LGZILT';//debug
//$folder_code = 'IEACZ5EZI4LG4PAE';//配信
//$folder_code = 'IEACZ5EZI4LDPHUT';//barth brand-web
$folder_code = 'IEACZ5EZI4LH66MG';//sleepdays brand-web

$space = 'Development';
$list = 'Team Tasks';
//$project = 'AW';
//$project = 'BARTH';
$project = 'Sleepdays';


//---------------------------
$assignees = array
(
'haruka-ogawa'=>'KUAGAXEA',
'ako-murata'=>'KUAGAQC6',
'ryo-murakami'=>'KUAGAOKD',
'keiichi-honma'=>'KUAGAEZE',
'risa-shibano'=>'KUAGDRHR',
'saki-goto'=>'KUAGDRJT',
'yuka-uchiumi'=>'KUAGBLCN',
'teppei-yamaguchi'=>'KUAGDKDX',
'kent-matsui'=>'KUAGBRU6',
'yuki-sano'=>'KUAGBK4D',
'shinji-maritani'=>'KUAGAQC6',
'mayumi koshikawa'=>'KUAGCULP',
'toshi-sasaki'=>'KUAGAEZE',
'kano-katagiri'=>'KUAGAQF7',
'Kosuke Nambu'=>'KUAGAEZE',
'hiroshi-hasegawa'=>'KUAGBLVG',
'kazuki-niizeki'=>'KUAGCT47',
'goroide'=>'KUAGAEZE',
'tomoyuki-hirono'=>'KUAGAVOH',
'terumi-ogawa'=>'KUAGBLE4',
'daiki yano'=>'KUAGDD2Y'
);

$records = get_csv('C:\git\wrike-api\905617CHSphizc.csv', $space, $list, $project);

define('angus',TRUE);
require 'api_handler.php';
$api = new wrikeapi();
$access_token = 'eyJ0dCI6InAiLCJhbGciOiJIUzI1NiIsInR2IjoiMSJ9.eyJkIjoie1wiYVwiOjI5NDYyMDEsXCJpXCI6NjM4MDk0OCxcImNcIjo0NjEyNTE1LFwidVwiOjYyOTYzNTYsXCJyXCI6XCJVU1wiLFwic1wiOltcIldcIixcIkZcIixcIklcIixcIlVcIixcIktcIixcIkNcIixcIkFcIixcIkxcIl0sXCJ6XCI6W10sXCJ0XCI6MH0iLCJpYXQiOjE1NjM0NDEzMjZ9.rimp4lgSo9HAr36wlW0NN99hHBZlD4IusTaXHI-0EcI';

$task_codes = array();
foreach ($records as $record){
  $params = array();


  //if($record['Task ID'] != 'z697u') continue;//task指定
  //task
  $params['title'] = $record['Task Name'];
  $params['description'] = str_replace( '\n', "<br>", $record['Task Content'] );
  switch ($record['Status']){
  case 'Open':
    $status = 'Active';
    break;
  case 'in progress':
    $status = 'Active';
    break;
  case 'Closed':
    $status = 'Completed';
    break;
  default:
    $status = 'Active';
    break;
  }
  $params['status'] = $status;
  //subtask
  if(array_key_exists($record['Parent ID'], $task_codes)){
    $params['superTasks'] = '["'.$task_codes[$record['Parent ID']].'"]';
  }
  
  
  if($record['Assignees']){
    $assignee_name = str_replace(array("[","]"), array("","") , $record['Assignees']);
    if(array_key_exists($assignee_name, $assignees)){
      $params['responsibles'] = '["'.$assignees[$assignee_name].'"]';
    }
  }
  
  
  if(!empty($record['Start Date Text']) && !empty($record['Due Date Text'])){
    $start_date = new DateTime($record['Start Date Text']);
    $due_date = new DateTime($record['Due Date Text']);
    $params['dates'] = '{"start":"'.$start_date->format('Y-m-d').'","due":"'.$due_date->format('Y-m-d').'"}';
  }elseif(empty($record['Start Date Text']) && !empty($record['Due Date Text'])){
    $due_date = new DateTime($record['Due Date Text']);
    $params['dates'] = '{"start":"2019-01-01","due":"'.$due_date->format('Y-m-d').'"}';
  }
  if(!$is_debug){
    $task_json = $api->create_tasks($access_token, $folder_code, $params);
    $task_obj = json_decode($task_json);
    $task_code = reset($task_obj->data)->id;
    $task_codes[$record['Task ID']] = $task_code;
    if(is_array($record['Checklists'])){
      foreach ($record['Checklists'] as $checklist){
        $child_params = array();
        $child_params['title'] = $checklist;
        $child_params['superTasks'] = '["'.$task_code.'"]';;
        $api->create_tasks($access_token, $folder_code, $child_params);
      }
    }
  }
  //Attachments///////////////////////////////////////////
  if(!empty($record['Attachments'])){
    foreach ($record['Attachments'] as $attachment){
      if(!$is_debug) $api->attachments_tasks($access_token, $task_code, $attachment->url, $attachment->title);
    }
  }
  
  //comments///////////////////////////////////////////
  if(!empty($record['Comments'])){
    foreach ($record['Comments'] as $comment){
      $comment_params['text'] = '';
      $comment_params['text'] .= 'comment by: '.str_replace('@two2.jp', '', $comment->by).'<br>';
      $comment_date = new DateTime($comment->date);
      $comment_params['text'] .= 'comment date: '.$comment_date->format('Y-m-d H:i:s').'<br><br>';
      $comment_params['text'] .= str_replace( '\n', "<br>", $comment->text);
      if(!$is_debug) $api->create_comments($access_token, $task_code, $comment_params);
    }
  }
}

/**
 * CSVローダー
 *
 * @param string $csvfile CSVファイルパス
 * @param string $mode `sjis` ならShift-JISでカンマ区切り、 `utf16` ならUTF-16LEでタブ区切りのCSVを読む。'utf8'なら文字コード変換しないでカンマ区切り。
 * @return array ヘッダ列をキーとした配列を返す
 */
function get_csv($csvfile, $space, $list, $project, $mode='utf8')
{
    // ファイル存在確認
    if(!file_exists($csvfile)) return false;
 
    // 文字コードを変換しながら読み込めるようにPHPフィルタを定義
         if($mode === 'sjis')  $filter = 'php://filter/read=convert.iconv.cp932%2Futf-8/resource='.$csvfile;
    else if($mode === 'utf16') $filter = 'php://filter/read=convert.iconv.utf-16%2Futf-8/resource='.$csvfile;
    else if($mode === 'utf8')  $filter = $csvfile;
 
    // SplFileObject()を使用してCSVロード
    $file = new SplFileObject($filter);
    if($mode === 'utf16') $file->setCsvControl("\t");
    $file->setFlags(
        SplFileObject::READ_CSV |
        SplFileObject::SKIP_EMPTY |
        SplFileObject::READ_AHEAD
    );
 
    // 各行を処理
    $records = array();
    $errors = array();
    foreach ($file as $i => $row)
    {
        // 1行目はキーヘッダ行として取り込み
        if($i===0) {
            foreach($row as $j => $col) $colbook[$j] = $col;
            continue;
        }

        // 2行目以降はデータ行として取り込み
        $line = array();
        if($row[17] == $space && $row[16] == $list && $row[15] == $project){
          foreach($colbook as $j=>$col){
            if($colbook[$j] == 'Comments' || $colbook[$j] == 'Attachments'){
              $json_result = json_decode(@$row[$j]);
              if(is_null($json_result)){
                $error_string = '';
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        $error_string = ' - No errors';
                    break;
                    case JSON_ERROR_DEPTH:
                        $error_string = ' - Maximum stack depth exceeded';
                    break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $error_string = ' - Underflow or the modes mismatch';
                    break;
                    case JSON_ERROR_CTRL_CHAR:
                        $error_string = ' - Unexpected control character found';
                    break;
                    case JSON_ERROR_SYNTAX:
                        $error_string = ' - Syntax error, malformed JSON';
                    break;
                    case JSON_ERROR_UTF8:
                        $error_string = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                    default:
                        $error_string = ' - Unknown error';
                    break;
                }
                echo '"'.$row[0].'","'.$colbook[$j].'","'.$error_string.'"'."<br>";
                $errors[$row[0]][$colbook[$j]] = $error_string;
              }else{
                $json_result = array_reverse($json_result);//逆
                $line[$colbook[$j]] = $json_result;
              }
            }elseif($colbook[$j] == 'Checklists'){
                $json_result = json_decode(@$row[$j]);
                if(isset($json_result->Checklist)){
                  $line[$colbook[$j]] = $json_result->Checklist;
                }else{
                  $line[$colbook[$j]] = $json_result;
                }
                
            }else{
              $line[$colbook[$j]] = @$row[$j];
            }
          }
          $records[] = $line;
        }
        //if($i == 20) break;
    }
    if(!empty($errors)) echo '---------------------------------<br>';
    //return empty($errors) ? $records : $errors;
    return $records;
}
?>
