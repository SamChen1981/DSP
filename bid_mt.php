<?php

/*
 * ����bid request ��Ϣ
 * */
$req_str = file_get_contents("php://input");
$req_obj = json_decode($req_str);
if(!is_object($req_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * �ж�������Ϣ����
 * */
switch ($req_obj->request_type){
    case 0:
		//����������Ӧ����
        break;
    case 1:
		//������������������Ӧ���ģ������յ����
        break;
    case 2:
		//�������ԣ����ؿ���Ӧ����
		header('HTTP/1.1 204 No Content');
		return null;
        break;
	default:
		break;
}  

/*
 * ��ȡad.jsonͶ�Ź�����
 * */
$ads_str = file_get_contents("./ad_mt.json");
$ads_obj = json_decode($ads_str);
if(!is_array($ads_obj)) {
    header('HTTP/1.1 204 No Content');
    return null;
}

/*
 * ƥ��request��Ϣ��Ͷ�Ų����е�device
 * */
function parse_device($r, $b){
    /*ƥ��UA*/
    if(!isset($r->ua)){
        return false;
    }
	foreach ($b->ua as $value){
		if(stristr($r->ua,$value)){
			continue;
		}
	}

    /*ƥ��OS*/
    if(!isset($r->os)){
        return false;
    }
	foreach ($b->os as $value){
		if(stristr($r->os,$value)){
			continue;
		}
	}

    /*ƥ��devicetype��PC�ͻ���չ�ֹ��*/
    if(!isset($r->connectiontype) || !in_array($r->connectiontype, $b->connectiontype)){
        return false;
    }
    return true;
}

/*
 * ƥ��request��Ϣ��Ͷ�Ų����е�video
 * */
function parse_video($r, $b){
    
	/*ƥ��video ID�����б� */
/*
	$result = array_intersect($r->item_ids, $b->item_ids);
    if(empty($result)){
        return false;
    }
*/	
    return true;
}

/*
 * ����bid request imp����Ͷ�Ų���
 * */
function parse_imp($r, $b){
    foreach ($r as $value){
        if($value->width!=$b->width || $value->height!=$b->height ){      
            continue;
        }
		$b->min_cpm_price = $value->min_cpm_price+$b->bidceiling;
		if($b->min_cpm_price>$b->range[1]||$b->min_cpm_price<$b->range[0]){      
            continue;
        }
		if(!isset($value->location) || !in_array($value->location, $b->location)){
			continue;
		}
		if(!isset($value->ctype) || !in_array($b->ctype, $value->ctype)){
			continue;
		}
		//�������͵Ĺ��
		if($b->ctype==2){
			if($value->playtime!=$b->playtime){      
				continue;
			}
			if(!isset($value->order) || !in_array($value->order, $b->order)){
				continue;
			}
		}
		return true;
    }
    return false;
}

/*
 * ��������زģ��жϹ��λ�͹���ز��Ƿ�ƥ��
 * */
function parse_ad($req, $bid){

   	if(!parse_device($req->device, $bid->device)){
		return null;
	}
	
   	if(!parse_video($req->video, $bid->video)){
		return null;
	}
	
    if(!parse_imp($req->imp, $bid->imp)){
        return null;
    }
	
    return $bid;
}

/*
 * ȫ�ֱ���
 * */
$bid_result = null;

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
function generate_response($req, $bid){
	
	$bid->resp->version = $req->version;
	$bid->resp->bid = $req->bid;
	$bid->resp->ads[0]->price = $bid->imp->min_cpm_price;
	$bid->resp->ads[0]->duration = $bid->imp->playtime;
	$bid->resp->ads[0]->ctype = $bid->imp->ctype;
	$bid->resp->ads[0]->width = $bid->imp->width;
	$bid->resp->ads[0]->height = $bid->imp->height;
}
generate_response($req_obj, $bid_result);

/*
 * ����bid response��Ϣhttpͷ
 * */
header('Content-type: application/json');
header('Connection: Keep-Alive');

/*
 * ��ʼͶ�š�����
 * */
$resp_str = json_encode($bid_result->resp);

echo $resp_str;
?>
