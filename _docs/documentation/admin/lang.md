`txmp.docs / documentation`

# Introducing language files

TXMP supports Simplified Chinese and English. So we have some language files.

## What's the format?

Languages are stored as `.lang` files. They're generally referred to as Minecraft Language, as it is formerly used in Minecraft.

Minecraft Language files are text and can be edited using your code editor.

See the snippet below.

```plain
# UI
ui.menu.language = Language
ui.menu.admin = Administration
ui.menu.admin.music = Manage Songs
ui.menu.admin.user = Manage Users
ui.menu.user = User

# Tests
test.s1 = 1 + 1 = 2
test.s2 = "  This line contains boundary spaces.  "
test.s3 = "Use escape sequences\nto break line."
```

- Empty lines are ignored.
- Lines starting with `#` are interpreted as comment.
- Other lines should contain a `=`. The string (trimmed) before the first `=` is key, and the string (trimmed) after it is content.
- If content is wrapped by a pair of `"`, it will be unescaped. Unescaped strings are not trimmed again. Note that `'` is not supported.

## Where're the files?

- Built-in files `lib/i18n/lang/<id>.lang`. Do not edit them as the changes will be lost on an update.
- User-defined `data/i18n/<id>.lang`, to override default values.

Items starting with `_` is used to define list items. To make effect, they must be defined in the `en_us` language.

```plain
# Annotation Definition
_.ann.test = Bug Test
_.ann.archive = Local Archive Restore
```
