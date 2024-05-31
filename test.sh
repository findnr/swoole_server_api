#!/bin/bash
###
 # @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 # @Date: 2024-03-13 09:41:59
 # @LastEditors: findnr
 # @LastEditTime: 2024-05-31 17:02:05
 # @FilePath: \swoole_http_api_xiehui\xiehui.sh
 # @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
### 

# 定义变量
port=9502
name='test.php'

# 获取进程ID列表
pid_list=$(lsof -i :$port | awk '{if(NR>1) print $2}')

# 循环杀死进程
for pid in $pid_list; do
      kill -9 $pid
done
case $1 in
    'restart')
        # 启动进程
        #nohup swoole-cli $name 1> /dev/null 2>&1 &
        swoole-cli $name
        echo "restart $name $port success..."
    ;;
    "stop") 
        echo "stop $name $port success..."
    ;;
esac
