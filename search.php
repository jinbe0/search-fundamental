<?php

/* おまじない: プロダクションではオフにすること! */
ini_set("display_errors", "On");
error_reporting(E_ALL);

/* データベース情報をインポート */
include "secret.php";

/* テーブル名 */
$table_name = "table001";

/* MySQLに接続 */
try{
    $dns = "mysql:dbname=" . MYSQL_DATABASE . ";host=" . MYSQL_HOST . ";port=" . MYSQL_PORT;
    $pdo = new PDO($dns, MYSQL_USERNAME, MYSQL_PASSWORD, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
    ]);
    if($pdo === null){
        die("接続失敗: PDOインスタンスがNULLだよ");
    }
}
catch(PDOException $e) {
    print "接続失敗: " . $e->getMessage();
    die;
}

/* 都道府県の一覧を定義しておくよ */
$prefectures = [
    "北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県",
    "東京都","神奈川県","新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県",
    "滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県",
    "香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県",
];

?>
<html>
<head>
<meta charset="UTF-8">
<title>search engine 2.0</title>
<style>
body {
    background: #f7f7f7;
    margin: 0 0 32px 0;
    font-family: serif;
}
main {
    display: block;
    max-width: 1200px;
    margin: auto;
}
header {
    background: #eeeeee;
    padding: 10px;
    text-align: center;
}
form section {
    border-bottom: solid 1px #dddddd;
    padding: 12px 0px;
}
form section label {
    padding-right: 1em;
}
form section.pref label {
    display: inline-block;
    width: 90px;
    padding-right: 0;
}
form input[type="text"] {
    width: 100%;
    height: 28px;
}
form .operations {
    padding: 40px 0;
    text-align: center;
}
form .operations button {
    width: 180px;
    height: 50px;
    background: #7C91E1;
    border: 0px;
    border-radius: 25px;
    outline: 0;
    font: inherit;
    font-size: 20px;
    color: #ffffff;
}
h3 {
    margin: 0px;
    padding: 0px;
}
blockquote {
    background: #eeeeee;
    margin: 0 0 16px 0;
    padding: 16px;
}
table {
    width: 100%;
    border: solid 1px #000000;
    font-size: 12px;
    border-spacing: 0px;
    border-collapse: collapse;
}
td, th {
    border: solid 1px #000000;
    padding: 6px 0;
}
thead td, thead th {
    background: #666666;
    font-weight: 700;
    color: #ffffff;
    text-align: center;
}
</style>
</head>
<body>
<main>
    <header>
        <h1>Search Engine 2.0</h1>
        <p>データベースに格納されている企業情報を検索します</p>
    </header>
    <form action="" method="GET">
        <section>
            <h3>上場企業</h3>
            <?php printCheckboxes("listed", ["上場企業", "非上場企業"]); ?>
        </section>
        <section>
            <h3>業種</h3>
            <?php printCheckboxes("business_type", ["インターネット", "製造業", "建設業", "サービス業"]); ?>
        </section>
        <section class="pref">
            <h3>都道府県</h3>
            <?php printCheckboxes("pref", $prefectures); ?>
        </section>
        <section>
            <h3>フリーワード</h3>
            <input type="text" name="search" placeholder="Search..." autofocus value="<?=$_GET["search"] ?? ""?>">
        </section>
        <div class="operations">
            <button class="search">検索</button>
        </div>
    </form>
    
    <?php
    /* フリーワード検索をANDにする場合はtrueを代入 */
    $search_type_and = false;

    /* コンディションマネージャクラスをインスタンス化: 下の方にクラス定義があるよ */
    $conditionManager = new ConditionManager();

    /* フリーワード検索のキーワードがある場合 */
    if(isset($_GET["search"]) && is_string($_GET["search"]) && $_GET["search"]){
        $raw_keyword = $_GET["search"];
        $keywords = mb_split("/[ 　]+/", trim($raw_keyword));

        // フリーワード検索で対象となるカラムを指定; 数値系のカラムは除く
        $target_columns = ["企業名", "フリガナ", "業種", "事業内容", "備考欄", "URL"];
        $conditionManager->createFreewordCondition($keywords, $target_columns, $search_type_and);
    }

    /* 上場企業の条件指定がある場合 */
    if(isset($_GET["listed"]) && is_array($_GET["listed"]) && $_GET["listed"]){
        $conditionManager->createExclusiveCondition($_GET["listed"], "上場企業");
    }

    /* 業種の条件指定がある場合 */
    if(isset($_GET["business_type"]) && is_array($_GET["business_type"]) && $_GET["business_type"]){
        $conditionManager->createExclusiveCondition($_GET["business_type"], "業種");
    }

    /* 都道府県の指定がある場合 */
    if(isset($_GET["pref"]) && is_array($_GET["pref"]) && $_GET["pref"]){
        $conditionManager->createExclusiveCondition($_GET["pref"], "所在地", true);
    }

    /* クエリ作成 */
    $where = $conditionManager->getWhereStatement();
    $query = "SELECT * FROM {$table_name} WHERE {$where}";
    $stmt = $pdo->prepare($query);
    
    /* デバッグ用 */
    print "<h3>生成されたクエリ</h3><blockquote>" . htmlspecialchars($query) . "</blockquote>";
    print "<h3>生成されたパラメータ</h3><blockquote>" . htmlspecialchars(print_r($conditionManager->getParams(), true)) . "</blockquote>";

    /* プリペアドステートメントを解決 */
    foreach($conditionManager->getParams() as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }

    /* クエリ実行 */
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    /* 検索結果を表示 */
    print "<h3>\"" . htmlspecialchars($raw_keyword) . "\" の検索結果</h3>";
    printResult($result);

    ?>
</main>
</body>
</html>
<?php

/**
 * 検索結果をテーブル形式で出力するための関数
 * 
 * @param array $result 検索結果
 * @return void
 */
function printResult(array $result) {
    if(!$result){
        print "<div class='notfound'>該当なし</div>";
        return;
    }
    print "<table>";
    print "<thead>";
    foreach(array_keys($result[0]) as $col) {
        print "<td>" . htmlspecialchars($col) . "</td>";
    }
    print "</thead>";
    print "<tbody>";
    foreach($result as $data) {
        print "<tr>";
        foreach($data as $val) {
            print "<td>" . htmlspecialchars($val) . "</td>";
        }
        print "</tr>";
    }
    print "</tbody>";
    print "</table>\n";
}


/**
 * name属性値とチェックボックスの値の一覧から、実際のチェックボックスをHTMLで出力する関数
 * 検索結果を表示する際、現在の検索条件にあわせて自動的にチェックが入るよ
 * 
 * @param string $key name属性値
 * @param array $candidate チェックボックスの値の一覧
 * @return void
 */
function printCheckboxes(string $key, array $candidate) {
    foreach($candidate as $val) {
        if(isset($_GET[$key]) && is_array($_GET[$key]) && in_array($val, $_GET[$key])){
            $isChecked = "checked";
        }
        else{
            $isChecked = "";
        }
        print "<label><input type='checkbox' name='{$key}[]' value='{$val}' {$isChecked}> {$val}</label>\n";
    }
}


/**
 * 条件管理クラス
 * 
 * 複雑化する条件文SQLの組み立てを簡潔に行うため、クエリのwhereステートメントを作成する機能を抽象化したクラス。
 * 4種類の条件を作成することができる。すべての条件を作成した後、getWhereStatementメソッドでwhereステートメント
 * をゲットしよう。その際、プリペアドステートメントのパラメータはgetParamsメソッドで連想配列としてゲットできるぞ。
 * このクラスでは条件管理以外の仕事をしてはいけない。
 * 
 * @author jinbe0 <github.com/jinbe0>
 */
class ConditionManager {

    const PARAM_PREFIX = "param";

    private $keywordCounter = 0;
    private $conditions = [];
    private $params = [];

    /**
     * 作成された条件をwhereステートメントとしてゲットするメソッド
     * 
     * @return string whereStatement
     */
    public function getWhereStatement(): string {
        $combinedCondition = "";
        foreach($this->conditions as $i => $condition) {
            if($i != 0){
                $combinedCondition .= " AND ";
            }
            $combinedCondition .= "($condition)";
        }
        return $combinedCondition ?: "1";
    }

    /**
     * プリペアドステートメントに入力するためのパラメータを連想配列としてゲットするメソッド
     * 
     * @return array params
     */
    public function getParams(): array {
        return $this->params;
    }

    /**
     * 条件作成メソッド; フリーワード検索のために用いる。フルテキスト検索になるためパフォーマンスが悪い。
     * 作成された条件とパラメータはインスタンスが保持する。
     * 
     * @param array $keywords 検索キーワードの一覧
     * @param array $target_columns 検索対象のカラム名の一覧
     * @param bool $and_mode AND検索をするときはtrueを入力
     * @return void
     */
    public function createFreewordCondition(array $keywords, array $target_columns, bool $and_mode = false): void {
        $target_columns_string = $this->concatColumnNames($target_columns);
        $where = [];
        foreach($keywords as $index_keyword => $keyword){
            $param = $this->nextParam();
            $where[] = "{$target_columns_string} LIKE :{$param}";
            $this->params[$param] = "%{$keyword}%";
        }
        $this->conditions[] = join($and_mode ? " AND " : " OR ", $where);
    }

    /**
     * 条件作成メソッド; 一つまたは複数のキーワードを用いて完全一致または前方一致検索を行う。インデックスが効くのでまぁまぁ早い。
     * 作成された条件とパラメータはインスタンスが保持する。
     * 
     * @param array $keywords 検索キーワードの一覧
     * @param string $target_column 検索対象のカラム
     * @param bool $first_match 前方一致検索を行うときはtrueを入力
     * @return void
     */
    public function createExclusiveCondition(array $keywords, string $target_column, bool $first_match = false): void {
        $where = [];
        foreach($keywords as $index_keyword => $keyword){
            $param = $this->nextParam();
            $where[] = "`{$target_column}` LIKE :{$param}";
            $this->params[$param] = $first_match ? "{$keyword}%" : "{$keyword}";
        }
        $this->conditions[] = join(" OR ", $where);
    }

    /**
     * 条件作成メソッド; LIKE文ではなく = を用いて完全一致検索を行う。
     * 作成された条件とパラメータはインスタンスが保持する。
     * 
     * @param mixed $value 入力値
     * @param string $target_column 検索対象のカラム
     * @return void
     */
    public function createEqualCondition($value, string $target_column): void {
        $p = $this->nextParam();
        $this->conditions[] = "`{$target_column}` = :{$p}";
        $this->params[$p] = $value;
    }

    /**
     * 条件作成メソッド; 範囲検索をするときに使う。数値系や日付系のカラムに対して使う。
     * 作成された条件とパラメータはインスタンスが保持する。
     * 
     * @param mixed $min 最小値
     * @param mixed $max 最大値
     * @param string $target_column 検索対象のカラム
     * @return void
     */
    public function createRangeCondition($min, $max, string $target_column): void {
        $p1 = $this->nextParam();
        $p2 = $this->nextParam();
        $this->conditions[] = "`{$target_column}` BETWEEN :{$p1} AND :{$p2}";
        $this->params[$p1] = $min;
        $this->params[$p2] = $max;
    }

    /**
     * プリペアドステートメントのためのキーワードをシーケンシャルに生成するメソッド
     * 
     * @return string parameter_key パラメータ名
     */
    private function nextParam(): string {
        return self::PARAM_PREFIX . $this->keywordCounter++;
    }

    /**
     * カラム名の一覧をCONCATプロシージャに渡すクエリを組み立てるメソッド
     * 
     * @param array $columnNames カラム名の一覧
     * @return string $query 生成されたクエリ
     */
    private function concatColumnNames(array $columnNames): string {
        if(count($columnNames) == 1){
            return "`{$columnNames[0]}`";
        }
        $quoted = [];
        foreach($columnNames as $col) {
            $quoted[] = "`{$col}`";
        }
        return "CONCAT(" . join(",", $quoted) . ")";
    }

}


