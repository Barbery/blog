#!/bin/sh

myEcho()
{
    # 30 黑 31 红, 32 绿, 33 黄, 34 蓝, 35 紫, 36 青绿, 37 白（灰）
    case $2 in
        black)
            color=30
            ;;
        red)
            color=31
            ;;
        green)
            color=32
            ;;
        yellow)
            color=33
            ;;
        blue)
            color=34
            ;;
        green)
            color=32
            ;;
        purple)
            color=35
            ;;
        azure)
            color=36
            ;;
        white)
            color=37
            ;;
        *)
            color=33
            ;;
    esac
    echo "\033[0;$color;1m$1\033[0m\n";
}

myEcho "-------------install some depends compose-------------";
sudo apt-get install python-software-properties libreadline-dev libpcre3-dev libssl-dev perl make sudo ^ncurses*;
sudo add-apt-repository ppa:ondrej/php5
sudo apt-get update

cd ~;
sudo mkdir tmp_download_file;
cd tmp_download_file/;

myEcho "------------download openresty--------------------";
wget http://openresty.org/download/ngx_openresty-1.4.3.6.tar.gz;
sudo tar xzvf ngx_openresty-*.tar.gz;
cd ngx_openresty-*;


myEcho "------------start to install openresty------------";
./configure --with-luajit --without-http_redis2_module --with-http_iconv_module;

myEcho "please input your machine is whether support multiple cores feature?(y/n)" red;
read is_multi_core;
case $is_multi_core in
    y|Y|yes|Yes)
        make -j2
        ;;
    n|N|no|No)
        make
        ;;
    *)
        make
        ;;
esac
sudo make install;
sudo ln /usr/local/openresty/nginx/sbin/nginx /usr/local/sbin/nginx;
sudo ln -sf /usr/local/openresty/nginx/html /www

echo 'user www-data;
worker_processes  2;

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;

    sendfile        on;
    keepalive_timeout  65;
    gzip on;
    client_max_body_size 50m;



    server {
        listen 80;
        server_name  localhost;
        root   html;


        location / {
            index  index.html index.htm index.php;
        }



        location ~ \.php {
            fastcgi_index index.php;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            include      fastcgi_params;

            set $path_info "";
            set $real_script_name $fastcgi_script_name;
            if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
                set $real_script_name $1;
                set $path_info $2;
            }

            fastcgi_param SCRIPT_FILENAME /usr/local/openresty/nginx/html/$real_script_name;
            fastcgi_param SCRIPT_NAME $real_script_name;
            fastcgi_param PATH_INFO $path_info;
        }
    }
}' > /usr/local/openresty/nginx/conf/nginx.conf;



myEcho "-------------start to install php and mysql-------------";
sudo apt-get install php5-fpm php5-dev php5-cli php5-gd php5-mysqlnd mysql-server;
sudo cp /etc/php5/fpm/pool.d/www.conf /etc/php5/fpm/pool.d/www.conf.bk
sed -i 's/127.0.0.1:9000/\/var\/run\/php5-fpm.sock/g' /etc/php5/fpm/pool.d/www.conf
sudo php5-fpm;

if test $( pgrep -f nginx | wc -l ) -eq 0
then
    sudo nginx;
else
    sudo nginx -s reload;
fi

# add nginx to server and add to auto start
sudo wget http://stutostu.qiniudn.com/openresty.init.d.script -O /etc/init.d/nginx;
sudo update-rc.d -f nginx defaults

cd ~;
sudo rm -rf tmp_download_file;
myEcho "-------------finish install----------------" green;
