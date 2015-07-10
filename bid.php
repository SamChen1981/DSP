<?php

/*
 * ȫ�ֱ���
 * */
$bid_price = 0;
$bid_result = null;

/*
 * ����bid request ��Ϣ
 * */
$req_str = file_get_contents("php://input");
$req_obj = json_decode($req_str);
if(!is_object($req_obj) || !isset($req_obj->id)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * ����bid response��Ϣ����
 * */
$resp_str = '{
 "id": "",
 "seatbid": [
 {
 "bid": [
 {
 "id": "",
 "impid": "1",
 "price": 0,
 "cmflag": 0,
 "adid": "",
 "nurl": "",
 "crid": "",
 "fmt": 0,
 "cat": [],
 "adomain": [],
 "adm": ""
 }
 ]
 }
 ],
 "bidid": "",
 "cur": "CNY"
}';
$resp_obj = json_decode($resp_str);
if(!is_object($resp_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * ��ȡͶ�Ź����Ϣ
 * */
$ads_str = file_get_contents("./ad.json");
$ads_obj = json_decode($ads_str);
if(!is_array($ads_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * ����bid request site����Ͷ�Ų���
 * */
function parse_site($r, $b){
    /*ƥ����վ�����������*/
    $result = array_intersect($r->sectioncat, $b->sectioncat);
    if(empty($result)){
        return false;
    }
    
    return true;
}

/*
 * ����bid request device����Ͷ�Ų���
 * */
function parse_device($r, $b){
    /*ƥ����վ����UA*/
    if(!isset($r->ua) || !stristr($r->ua,$b->ua)){
        return false;
    }

    /*ƥ��js,֧��JS�Ŀͻ���չ�ֹ��*/
    if(!isset($r->js) || ($r->js!=$b->js)){
        return false;
    }

    /*ƥ��devicetype��PC�ͻ���չ�ֹ��*/
    if(!isset($r->devicetype) || ($r->devicetype!=$b->devicetype)){
        return false;
    }
    return true;
}

/*
 * ����bid request imp����Ͷ�Ų���
 * */
function parse_imp($r, $b){
    foreach ($r as $value){
        switch ($b->type){
            case "banner":
                if(isset($value->banner) && is_object($value->banner)){
                    $banner = $value->banner;
                    /*���ߴ�*/
                    if($banner->w==$b->w && $banner->h==$b->h){
                        $GLOBALS['bid_price'] = $value->bidfloor;
                        return true;
                    }
                }
                break;
            case "video":
                break;
        }  
    }
    return false;
}

/*
 * ��������زģ��жϹ��λ�͹���ز��Ƿ�ƥ��
 * */
function parse_ad($req, $bid){

    if(!isset($req->site) || !is_object($req->site) || !isset($bid->site) || !is_object($bid->site) || !parse_site($req->site, $bid->site)){
        return null;
    }
   
    if(!isset($req->device) || !is_object($req->device) || !parse_device($req->device, $bid->device)){
        return null;
    }
  
    if(!isset($req->imp) || !is_array($req->imp) || !parse_imp($req->imp, $bid->imp)){
        return null;
    }
   
    return $bid;
}

foreach ($ads_obj as $ad_obj){
    $result = parse_ad($req_obj, $ad_obj);
    if(!empty($result)){
        $GLOBALS['bid_result'] = $result; 
        break;
    }
}
if(empty($bid_result)){
    header('HTTP/1.1 204 No Content');
    return null;    
}

/*
 * ����response�زĹ���ز�
 * */
function generate_response($resp, $req, $bid){
    $resp->id = (string) $req->id;
    $resp_bid = $resp->seatbid[0]->bid[0];
    $resp_bid->id = $bid->id;
    $resp_bid->cmflag = $bid->cmflag;
    $resp_bid->adid = $bid->adid;
    $resp_bid->nurl = $bid->nurl;
    $resp_bid->crid = $bid->crid;
    $resp_bid->fmt = $bid->fmt;
    $resp_bid->cat = $bid->cat;
    $resp_bid->adomain = $bid->adomain;
    $resp_bid->adm = $bid->adm;
	//�������յľ���
    $resp_bid->price = $GLOBALS['bid_price']+rand(1,50)/100;
    if($resp_bid->price>$bid->bidceiling){
        header('HTTP/1.1 204 No Content');
        return null;
    }
	//����Ͷ��ʱ��
	if((date('H')<$bid->dtime[0])||(date('H')>$bid->dtime[1]))
	{
		header('HTTP/1.1 204 No Content');
        return null;
	}
    $resp->bidid = $bid->bidid;
}
generate_response($resp_obj, $req_obj, $bid_result);

/*
 * ��ȡͶ�����ݱ�������Ϣ,�ж��Ƿ񳬳�ÿ�����õ��ܶ�
 * */
$wins_str = file_get_contents("./win.json");
$wins_obj = json_decode($wins_str);
if(!is_array($wins_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}
foreach ($wins_obj as $win_obj){
	if(($win_obj->adid==$result->adid)&&($win_obj->date==date('y-m-d',time())))
	{
		if($win_obj->cost+$resp_obj->seatbid[0]->bid[0]->price>$bid_result->cost)
		{		
			header('HTTP/1.1 204 No Content');
			return null;
		}
	}
}

/*
 * ����bid response��Ϣhttpͷ
 * */
header('Content-type: application/json');
header('Connection: Keep-Alive');

/*
 * ��ʼͶ�š�����
 * */
$resp_str = json_encode($resp_obj);

echo $resp_str;
?>
