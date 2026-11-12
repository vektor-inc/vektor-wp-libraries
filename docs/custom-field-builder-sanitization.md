# Custom Field Builder の保存値サニタイズ仕様

- Custom Field Builder と Flexible Table は共通のサニタイザーで保存値を整える。
- 配列はキーに対応するフィールド定義をたどって再帰的に処理し、葉の値をフィールド形式に応じてサニタイズする。
- `textarea` は `wp_kses_post()`、`url` は `esc_url_raw()`、それ以外の値は `sanitize_text_field()` を使う。
- Custom Field Builder の `image` は添付 ID と HTTP(S) URL を保持する既存の画像サニタイザーを使う。Flexible Table では従来どおり通常のテキストとして整える。
