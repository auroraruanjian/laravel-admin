if [ $USER != "www-data" -o $UID -eq 0 ];
  echo '请切换sudo -u www-data 用户使用此脚本'
  exit 1
fi

root_path="/var/www/html/laravel-admin/"

cd {$root_path}/backend/
echo "开始compoer install安装："
composer install

echo "开始npm install安装："
npm install

echo "开始编译后台安装："
npm run prod

echo "开始安装数据库："
php artisan migrate:install

echo "建立软连接："
php artisan storage:link

cd ../frontend/
composer install
npm install
npm run prod
ln -s {$root_path}/backend/storage/app/public/ {$root_path}/frontend/public/storage

