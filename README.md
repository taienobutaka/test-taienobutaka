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
`アップロード`をクリックするとDBへ保存されます。<br />
同じメールアドレスを含むファイルを取り込もうとするとエラー表示します。
![alt text](<images/スクリーンショット 2025-03-26 100829.png>)
## 従業員データ
### CSVファイル内容
保存できる従業員のデータは、`名前`、`生年月日`、`メールアドレス`、`住所`
下記は、CSVファイルの一例です。
```
name,birth_date,email,address
鈴木 太郎,1990-01-01,suzuki@example.com,大阪
田中 太郎,1985-05-15,tanaka@example.com,東京
高橋 太郎,1990-01-01,takahasi@example.com,北海道
佐藤 太郎,1985-05-15,satou@example.com,秋田
前田 太郎,1990-01-01,maeda@example.com,福岡
井上 太郎,1985-05-15,inoue@example.com,鹿児島
```
従業員データは、phpMyAdminにて確認できます。
![alt text](<images/スクリーンショット 2025-03-26 100855.png>)
## 環境構築

**Dockerビルド**
1. `git clone git@github.com:taienobutaka/test-taienobutaka.git`
2. DockerDesktopアプリを立ち上げる
3. `docker-compose up -d --build`

``` bash
version: "3.8"

services:
  nginx:
    image: nginx:1.24
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./src:/var/www/
    depends_on:
      - php

  php:
    build: ./docker/php
    volumes:
      - ./src:/var/www/

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: laravel_db
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass
    command: mysqld --default-authentication-plugin=mysql_native_password
    volumes:
      - ./docker/mysql/data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf

  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
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
FROM php:8.4-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip \
  && docker-php-ext-install pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && composer self-update

WORKDIR /var/www
```
**Laravel環境構築**
1. `docker-compose exec php bash`
2. phpコンテナ内で`composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.envファイルを作成
4. .envに以下の環境変数を追加
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. アプリケーションキーの作成
``` bash
php artisan key:generate
```

6. マイグレーションの実行
``` bash
php artisan migrate
```
**Tailwind CSS環境構築**
1. TailwindCSSをインストール
```
npm install -D tailwindcss
```
2. TailwindCSSの設定
```
npx tailwindcss init
```
3. TailwindCSSをapp.cssに追加
```
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**Vue.js（Javascript）環境構築**
1. Laravel Mixをインストール
```
npm install laravel-mix --save-dev
```
2. Vue.jsをインストール
```
npm install vue@3
```
3. Vue.jsの設定<br />
Vue.jsを使用するために、webpack.mix.js を設定

4. Vueコンポーネントの作成<br />
resources/js ディレクトリにcomponentsフォルダを作成し、<br />
Vueコンポーネントを追加

5. Vue.jsをエントリーポイントに登録<br />
resources/js/app.js に以下を追加<br />
```
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';

const app = createApp({});
app.component('example-component', ExampleComponent);
app.mount('#app');
```

## 使用技術(実行環境)
- PHP 8.3.7
- Laravel 12.3.0
- MySQL 8.0.41
- Vue.js（Javascript）3.x
- tailwindcss 最新版（常に最新バージョンが使用されます）

## テーブル仕様
![alt text](<images/スクリーンショット 2025-03-26 095448.png>)

## 基本設計
![alt text](<images/スクリーンショット 2025-03-26 151223.png>)

## URL
- 開発環境：http://localhost/
- CSVアップロード画面：http://localhost/upload
- phpMyAdmin:：http://localhost:8080/
