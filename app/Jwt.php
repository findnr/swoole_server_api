<?php 
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-28 09:39:38
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-03-28 10:28:18
 * @FilePath: \swoole_http_api_xiehui\app\Jwt.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app;

class Jwt
{
    private $key;
    private $expireTime;
    private $data;
    private $jwt;
    
    public static function init(){
        return new Jwt();
    }

    public function setKey(string $key){
        $this->key = $key;
        return $this;
    }
    public function setExpireTime(int $time)
    {
        $this->expireTime = $time;
        return $this;
    }
    public function setData(array $data){
        $this->data = $data;
        return $this;
    }
    public function setJwt(string $jwt){
        $this->jwt=$jwt;
        return $this;
    }
    function create(): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $issuedAt = time();
        $expiration = $issuedAt + $this->expireTime;

        $payload = array_merge($this->data, [
            'iat' => $issuedAt,
            'exp' => $expiration
        ]);

        $payloadString = json_encode($payload);
        $base64HeaderString = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64PayloadString = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadString));

        $signature = hash_hmac('sha256', $base64HeaderString . '.' . $base64PayloadString, $this->key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64HeaderString . '.' . $base64PayloadString . '.' . $base64Signature;
    }

    function verify(): ?array
    {
        
        $parts = explode('.', $this->jwt);
        if (count($parts) !== 3 || !$this->isBase64UrlEncoded($parts[0]) || !$this->isBase64UrlEncoded($parts[1])) {
            return sa('jwt格式不对');
        }
        $headerString = $this->base64UrlDecode($parts[0]);
        $payloadString = $this->base64UrlDecode($parts[1]);
        $signature = $this->base64UrlDecode($parts[2]);

        $header = json_decode($headerString, true);
        if ($header === null) {
            return sa('jwt头部不对');
        }

        $payload = json_decode($payloadString, true);
        if ($payload === null) {
            return sa('jwt数据不对');
        }

        $expectedSignature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->key, true);
        if (!hash_equals($expectedSignature, $signature)) {
            return sa('签名验证失败');
        }

        $now = time();
        if (!isset($payload['exp']) || $payload['exp'] < $now) {
            return sa('JWT已过期');
        }
        return sa($payload);
    }
    /**
     * 判断字符串是否为合法的 Base64Url 编码
     * @param string $str
     * @return bool
     */
    private function isBase64UrlEncoded(string $str): bool
    {
        $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $str), true);
        return $decoded !== false;
    }

    /**
     * Base64Url 解码
     * @param string $str
     * @return string|false
     */
    private function base64UrlDecode(string $str)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $str), true);
    }
}
//加密: $jwt=Jwt::init()->setKey('abc')->setExpireTime(3600)->setData(['abc'=>'test','id'=>1])->create();
// $data['jwy']=$jwt;
//解密: $jwt_arr=Jwt::init()->setKey('abc')->setJwt($jwt)->verify();
// $data['jwt_arr']=$jwt_arr;
// return sa($data);