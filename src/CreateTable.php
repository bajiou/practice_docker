<?php

require_once 'vendor/autoload.php';

// データベースの接続情報を.envファイルから取得
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = 'db';
$user = 'root';
$password = $_ENV['MYSQL_PASSWORD'];
$dbName = $_ENV['MYSQL_DATABASE'];

function connectDb($host, $user, $password, $dbName)
{
    // MySQLへの接続
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo 'MySQLに接続しました。' . PHP_EOL;
        // return true;
    } catch (PDOException $e) {
        echo 'error: MySQLの接続に失敗しました。' . PHP_EOL;
        echo 'エラーメッセージ:' . $e->getMessage() . PHP_EOL;
        // return false;
        exit;
    }

    return $pdo;
}

function createDb($pdo)
{
    // データベースの作成
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS wikipedia_practice");
        echo "データベース 'wikipedia_practice' を作成しました。" . PHP_EOL;
    } catch (PDOException $e) {
        echo 'error: データベースの作成に失敗しました。' . PHP_EOL;
        echo 'エラーメッセージ:' . $e->getMessage() . PHP_EOL;
        exit;
    }
}

function createTable($pdo)
{
    //テーブルの作成
    try {
        // 作成したデータベースを選択
        $pdo->exec("USE wikipedia_practice");

        // テーブル作成SQL
        $tableSQL = "
        CREATE TABLE IF NOT EXISTS wikipedia_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            domain_code VARCHAR(10) NOT NULL,
            page_title VARCHAR(255) NOT NULL,
            view_count INT NOT NULL,
            total_response_size INT NOT NULL
            );
            ";
        // テーブル作成を実行
        $pdo->exec($tableSQL);
        echo "テーブル 'wikipedia_views' を作成しました。" . PHP_EOL;
    } catch (PDOException $e) {
        echo 'error: テーブルの作成に失敗しました。' . PHP_EOL;
        echo 'エラーメッセージ:' . $e->getMessage() . PHP_EOL;
        exit;
    }
}

function typeUser()
{
    echo 'wikipediaのアクセス数が多い記事を表示します。' . PHP_EOL;
    echo '確認したい分の数字を入力して下さい。' . PHP_EOL;

    $limit = trim(fgets(STDIN));

    //　簡単なバリデーション
    if (!ctype_digit($limit)) {
        echo '数字を入力して下さい。' . PHP_EOL;
        exit;
    }

    return $limit;
}

function getinfo($pdo, $limit)
{
    // データの取得
    try {
        $sql = "SELECT domain_code, page_title, view_count FROM wikipedia_views ORDER BY view_count DESC LIMIT :limit";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "データの取得に成功しました。" . PHP_EOL;
    } catch (PDOException $e) {
        echo 'error: データの取得に失敗しました。' . PHP_EOL;
        echo 'エラーメッセージ:' . $e->getMessage() . PHP_EOL;
        exit;
    }

    return $results;
}

function showData($results)
{
    // データの出力
    if (empty($results)) {
        echo "データが見つかりませんでした。";
    } else {
        foreach ($results as $value) {
            // echo print_r($value);
            echo 'domain_code ' . $value['domain_code'] . PHP_EOL . 'page_title ' . $value['page_title'] . PHP_EOL . 'view_count ' . $value['view_count'] . PHP_EOL;
            echo "\n";
        }
    }
}

function typeUserSecondQuestion()
{
    echo 'wikipediaのドメインコード毎の人気記事をを表示します。' . PHP_EOL;
    echo '確認したいドメインコードを入力して下さい。' . PHP_EOL;

    $type = trim(fgets(STDIN));

    //　簡単なバリデーション
    if (empty($type)) {
        echo '文字を入力して下さい。' . PHP_EOL;
        exit;
    }

    return explode(' ', $type);
}


function getinfoSecond($pdo, $type)
{
    // データの取得
    try {

        $placerholders = implode(',', array_fill(0, count($type), '?'));

        $sql = "SELECT
                    domain_code, SUM(view_count)
                FROM
                    wikipedia_views
                WHERE
                    domain_code IN ($placerholders)
                GROUP BY
                    domain_code
                ORDER BY
                    SUM(view_count) DESC";

        $stmt = $pdo->prepare($sql);

        $stmt->execute($type);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "データの取得に成功しました。" . PHP_EOL;
    } catch (PDOException $e) {
        echo 'error: データの取得に失敗しました。' . PHP_EOL;
        echo 'エラーメッセージ:' . $e->getMessage() . PHP_EOL;
        exit;
    }

    return $result;
}

function showDataSecond($result)
{
    // データの出力
    if (empty($result)) {
        echo "データが見つかりませんでした。";
    } else {
        foreach ($result as $value) {
            echo implode(", ", $value) . PHP_EOL;
            // echo "\n";
        }
    }
}


$pdo = connectDb($host, $user, $password, $dbName);
createDb($pdo);
createTable($pdo);
$limit = typeUser();
$results = getinfo($pdo, $limit);
showData($results);
$type = typeUserSecondQuestion();
$result = getinfoSecond($pdo, $type);
showDataSecond($result);
