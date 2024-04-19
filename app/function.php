<?php
use app\Jwt;
/**
 *  @param   string  $key  [加密key]
 *  @param   string  $time  [过期时间s]
 *  @param   array  $data  [携带的数据]
 * 
 *  @return  string         [返回jwt数据]
 * 
 */
function jwt_get(string $key="aaa",int $time=3600,array $data=[]) :string
{
    return Jwt::init()->setKey($key)->setExpireTime($time)->setData($data)->create();
}
/**
 *  @param   string  $key  [加密key]
 *  @param   string  $jwt  [jwt字符串]
 *
 *  @return  string         [返回jwt中数据数组]
 * 
 */
function jwt_arr(string $key='aaa',string $jwt='') :array
{
    return Jwt::init()->setKey($key)->setJwt($jwt)->verify();
}
/**
 * 数组转XML
 *
 * @param   string  $str  [$str description]
 *
 * @return  array         [return description]
 */
function arr_to_xml(array $arr,string $root='root'): string
{
    $xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?>
    <$root></$root>");
//数组转XML
    if (!function_exists('arrayToXml')) {
        function arrayToXml($obj, $array)
        {
            foreach ($array as $k => $v)
            {
                if(is_numeric($k))
                    $k = 'item' . $k;
                if(is_array($v)){
                    $node = $obj->addChild($k);
                    arrayToXml($node, $v);
                }else{
                    $obj->addChild($k, htmlspecialchars($v));
                }
            }
        }
    }
    arrayToXml($xml,$arr);
    return $xml->asXml();
}
/**
 * XML转数组
 *
 * @param   string  $str  [$str description]
 *
 * @return  array         [return description]
 */
function xml_to_arr(string $str): array
{
    return (array)simplexml_load_string($str);
}
/**
 * 通过身份证号判别男女
 *
 * @param   string  $id_car   [身份证号]
 */
function id_car_sex($id_car)
{
    if ((substr($id_car, -2, 1) % 2 == 0)) return '女';
    return '男';
}
/**
 * 通过身份证号取出生年月日
 *
 * @param   string  $id_car   [身份证号]
 */
function id_car_birth($idCard,$action=3)
{
     // 定义身份证号的正则表达式
    $pattern = '/^(\d{6})(\d{4})(\d{2})(\d{2})\d{2}(\d)(\d|X)$/';

    // 使用正则表达式匹配身份证号
    if (preg_match($pattern, $idCard, $matches)) {
        // 提取出生年月日信息
        $birthYear = $matches[2];
        $birthMonth = $matches[3];
        $birthDay = $matches[4];
        switch ($action) {
            case 1:
                return $birthYear;
                break;
            case 2:
                return $birthYear . '-' . $birthMonth;
                break;
            case 3:
                return $birthYear . '-' . $birthMonth . '-' . $birthDay;
                break;
            default:
                return $birthYear . '-' . $birthMonth;
                break;
        }
        // 返回结果
        
    } else {
        // 匹配失败，返回空字符串或者其他标识
        return '无';
    }
}
/**
 * 错误返回的信息
 *
 * @param   string  $msg   [$msg description]
 * @param   int     $code  [$code description]
 *
 * @return  array           [return description]
 */
define('EA_DE_ARR', [11 => '获取失败', 21 => '添加失败', 31 => '修改失败', 41 => '删除失败']);
function ea($msg = '错误信息', $code = 400, $datas = []): array
{
    if (is_array($msg)) {
        return ['msg' => '错误', 'code' => 400, 'data' => $msg];
    }
    $data = ['msg' => $msg, 'code' => $code, 'data' => $datas];
    if (is_int($msg) && isset(EA_DE_ARR[$msg])) {
        $data['msg'] = EA_DE_ARR[$msg];
    }
    if (is_array($code)) {
        $data['data'] = $code;
        $data['code'] = 400;
    }
    return $data;
}
/**
 * 正确的返回的信息
 *
 * @param   string  $msg    [$msg description]
 * @param   int     $code   [$code description]
 * @param   array   $datas  [$datas description]
 *
 * @return  array            [return description]
 */
define('SA_DE_ARR', [11 => '获取成功', 21 => '添加成功', 31 => '修改成功', 41 => '删除成功']);
function sa($msg = '成功信息', $code = 200, $datas = ''): array
{
    if (is_array($msg)) {
        return ['msg' => '成功', 'code' => 200, 'data' => $msg];
    }
    $data = ['msg' => $msg, 'code' => $code, 'data' => $datas];
    if (is_int($msg) && isset(SA_DE_ARR[$msg])) {
        $data['msg'] = SA_DE_ARR[$msg];
    }
    if (is_array($code)) {
        $data['data'] = $code;
        $data['code'] = 200;
    }
    return $data;
}
/**
 * 文本文件转数组
 *
 * @param   string  $str  [$str description]
 *
 * @return  array         [return description]
 */
function txt_to_arr(string $str): array
{
    $arr = explode(PHP_EOL, $str);
    $new_arr = [];
    array_walk($arr, function ($v) use (&$new_arr) {
        if ($v != "") {
            array_push($new_arr, explode("\t", $v));
        }
    });
    unset($new_arr[0]);
    return $new_arr;
}
/**
 * 检验身份证号的合性，使用身份号国家标准算法
 *
 * @param   string  $id_car  [$id_car description]
 *
 * @return  [type]           [return description]
 */
function person_id_car_validate(string $id_car)
{
    // 校验身份证长度和格式
    if (!preg_match('/^\d{17}[\dX]$/', $id_car)) {
        return false;
    }
    // 校验身份证最后一位校验码
    $idCardWi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
    $idCardCheckDigit = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
    $sigma = 0;
    for ($i = 0; $i < 17; $i++) {
        $sigma += intval($id_car[$i]) * $idCardWi[$i];
    }
    $mod = $sigma % 11;
    $checkDigit = $idCardCheckDigit[$mod];
    if ($checkDigit != $id_car[17]) {
        return false;
    }
    return true;
}
/**
 * 检验统一信用代码的合性，使用身份号国家标准算法
 *
 * @return  [type]  [return description]
 */
function unit_id_car_validate($id_car)
{
    // 校验统一信用代码长度和格式
    if (!preg_match('/^[0-9A-Z]{18}$/', $id_car)) {
        return false;
    }
    // 校验统一信用代码校验位
    $creditCodeWi = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];
    $creditCodeCheckDigit = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'W', 'X', 'Y'];
    $sigma = 0;
    for ($i = 0; $i < 17; $i++) {
        $codeChar = $id_car[$i];
        $codeValue = array_search($codeChar, $creditCodeCheckDigit);
        $sigma += $codeValue * $creditCodeWi[$i];
    }
    $mod = $sigma % 31;
    $checkDigit = $creditCodeCheckDigit[$mod];
    if ($checkDigit != $id_car[17]) {
        return false;
    }
    return true;
}