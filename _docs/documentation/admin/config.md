`txmp.docs / documentation`

# Configuration and customization

## Custom language keys

You can do a lot using this, such as changing displayed strings, customize the About dialog, create DeltaDesc annotations and so on.

Vanilla Location: `lib/i18n/lang/<id>.lang`  
Override Location: `data/i18n/<id>.lang`

```plain
# Configuration
config.app_name = Music Archive
config.app_name.title = Music Archive
```

Add your own `.lang` files at the override location to override vanilla values and define new keys.

If you don't know how, have a look at this:

- [Introducing language files](./lang.md)

## Branding

As a free application, you can change the theme color and the title of the app.

### Theme Color

Location: `internal_config/config_basic.php`

```php
define("MAIN_COLOR","E64A19"); // [默认]主颜色
define("GC_COLOR_1",MAIN_COLOR);
define("GC_COLOR_2","FFA000"); // 渐变色2
```

You can only use constant hex values, without `#`.

### App Title

Location: `internal_config/config_misc.php`

```php
"app_name" => LNG('config.app_name'),//软件名称自定义
"app_name_title" => LNG('config.app_title'),
"app_desc" => LNG('config.app_desc'), // 软件描述
```

- `app_name` the one displayed on the header bar.
- `app_name_title` the one displayed on the page title.
- `app_desc` description displayed on the page title of the homepage.

You can use `LNG()` to refer to language keys.

## Misc Config

Location: `internal_config/config_misc.php`

```php
// Timezone shift (should not be used)
"timezone" => 0,
// RemotePlay cache life, in seconds
"cache_expire" => 24*60*60*93,
// Lyrics book cache life, in seconds
"temp_expire" => 3600,

// Retries of RemotePlay search. Default values are recommended
"rp_search_retry" => 4,
"rp_search_retry_delay" => 0.1,
// Allow pay_play hack on RemotePlay
// - false: Paid-to-play songs will be unplayable and undownloadable.
// - true: One have full access to paid-to-play songs.
"rp_allow_pay_crack" => true,

// Allow visitor register
"can_register" => true,
// Register limit per IP
"ip_reg_limit" => 1,

// Max playlists saved by a single user
"user_playlist_quota" => 128,
// Max size of a playlist (76800 = approximately 4100 songs)
"user_playlist_limit" => 76800,
// [WIP] Max exams saved by a single user
"user_exam_quota" => 50,
// [WIP] Max recent submissions
"user_submission_capacity" => 20,

// [WIP] Max problems in a exam
"exam_problem_limit" => 512,

// Offline mode (for usage in LAN)
// - false: Use CDN to load fonts, for better speed.
// - true: Load fonts from statics, for offline accessibility.
"offline_usage" => true,

// Cache compiled lyrics
// - false: Slow loading speed
// - true: Fast loading, ~6KB extra space for each song
"compiled_cache" => true,

// Debug Mode
// * Should be false in production.
// - false: Display no errors, minimized JSON.
// - true: Display errors, prettified JSON.
"debug" => true,
// Show lyric compilation process on Debug Code screen
// * Should be false in production.
"show_comp_process" => true,
```

## Broadcast

If you want to show a notice on the music discovery page.

Location: `data/bc/bc.html`

```html
<h3>Broadcast</h3>

<p>Content here...</p>
```
