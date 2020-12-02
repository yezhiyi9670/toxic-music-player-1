Filtering
=========

Filters appear at playlist page, main page and admin page.

You may use advanced grammar to perform advanced search.

0x00 Scheduled
--------------

Filters are introduce in v115c, but scheduled to be fully implemented in undefined.

0x01 How does it work
---------------------

Filtering happens at frontend, so it can be performed in nearly no time.

System first splits the search term into multiple keywords, using Space (' '). An item will only be shown if the item matchs all the keywords exactly.

0x02 Keyword Features
---------------------

# Numeric Value (Num.)

If a keyword format like `K:{Value}` is Numeric, then it inherits the following formats:

| Format              | Requirements                                                                  | Feature |
| ------------------- | ----------------------------------------------------------------------------- | ------- |
| K:{Value}           | [INDEX] Matches {Value} exactly                                               | Num.    |
| K:*{Value}          | {Value} divides [INDEX] exactly                                               | Ext.    |
| K:/{Value}          | [INDEX] Divides {Value} exactly                                               | Ext.    |
| K:-{rValue}         | [INDEX] Is not greater than {rValue}                                          | Ext.    |
| K:{lValue}-         | [INDEX] Is not less than {lValue}                                             | Ext.    |
| K:{lValue}-{rValue} | [INDEX] Is between {lValue} and {rValue} inclusively, if {lValue} <= {rValue} | Ext.    |
| K:{lValue}-{rValue} | Always FALSE, if {lValue} > {rValue}                                          | Ext.    |
| K:{{A}}\|{{B}}      | At least one range in {{A}} and {{B}} contains [INDEX]                        | Cmp.    |

0x03 keywords
-------------

# Playlist Item

Those are used at main page, admin page and playlist page.

Structure:

| Index | Info                                        |
| ----- | ------------------------------------------- |
| 0     | ID (Local ID if in a playlist) of the music |
| 1     | Index (starting from 0) of the music        |
| 2     | Title (N in V201803)                        |
| 3     | Singer (S in V201803)                       |
| 4     | Collection (C in V201803, deprecated)       |
| 5     | Lyric Author (LA in V201803)                |
| 6     | Melody Author (MA in V201803)               |
| 7     | Rating (only valid in a playlist)           |

Formats:

| Format             | Requirements                                     | Feature |
| ------------------ | ------------------------------------------------ | ------- |
| {Keyword}          | At least one in [0,2,3,4,5,6] contains {Keyword} |         |
| I:{Local ID}       | [0] Matches {Local ID} exactly                   |         |
| X:{Index}          | [1] Matches {Index} exactly                      | Num.    |
| N:{Title}          | [2] Contains {Title}                             |         |
| S:{Singer}         | [3] Contains {Singer}                            |         |
| C:{Collection}     | [4] Contains {Collection}                        |         |
| LA:{Lyric Author}  | [5] Contains {Lyric Author}                      |         |
| MA:{Melody Author} | [6] Contains {Melody Author}                     |         |
| A:{Author}         | At least one in [5,6] Contains {Author}          |         |
| R:{Rating}         | [7] Matches {Rating} exactly                     | Num.    |

# User Manager

Used for user manager, filtering users.

Structure:

| Index | Info                                    |
| ----- | --------------------------------------- |
| 0     | Username                                |
| 1     | Password Hash Format ('md5'/'uauth')    |
| 2     | Registration IP                         |
| 3     | User Type (0='ban'/1='normal'/3='root') |

Formats:

| Format         | Requirements                             | Feature |
| -------------- | ---------------------------------------- | ------- |
| {Keyword}      | At least one in [0,2] contains {Keyword} |         |
| N:{Name}       | [0] Contains {Name}                      |         |
| P:{HashFormat} | [1] Matches {HashFormat} exactly         |         |
| I:{IP}         | [2] Matches {IP} exactly                 |         |
| T:{Type}       | [3] Matches {Type} exactly               |         |
