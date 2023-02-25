`txmp.docs / documentation`

# URL routing cheatsheet

All available URLs in the system. If you want to write a crawler, you may need this.

## Flags

| Flag | Comment                               |
| ---- | ------------------------------------- |
| +    | POST                                  |
| R    | Does not make changes to server-side. |
| L    | Require login.                        |
| S    | Restricted.                           |
| A    | Require administrator.                |

## Global parameters

| Param    | Values                   | Comment                                   |
| -------- | ------------------------ | ----------------------------------------- |
| wap      | `force-phone` `force-pc` | Simulate a device type                    |
| iframe   | -                        | iFramed page. Not all pages support this. |
| isSubmit | ...                      | POST submit data                          |

## Top level

| Flag | URL                | Param | Comment                             |
| ---- | ------------------ | ----- | ----------------------------------- |
| R    | `/`                | -     | Homepage                            |
| +R   | `/`                | ...   | RemotePlay or ID serach             |
|      | `/clean-garbage`   | -     | Garbage cleaner                     |
| R    | `/i18n-script`     | -     | Javascript containing language keys |
| R    | `/dynamic/<path>`  | -     | Colored CSS file                    |
| RL   | `/user`            | -     | User center                         |
| L    | `/user/logout`     | -     | Logout                              |
| R    | `/user/login`      | -     | Register or login                   |
| +    | `/user/login`      | ...   | Register or login                   |
| RL   | `/user/passwd`     | -     | Change password                     |
| +L   | `/user/passwd`     | ...   | Change password                     |
| R    | `/setting`         | -     | User configuration                  |
| +R   | `/setting`         | ...   | Save user configuration             |
| R    | `/list-maker`      | -     | Temporary playlist editor           |
| R    | `/version-history` | -     | Version history                     |

## Administration

| Flag | URL                 | Param | Comment                     |
| ---- | ------------------- | ----- | --------------------------- |
| RA   | `/admin`            | -     | Song administration         |
| +A   | `/admin`            | ...   | Song administration actions |
| RA   | `/admin/query-comp` | -     | Query for compile info      |
| RA   | `/admin/query-ann`  | -     | Query for annotations       |
| RA   | `/admin/users`      | -     | User administration         |
| +A   | `/admin/users`      | ...   | User administration actions |

## Playlists

| Flag | URL                                               | Param                         | Comment                |
| ---- | ------------------------------------------------- | ----------------------------- | ---------------------- |
| RL   | `/list-maker/<list_id>`                           | -                             | Online playlist editor |
| +L   | `/playlist/save-list/<list_id>`                   | ...                           | Save online list       |
| RS   | `/playlist/<username>/<list_id>`                  | `[raw,[json,[include-meta]]]` | Online playlist player |
| RS   | `/playlist/gen-docs/`<br />`<username>/<list_id>` | -                             | Lyric book check-out   |

## Single Song

| Flag | URL                             | Param                                                    | Comment                  |
| ---- | ------------------------------- | -------------------------------------------------------- | ------------------------ |
| RS   | `/<song_id>`                    | -                                                        | Single song player       |
| RS   | `/<song_id>/code`               | `[raw,[lrc=minified/fancy,`<br />`[delta=#,comment=#]]]` | Debug code and LRC       |
| S    | `/<song_id>/refresh-cache`      | -                                                        | Refresh RemotePlay cache |
| RS   | `/<song_id>/comp_info`          | -                                                        | Compilation info         |
| RS   | `/<song_id>/docs`               | -                                                        | Single doc check-out     |
| +S   | `/<song_id>/make-doc`           | ...                                                      | Doc generate             |
| RS   | `/<song_id>/audio[.<ext>]`      | -                                                        | Main audio               |
| RS   | `/<song_id>/background[.<ext>]` | -                                                        | Accompanying audio       |
| RS   | `/<song_id>/download`           | -                                                        | Download main audio      |
| RS   | `/<song_id>/raw`                | -                                                        | Lyrics JSON              |
| RS   | `/<song_id>/html/...`           | -                                                        | HTML fragment            |
| RS   | `/<song_id>/meta`               | -                                                        | Essential URLs           |
| RS   | `/<song_id>/switch-all`         | -                                                        | Bundled data             |
| RS   | `/<song_id>/avatar[.<ext>]`     | -                                                        | Cover image              |
| RS   | `/<song_id>/edit`               | -                                                        | Admin edit               |
| +S   | `/<song_id>/edit`               | ...                                                      | Admin edit save          |
| RA   | `/<song_id>/resource`           | -                                                        | Admin resource           |
| +A   | `/<song_id>/resource`           | ...                                                      | Admin resource save      |
| RA   | `/<song_id>/permission`         | -                                                        | Admin permission         |
| +A   | `/<song_id>/permission`         | ...                                                      | Admin permission save    |
