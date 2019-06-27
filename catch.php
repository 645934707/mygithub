<?php
set_time_limit(0);
$pdo = new PDO('mysql:host=127.0.0.1;dbname=test','root','123456');
$pdo->query('set names utf8');

while (true) {
    $data = ccurl('https://test.com');
    $preg = "/<div class=\"note-container\">([\\s\\S]*?)<\\/div>/";
    $data = $data['data'];
    $ma = preg_match($preg, $data, $matchs);
    $content = trim($matchs[1]);
    $md5str = md5($content);


//先判断是否存在
    $sqlex = 'select id from mingyan where md5_str="'.$md5str.'"';
    $sth = $pdo->prepare($sqlex);
    $sth->execute();
    $rs = $sth->fetchAll();
    if (empty($rs)) {
        $sql = 'insert into mingyan (`content`, `date_time`, `md5_str`) values (:content, :datetime, :md5str)';
        $sth = $pdo->prepare($sql);
        $sth->bindValue(':content', $content, PDO::PARAM_STR);
        $sth->bindValue(':datetime', date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']), PDO::PARAM_STR);
        $sth->bindValue(':md5str', $md5str, PDO::PARAM_STR);
        $sth->execute();
        $info = $sth->errorInfo();
        $info = '['.date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']).'] '.implode(' ',$info)."\r\n";
        file_put_contents('./err.log', $info, FILE_APPEND);
    }
    sleep(2);
}
