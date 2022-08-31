`txmp.docs / documentation`

# Introducing DeltaDesc V201805

DeltaDesc is the file format for lyrics in TXMP. This is a quick start article for it.

Take an example to better understand it.

```plain
!dataver 201805
[Info]
N  明天会更好
S  卓依婷
LA --
MA --
C  校园青春乐2
A  #1eaaf1
G1 #1eaaf1
G2 #00BCD4
O  http://www.kuwo.cn/yinyue/533393/

// @reference [Para] 与 [Similar] 的正确用法

[Comment]
上传于 1970/01/01
年级休业式主题曲

[Para @XSA 1A1 段落]
L 26.9 轻轻敲醒沉睡的心灵
L 30.4 慢慢张开你的眼睛
L 33.6 看看忙碌的世界 是否依然
L 37.7 孤独地转个不停

[Para @XSB $1B1 段落]
L 40.8 春风不解风情
L 44.2 吹动少年的心
L 47.7 让昨日脸上的泪痕
L 50.6 随记忆风干了

[Similar @XSA 56.0 1A2 段落]
L - 抬头寻找天空的翅膀
L - 候鸟出现它的影迹
L - 带来远处的饥荒 无情的战火
L - 依然存在的消息

[Similar @XSB 69.6 1B2 段落]
L - 玉山白雪飘零
L - 燃烧少年的心
L - 使真情溶化成音符
L - 倾述遥远的祝福

[Para @SGC 2A1 高潮]
L 85.0 唱出你的热情 伸出你双手
L 88.4 让我拥抱着你的梦
L 91.6 让我拥有你
L 93.7 真心的面孔
L 98.4 让我们的笑容
L 100.3 充满着青春的骄傲
L 105.1 为明天献出
L 106.9 虔诚的祈祷

[Split]

[Similar @XSA 111.6 1A3 段落]
L - 谁能不顾自己的家园
L - 抛开记忆中的童年
L - 谁能忍心看他 昨日的忧愁
L - 带走我们的笑容

[Similar @XSB 125.5 1B3 段落]
L - 青春不解红尘
L - 胭脂沾染了灰
L - 让久违不见的泪水
L - 滋润了你的面容

[Similar @SGC 140.9 2A1 高潮]
L - 唱出你的热情 伸出你双手
L - 让我拥抱着你的梦
L - 让我拥有你
L - 真心的面孔
L - 让我们的笑容
L - 充满着青春的骄傲
L - 为明天献出
L - 虔诚的祈祷

[Split]

[Similar @XSA 167.5 1A1 段落]
L - 轻轻敲醒沉睡的心灵
L - 慢慢张开你的眼睛
L - 看[U]那[/U]忙碌的世界 是否依然
L - 孤独地转个不停

[Similar @XSB 181.2 1B4 段落]
L - 日出唤醒清晨
L - 大地光彩重生
L - 让和风拂出的音响
L - 谱成生命的乐章

[Similar @EGC @SGC 196.6 2A2 高潮]
L - 唱出你的热情 伸出你双手
L - 让我拥抱着你的梦
L - 让我拥有你
L - 真心的面孔
L - 让我们的笑容
L - 充满着青春的骄傲
L - 让我们期待
L - 明天会更好

[Reuse @EGC 223.5]

[Reuse @EGC 250.5]

[Final 276.1]

```

## Comments

- `//` on the beginning of a line indicates comment.
- `##` on anywhere of a line (except the end) indicates a comment.
- `[Comment]` indicates a commented section.

## Structure of a descriptor

Commands are lines without `[]`, they're descriptors.

Lines with `[]` are section headers. The stuff in `[]` is also a descriptor.

See this example:

```plain
[Para @SGC 2A1 高潮]
L 85.0 唱出你的热情 伸出你双手
```

Every descriptor has a head, such as `Para` and `L` here. Heads are case-sensitive.

A certain amount following stuff are arguments. For `Para` and `L`, they are as below:

```plain
Para | new_id = @SGC, ac = 2A1
L    | timestamp = 85.0
```

The remaining part is extension. It can be any text.

```plain
Para | name = 高潮
L    | content = 唱出你的热情 伸出你双手
```

You can pass argument containing spaces like this: `<[Two words]>`.  
Even include `<[` and `]>` in the string: `<[Here are <[Two words]>]><[ !]>`

## Preprocessor macros

### Data version

`!dataver <version>` should be exactly on the first line, indicating the version of DeltaDesc.

### Invoke and supress error

Manually invoke an error with `~invoke <level> <error_id> <arg1> <arg2> ...`, at anywhere.

Supress non-syntax errors with `~supress <error_id>`, at anywhere.

For a complete list of error level and ids, you may look at language files, or see:

- [DeltaDesc V201805 quick reference](../cheatsheet/deltadesc201805.md)

## Sections and commands

Lines with `[]` are section headers, defining a beginning of a section.

All commands should be under a section. Note that under different sections, commands with the same head can mean different things.

## [Info]

`[Info]` section is for the basic information of a song. There should be exactly one `[Info]` section in a document.

Commands:

| Head | Args | ext.        | Comment                |
| ---- | ---- | ----------- | ---------------------- |
| N    | -    | `content`   | Song title             |
| S    | -    | `content`   | Singer                 |
| LA   | -    | `content`   | Author of lyrics       |
| MA   | -    | `content`   | Author of musical part |
| C    | -    | `content`   | Album name             |
| A\*  | -    | `hex_color` | Single theme color     |
| G1\* | -    | `hex_color` | Gradient theme color 1 |
| G2\* | -    | `hex_color` | Gradient theme color 2 |
| O    | -    | `url`       | Origin URL             |
| P\*  | -    | `url`       | Cover image URL        |

- `*` items are optional.
- For more than one singers and authors, they should be separated by ` / `, not other symbols.
- Hex color go with or without `#`.
- If G1 and G2 are not defined, will use single theme color.
- P is by default `<song_id>/avatar`.

Due to a bug, if a song is renamed, it may not be able to show its default cover image. Re-saving the lyrics file could fix it.

## Lyrics

Lyrics section header has 4 heads.

| Head    | Args                                | ext. | Comment                      |
| ------- | ----------------------------------- | ---- | ---------------------------- |
| Para    | `[@<new_id>] <ac>`                  | name | Common paragraph             |
| Hidden  | `[@<new_id>] <ac>`                  | name | Paragraph hidden in overview |
| Reuse   | `@<ref_id> <time>`                  | -    | Copy an entire paragraph     |
| Similar | `[@<new_id>] @<ref_id> <time> <ac>` | name | Copy pattern of a paragraph  |

- `<ac>` is the ID of a paragraph. For example, `1A`, `1A1`, `1*`.
- Overview is the bar on top of the lyrics area.

and share one command.

| Head | Args     | ext.    | Comment      |
| ---- | -------- | ------- | ------------ |
| L    | `<time>` | content | A lyric line |

### Time format

Lyrics are paired with timestamps in the song, so time should be included in a DeltaDesc file. Time format should be as below:

- `__LT__` to-be-filled, and should only exist when editing.
- `-` null.
- `78` 78s.
- `078.0` 78s.
- `01-18.0` 78s.

Lyric lines with null timestamps will be interpreted as 'commented', and will be rendered gray and italic (blue and italic in the generated Word document).

### Describing metadata

Genrally a song has lots of author information that won't fit into `[Info]` section. You can describe them under a `[Hidden M1 Song Info]` section, and give them null timestamps.

```plain
[Hidden M1 歌曲信息]
L - 心之所想 - 爱朵女孩 (Idol Girls)
L - 词：张志远
L - 曲：张志远
L - 编曲：小路
L - 制作人：彭钧
L - 木吉他：小路
L - 和声：徐海榕
L - 录音师：李进东
L - 录音棚：作乐Studio/小跳蛙Studio
L - 混音师：杜渡/彭钧
L - 出品公司：北京爱朵文化传播有限公司
L - 出品人：张志远/杨群星
L - （未经著作人许可，不得翻唱、翻录或使用）
```

### Creating paragraph

A `[Para]` section should contain `L` commands describing lyric lines in the correct order.

```plain
[Para @XSA 1A1 段落]
L 26.9 轻轻敲醒沉睡的心灵
L 30.4 慢慢张开你的眼睛
L 33.6 看看忙碌的世界 是否依然
L 37.7 孤独地转个不停
```

You can use `@` to assign it with an id. ID should only contain alpha, numbers and `_`.

Lyric lines can have punctuations (ASCII ones recommended, if possible), or no punctuations at all.

### Reusing paragraph

Use `[Reuse]` to reuse everything from a defined paragraph, except for assigning a new starting time.

```plain
[Reuse @EGC 223.5]
```

Use `[Similar]` to reuse pattern from a defined paragraph, assigning new starting time and lyrics content.

```plain
[Similar @XSB 181.2 1B4 段落]
L - 日出唤醒清晨
L - 大地光彩重生
L - 让和风拂出的音响
L - 谱成生命的乐章
```

Lyric lines here should have null timestamps.

The reused paragraph must appear earlier than the reference.

### Splitting and finalizing

Use `[Split]` to indicate a split in structure.

```plain
[Split]
```

Use `[Final]` to indicate the end point of the last paragraph.

```plain
[Final 276.1]
```

### Decorating

Use stuff like `[U]` `[/U]` in lyrics content to decorate.

| Tag | Comment       |
| --- | ------------- |
| U   | Underline     |
| S   | Strikethrough |
| R   | Reverse       |
| 1   | Role 1        |
| 2   | Role 2        |
| 3   | Role 1,2      |
| 4   | Role 3        |
| 5   | Role 1,3      |
| 6   | Role 2,3      |
| 7   | Role 1,2,3    |

### Translating

For translating, simply add a line with null timestamp under the original lyric line.

## Comments and annotations

- `//` on the start of a line
- `##` on anywhere of a line
- `[Comment]` section

Specially, `// @<annotation_name> <content>` means annotation. For available annotations, see:

- [DeltaDesc V201805 quick reference](../cheatsheet/deltadesc201805.md)

Using custom language keys, you can add annotations by yourself. You can reference to the default language files to find out how.
