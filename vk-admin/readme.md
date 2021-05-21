## 使い方

1. vk-admin-config.php を vk-admin を使用するプラグインディレクトリに複製
1. vk-admin-config.php の中身をプラグインの情報にあわせて書き換える
1. プラグインが最初に読み込むPHPファイルなどから require_once( 'inc/vk-admin-config.php' ); などで読み込む

## バナーのサイズ
600px × 500px or 6 : 5 でお願いします。

## バナーの追加方法

1. package/images にバナーを追加
1. package/vk-admin-banner.php を開く
1. 下記のように編集

### テーマのバナーを追加する場合

下記のように  
type には　```'theme'```  
slug には　```'(テーマのディレクトリ名)/style.css'```  
image_url には　```$imgge_base_url . '（バナーのファイル名）'```  
link_url には ```'リンク先 URL'```  
alt には ```'（alt 属性のテキスト）'```  
language には表示する言語を格納（現状 ```'ja'``` or ```'en'``` ）  

```
	// Lightning (ja)
	$banner_array[] = array(
		'type' => 'theme',
		'slug' => 'lightning/style.css',
		'image_url'    => $imgge_base_url . 'lightning_bnr_ja.jpg',
		'link_url'     => 'https://lightning.nagoya/ja/',
		'alt'          => 'Lightning',
		'language'     => 'ja'
	);
```

### プラグインのバナーを追加する場合

下記のように  
type には　```'plugin'```  
slug には　```'(プラグインのディレクトリ名)/（プラグイン本体のファイル名）'```  
image_url には　```$imgge_base_url . '（バナーのファイル名）'```  
link_url には ```'リンク先 URL'```  
alt には ```'（alt 属性のテキスト）'```  
language には表示する言語を格納（現状 ```'ja'``` or ```'en'``` ）  

```
	// VK Block Patterns (ja)
	$banner_array[] = array(
		'type' => 'plugin',
		'slug' => 'vk-block-patterns/vk-block-patterns.php',
		'image_url'    => $imgge_base_url . 'vk-block-patterns_bnr_ja.jpg',
		'link_url'     => admin_url('plugin-install.php?s=vk+block+patterns&tab=search&type=term'),
		'alt'          => 'VK Block Patterns',
		'language'     => 'ja'
	);
```
