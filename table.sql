CREATE TABLE IF NOT EXISTS `table001` (
    `番号` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `企業名` varchar(100) DEFAULT NULL,
    `フリガナ` varchar(100) DEFAULT NULL,
    `上場企業` varchar(5) DEFAULT NULL,
    `所在地` varchar(100) DEFAULT NULL,
    `資本金` int(11) DEFAULT NULL,
    `売上高` bigint(20) DEFAULT NULL,
    `業種` varchar(32) DEFAULT NULL,
    `事業内容` varchar(100) DEFAULT NULL,
    `従業員数` int(11) DEFAULT NULL,
    `備考欄` text,
    `URL` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`番号`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `table001` (`企業名`,`フリガナ`,`上場企業`,`所在地`,`資本金`,`売上高`,`業種`,`事業内容`,`従業員数`,`備考欄`,`URL`) VALUES
("株式会社アルファ", "かぶしきがいしゃあるふぁ", "上場企業", "大阪府大阪市北区", "10000", "240000", "インターネット", "マッチングサービス", "5", "", "http://example.com/"),
("株式会社ブラボー", "かぶしきがいしゃぶらぼー", "上場企業", "大阪府堺市堺区", "20000", "480000", "インターネット", "マッチングサービス", "5", "", "http://example.net/"),
("株式会社チャーリー", "かぶしきがいしゃちゃーりー", "非上場企業", "大阪府大阪市阿倍野区", "30000", "480000", "インターネット", "マッチングサービス", "5", "", "http://example.org/"),
("株式会社デルタ", "かぶしきがいしゃでるた", "非上場企業", "東京都葛飾区亀有", "40000", "240000", "インターネット", "マッチングサービス", "5", "", "http://example.biz/"),
("株式会社フォクストロット", "かぶしきがいしゃふぉくすとろっと", "上場企業", "東京都新宿区", "50000", "1000000", "インターネット", "マッチングサービス", "5", "", "http://example.jp/"),
("株式会社ゴルフ", "かぶしきがいしゃごるふ", "非上場企業", "神奈川県川崎市", "60000", "10000", "インターネット", "マッチングサービス", "5", "", "http://example.co.jp/"),
("株式会社ホテル", "かぶしきがいしゃほてる", "上場企業", "京都府京都市", "70000", "400", "インターネット", "マッチングサービス", "5", "", "http://example.tk/");

