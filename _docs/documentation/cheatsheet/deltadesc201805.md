`txmp.docs / documentation`

# DeltaDesc V201805 quick reference

## Time format

- `__LT__` to-be-filled, and should only exist when editing.
- `-` null.
- `78` 78s.
- `078.0` 78s.
- `01-18.0` 78s.

## Sections and commands

### [Info] - Basic metadata

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

Note: Items with \* are optional. Cover image defaultly refers to the uploaded image on the panel.

### [Para [@<new_id>] \<ac>] - Common paragraph
### [Hidden [@<new_id>] \<ac>] - Metadata paragraph
### [Similar [@<new_id>] @\<ref_id> \<time> \<ac>] - Pattern reuse

| Head | Args     | ext.    | Comment      |
| ---- | -------- | ------- | ------------ |
| L    | `<time>` | content | A lyric line |

### [Reuse @<ref_id> \<time>] - Total reuse

This section should not have commands.

### [Split]
### [Final \<time>]

These sections should not have commands.

### [Comment]

This section represents comments.

## Annotations

These are default annotations. You can add language items starting with `_.ann.` in i18n overrides to create new annotations. You can reference to the default language files to find out how. See:

- [Configuration and customization](../admin/config.md)

| Name         | Display         | Comment                                                                                                                        |
| ------------ | --------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| @`issue`     | Issue           | Issue, generally something to do with music theory research.                                                                   |
| @`reference` | Reference       | Standard reference (example) of document format.                                                                               |
| @`error`     | To be corrected | Something significantly wrong about lyrics or audio files, to be corrected.                                                    |
| @`rev`       | Revision        | Edits to the song, especially ambiguous modifications to the lyrics.                                                           |
| @`update`    | To be updated   | Data version of the lyrics file to be updated.                                                                                 |
| @`forward`   | Forwared Compat | Forward compatibility format, but not implemented. This could be used when you are using a to-be-implemented new data version. |

## Errors

| Level  | ID                                 | Comment                                                                                                                                                        |
| ------ | ---------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| unstd  | role                               | Line ${0} - Non-comment line used to mark role. Use commented lines or color annotations.                                                                      |
| unstd  | fullspace                          | Line ${0} - Full space detected. Use two half spaces instead. *Use two half spaces as a full space. It will be automatically converted.*                       |
| unstd  | fullspace_h                        | Line ${0} - Continuous spaces or full space found in paragraph header. *Full spaces should by no means be used in paragraph headers.*                          |
| unstd  | wronginterval                      | Line ${0} - The way of marking an empty line is incorrect. *Don't use `---`, use `- - -`.*                                                                     |
| unstd  | authorsep                          | Line ${0} - Different authors should only be separated with '/'. *This will be triggered when you try to use '&'.*                                             |
| unstd  | ftime                              | Line ${0} - Marking a null time with \_\_LT__ or \_\_FTIME__.                                                                                                  |
| unstd  | no_final                           | Line ${0} - Final point of lyric area is not defined. *You need a `Final` section. If the final point is the end of the audio track, you may use `[Final -]`.* |
| notice | low_ver                            | Line \${0} - The version of file is \${1}, while the latest is ${2}. This probably means that the file is outdated.                                            |
| warn   | sametime                           | Line ${0} - The lyric line has exactly the same time as the previous one. *Merging lines are not supported now.*                                               |
| warn   | mult_info                          | Line ${0} - Multiple Info tags are found. Note that only the earliest one will work.                                                                           |
| warn   | undef_header                       | Line ${0} - Usage of undefined section heading.                                                                                                                |
| warn   | no_ver                             | Line ${0} - The version of the file is undefined. This may not be allowed in the future.                                                                       |
| warn   | fake_final                         | Line ${0} - Paragraph defined after finalizing.                                                                                                                |
| warn   | early_final                        | Line ${0} - Final point of lyric area is earlier than the last lyric line.                                                                                     |
| warn   | invalid_argument                   | Line ${0} - Invalid argument format. *Misuse of `~supress` and `~invoke`*.                                                                                     |
| error  | id_tan90                           | Line ${0} - Referred para ID does not exist.                                                                                                                   |
| error  | similar_exceeded                   | Line ${0} - The length of similar para is longer than the referred one.                                                                                        |
| error  | id_invalid                         | Line ${0} - Para ID does not start with '@'.                                                                                                                   |
| error  | ambig_heading                      | Line ${0} - The line starts with '[' but does not end with ']'.                                                                                                |
| error  | high_ver                           | Line \${0} - The version of file is \${1}, while the latest is ${2}. The file may not work properly.                                                           |
| error  | illegal_ver                        | Line ${0} - The version mark does not look right. *Data version must be 6-digit code.*                                                                         |
| error  | Line ${0} - Duplicate final point. | Line ${0} - Duplicate final point.                                                                                                                             |
| error  | unclosed_string                    | Line ${0} - String unclosed. *A `<[` quote is unclosed.*                                                                                                       |
| error  | invoke_type_tan90                  | Line \${0} - Invoked error level '${1}' does not exist.                                                                                                        |
| error  | invoke_id_tan90                    | Line ${0} - Invoked error '\${1}' does not exist.                                                                                                              |
| fatal  | no_title                           | Line ${0} - Song title is not defined.                                                                                                                         |
| fatal  | not_started                        | Line ${0} - The first non-empty line is not a section heading.                                                                                                 |
| fatal  | file_tan90                         | Line ${0} - Lyric file is not readable.                                                                                                                        |
| fatal  | uke                                | Line ${0} - Unknown Error                                                                                                                                      |
| ?      | lyric_ambiguity                    | Line ${0} - Lyrics to be verified.                                                                                                                             |
| ?      | lyric_mismatch                     | Line ${0} - Lyrics mismatch.                                                                                                                                   |
| ?      | lyric_missing                      | Line ${0} - Lyrics missing.                                                                                                                                    |
| ?      | audio_missing                      | Line ${0} - Audio file missing.                                                                                                                                |
| ?      | audio_changeable                   | Line ${0} - Audio to be changed.                                                                                                                               |
| ?      | custom                             | Line ${0} - \${1}                                                                                                                                              |

When using `~invoke`, the error level need not match with the default one. These without a default error level can only be triggered with `~invoke`.
