#!/bin/bash

# 设置变量
current_directory=$(dirname "$0")

# 创建排除A文件夹的压缩包
cd "$current_directory"

# 从index.php文件中提取版本号和主题名
VERSION=$(grep -Eo '\*\s+@version\s+([0-9.]+)' index.php | grep -Eo '[0-9.]+')
THEME=$(grep -Eo '\*\s+@package\s+[a-zA-Z0-9.]+' index.php | awk '{print $NF}')

 excluded_folder="node_modules"
 zip_date=$(date '+%Y%m%d')  # 获取当前日期，格式为年月日，备用

 zip_filename="$THEME($VERSION)"  # 在文件名中添加版本号

  zip -r "../$THEME/$zip_filename.zip" "../$THEME" --exclude "../$THEME/$excluded_folder/*"
 # tar -czf "../$THEME/$zip_filename.tar.gz" -C "../$THEME" --exclude "$excluded_folder" .

 echo "压缩包已创建：../$THEME/$zip_filename 3秒后窗口关闭"

# 等待3秒后关闭窗口
sleep 3
# 关闭终端窗口
killall Terminal

exit 0