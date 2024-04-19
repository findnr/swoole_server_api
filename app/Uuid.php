<?php 

declare(strict_types=1);

namespace app;

class Uuid
{
    /**
     * 生成一个符合 RFC 4122 标准的 UUID
     *
     * @param string $data 可选的入口内容数据，用于增加种子的随机性
     * @return string 返回生成的 UUID
     */
    public static function init():object
    {
        return new Uuid();
    }


    public function create(): string
    {
        // 获取随机种子
        $seed = uniqid('', true);
        // 计算 Hash 并以十六进制形式返回
        $hash = md5($seed);

        // 将 Hash 划分为几个部分
        $hashParts = str_split($hash, 4);

        // 生成 UUID 格式的字符串
        return sprintf(
            '%08s-%04s-%04x-%04x-%04x%08x',
            $hashParts[0],
            $hashParts[1],
            hexdec($hashParts[2]) & 0x0fff | 0x4000, // 版本 4
            hexdec($hashParts[3]) & 0x3fff | 0x8000, // 版本 4 + UUID 版本
            hexdec($hashParts[4]),
            hexdec($hashParts[5]) // 节 ID
        );
    }
}