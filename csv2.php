<?php
$records = get_csv('./905617DAK9sl9W.csv');

define('angus',TRUE);
require 'api_handler.php';
$api = new wrikeapi();
$access_token = 'eyJ0dCI6InAiLCJhbGciOiJIUzI1NiIsInR2IjoiMSJ9.eyJkIjoie1wiYVwiOjI5NDYyMDEsXCJpXCI6NjM4MDk0OCxcImNcIjo0NjEyNTE1LFwidVwiOjYyOTYzNTYsXCJyXCI6XCJVU1wiLFwic1wiOltcIldcIixcIkZcIixcIklcIixcIlVcIixcIktcIixcIkNcIixcIkFcIixcIkxcIl0sXCJ6XCI6W10sXCJ0XCI6MH0iLCJpYXQiOjE1NjM0NDEzMjZ9.rimp4lgSo9HAr36wlW0NN99hHBZlD4IusTaXHI-0EcI';

foreach ($records as $record){
  $params['title'] = $record['Task Name'];
  //$params['description'] = $record['Task Content'];
  
  $CsvData = str_replace( '\n', "< br >", $record['Task Content'] );
  $params['description'] = str_replace( "< br >", "\n", $CsvData );
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
  if(!empty($record['Start Date']) && !empty($record['Due Date'])){
    $params['dates'] = '{"start":"'.date($record['Start Date'], "Ymd").'","due":"'.date($record['Due Date'], "Ymd").'"}';
  }
  //$task_code = $api->create_tasks($access_token, 'IEACZ5EZI4LGZILT', $params);
  $comment_params['text'] = $params['description'];
  $comment_code = $api->create_comments($access_token, 'IEACZ5EZKQLK74NT', $comment_params);
var_dump($comment_code);
die();
/*

string(1238) "{
  "kind": "tasks",
  "data": [
    {
      "id": "IEACZ5EZKQLK7RQW",
      "accountId": "IEACZ5EZ",
      "title": "入浴剤ページの誤字修正",
      "description": "WORM BATH METHOD : 3 STEPS\\n\\nwarmです。\\n",
      "briefDescription": "WORM BATH METHOD : 3 STEPS\\n\\nwarmです。\\n",
      "parentIds": [
        "IEACZ5EZI4LGZILT"
      ],
      "superParentIds": [],
      "sharedIds": [
        "KX74BPN7",
        "KUAGAEZE"
      ],
      "responsibleIds": [],
      "status": "Completed",
      "importance": "Normal",
      "createdDate": "2019-07-31T13:39:44Z",
      "updatedDate": "2019-07-31T13:39:44Z",
      "completedDate": "2019-07-31T13:39:44Z",
      "dates": {
        "type": "Backlog"
      },
      "scope": "WsTask",
      "authorIds": [
        "KUAGAEZE"
      ],
      "customStatusId": "IEACZ5EZJMAAAAAB",
      "hasAttachments": false,
      "attachmentCount": 0,
      "permalink": "https://www.wrike.com/open.htm?id=380618262",
      "priority": "1b8844008000000000001c00",
      "followedByMe": true,
      "followerIds": [
        "KUAGAEZE"
      ],
      "superTaskIds": [],
      "subTaskIds": [],
      "dependencyIds": [],
      "metadata": [],
      "customFields": []
    }
  ]
}"

*/


var_dump($task_code);
die();
}













/**
 * CSVローダー
 *
 * @param string $csvfile CSVファイルパス
 * @param string $mode `sjis` ならShift-JISでカンマ区切り、 `utf16` ならUTF-16LEでタブ区切りのCSVを読む。'utf8'なら文字コード変換しないでカンマ区切り。
 * @return array ヘッダ列をキーとした配列を返す
 */
function get_csv($csvfile, $mode='utf8')
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
    foreach ($file as $i => $row)
    {
        // 1行目はキーヘッダ行として取り込み
        if($i===0) {
            foreach($row as $j => $col) $colbook[$j] = $col;
            continue;
        }

        // 2行目以降はデータ行として取り込み
        $line = array();
        if($row[17] == 'Development' && $row[16] == 'Team Tasks' && $row[15] == 'BARTH'){
          foreach($colbook as $j=>$col){
            if($colbook[$j] == 'Comments' || $colbook[$j] == 'Attachments'){
              $line[$colbook[$j]] = json_decode(@$row[$j]);
            }else{
              $line[$colbook[$j]] = @$row[$j];
            }
          }
          $records[] = $line;
        }
        //if($i == 20) break;
    }
    return $records;
}
?>
