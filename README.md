# CSVアップロード
従業員データのインポート機能の開発により、<br />
ドラッグ&ドロップを使ったCSVアップロードで、DBに保存できるようにしました。
## CSVアップロード画面
### ドラッグ&ドロップ
画面中央に`ファイルを選択`ボタンがあり、直接ファイルを選択することができます。<br />
![alt text](<images/スクリーンショット 2025-03-26 100636.png>)
ドラッグ&ドロップにより、四角い枠内にドロップすると、選択したファイルが表示されます。
![alt text](<images/スクリーンショット 2025-03-26 100025.png>)
選択されているファイルの右側にありますバツ印で選択を解除できます。
![alt text](<images/スクリーンショット 2025-03-26 100657.png>)
`アップロード`をクリックするとDBへ保存されます。
![alt text](<images/スクリーンショット 2025-03-26 100829.png>)
## 従業員データ
### CSVファイル内容
保存できる従業員のデータは、`名前`、`生年月日`、`メールアドレス`、`住所`
下記の画像は、CSVファイルの一例です。
![alt text](<images/スクリーンショット 2025-03-26 100414.png>)
従業員データは、phpMyAdminにて確認できます。
![alt text](<images/スクリーンショット 2025-03-26 100855.png>)
## 環境構築

**Dockerビルド**
1. `git clone git@github.com:taienobutaka/ReservationSystem.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

``` bash
services:
    nginx:
        image: nginx:1.21.1
        ports:
            - "80:80"
        volumes:
            - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
            - ./src:/var/www/html
        depends_on:
            - php

    php:
        build: ./docker/php
        volumes:
            - ./src:/var/www/html

    mysql:
        image: mysql:8.0.26
        environment:
            MYSQL_ROOT_PASSWORD: root
            MYSQL_DATABASE: laravel_db
            MYSQL_USER: laravel_user
            MYSQL_PASSWORD: laravel_pass
        command:
            mysqld --default-authentication-plugin=mysql_native_password
        volumes:
            - ./docker/mysql/data:/var/lib/mysql
            - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf

    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        environment:
            - PMA_ARBITRARY=1
            - PMA_HOST=mysql
            - PMA_USER=laravel_user
            - PMA_PASSWORD=laravel_pass
        depends_on:
            - mysql
        ports:
            - 8080:80

```

**Docker環境の設定**
1. Dockerfileを使用してPHP環境を構築
``` bash
FROM php:8.1-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
    && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip libmagickwand-dev --no-install-recommends \
    && docker-php-ext-install pdo_mysql zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update

WORKDIR /var/www




FROM php:7.4.9-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
    && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update

WORKDIR /var/www
```


**Laravel環境構築**
1. `docker-compose exec php bash`
2. phpコンテナ内でhtmlファイルを作成
3. htmlファイルに移動して、`composer install`
4. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
5. .envに以下の環境変数を追加
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
6. MailHogの設定
```
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="メールアドレス"
MAIL_FROM_NAME="${APP_NAME}"
```
7. アプリケーションキーの作成
``` bash
php artisan key:generate
```

8. マイグレーションの実行
``` bash
php artisan migrate
```

9. シーディングの実行
``` bash
php artisan db:seed
```

**リマインダー**
1. コマンドの作成<br>
リマインダーを送信するためのカスタムコマンドを作成

``` bash
php artisan make:command SendReservationReminders
```

2. メールクラスの作成<br>
リマインダーのメールを送信するためのMailableクラスを作成
``` bash
php artisan make:mail ReservationReminder
```

3. リマインダーの実行
``` bash
php artisan schedule:work
```
**認証メール**
1. カスタムメール通知を設定
``` bash
php artisan make:notification VerifyEmail
```

**予約完了メール**
1. メールクラスの作成<br>
予約完了メールを送信するためのMailableクラスを作成
``` bash
php artisan make:mail ReservationMail
```

**Simple QrCodeの設定**
1. 必要なパッケージのインストール<br>
Simple QrCodeパッケージをインストール
``` bash
composer require simplesoftwareio/simple-qrcode
```
**Stripe（テスト環境）の設定**
1. Stripeのインストール<br>
StripeのPHPライブラリをインストール
```
composer require stripe/stripe-php
```

2. 環境設定ファイルの更新<br>
`.env`ファイルにStripeのAPIキーを追加
```
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret_key
```

## 使用技術(実行環境)
- PHP 8.1.31
- Laravel 8.83.29
- MySQL 8.0.26

## ER図
![alt text](<images/スクリーンショット 2025-03-14 085500.png>)

## 機能一覧
![alt text](<images/スクリーンショット 2025-02-10 231957-1.png>)
![alt text](<images/スクリーンショット 2025-02-10 231927.png>)

## テーブル仕様
![alt text](<images/スクリーンショット 2025-02-12 091514.png>)

## 基本設計
![alt text](<images/スクリーンショット 2025-02-10 164341.png>)
![alt text](<images/スクリーンショット 2025-02-10 231107.png>)
![alt text](<images/スクリーンショット 2025-02-10 231044.png>)

## URL
- 開発環境：http://localhost/
- 店舗一覧画面：http://localhost/
- 管理者登録画面：http://localhost/admin-register
- 店舗代表者登録：http://localhost/owner-register
- phpMyAdmin:：http://localhost:8080/

# test-taienobutaka
