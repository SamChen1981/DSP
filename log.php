<?php

$fp_win = "./win.json";
$win_str = file_get_contents($fp_win);
$win_obj = json_decode($win_str);
if(!is_array($win_obj)) {
    return null;
}

echo "����ͳ�ƣ�";
echo "<br>";
foreach ($win_obj as $value){
    if(!empty($value)){
        echo "id:  ".$value->adid."    Ͷ������:  ".$value->date."    ���۳ɹ�����������:  ".$value->num."    ���컨�ѣ�Ԫ��:  ".$value->cost/1000;
        echo "<br>";
    }
}

echo "<br>";

$fp_count = "./count.json";
$count_str = file_get_contents($fp_count);
$count_obj = json_decode($count_str);
if(!is_array($count_obj)) {
    return null;
}
echo "���Ͷ��ͳ�ƣ�";
echo "<br>";
foreach ($count_obj as $value){
    $ctr = sprintf("%.4f", 100.0*$value->nclick/$value->nload);
    echo "id:  ".$value->adid."    Ͷ������:  ".$value->date."    �������������:  ".$value->nclick."    �ع�����������:  ".$value->nload."    �����:  ".$ctr."%"."    �ۼƻ��ѣ�Ԫ��:  ".$value->cost/1000;
	echo "<br>";
}

?>
