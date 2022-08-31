`txmp.docs / documentation`

# Uploading and managing songs

## Creating, renaming and deleting

Songs are stored under `data/music/` and can be managed manually.

For UI management, go to the Song Management page. To do this, click the key icon on the header bar and select 'Manage Songs'.

At the bottom of page, you can see this form.

```plain
+------------------------------------+----------+
| New ID                             | Create   |
+------------------+-----------------+------------+
+------------------+-------------------+----------+
| Old ID           | New ID            | Rename   |
+------------------+-------------------+----------+
```

Song ID can only contain alpha, numbers and `_`.

### Creating

Input ID of the new song, and click Create.

### Changing ID

Input the old ID, then the new ID, and click Rename.

### Deleting

Simply by renaming an ID to `DELETE`. You will see a different dialog when clicking Rename.

## Managing access

Click on any song on the admin list, and you will see the menu.

```plain
+------------------------+
| Edit                   |
+------------------------+
| Manage Resources       |
+------------------------+
| View                   |
+------------------------+
| Generate Doc           |
+------------------------+
| View Debug Code        |
+------------------------+
| Download Music         |
+------------------------+
| LPCKD-XW-              |
+------------------------+
```

Click the last line, then you will enter the permission page.

Unfold `▶ Tips` to see how it works.

## Uploading a song

First create a song by ID. You will be taken into the resource manager page.

### Uploading resources

Download the audio you want using some techniques. Also get the cover image.

Upload them on the resource manager page.

After confirming them, go to Editor page.

### Editing

You should first have a look at this:

- [Introducing DeltaDesc V201805](./deltadesc201805.md)

There will be a file prepared for you. You do like this.

- Fill up `[Info]` section.
- Submit your changes.
- Paste lyrics right after the `[Info]` section.
- Run `Basic > Trim Redundant Lines`.
- Add paragraph section headings.
- Add `[Split]`.
- Add breaks using `Snippet > Interval`.
- Run `Basic > Add 'L' Heading`.
- Submit your changes.
- Play the music in preview.
- Select the first `__LT__`.
- Click `Timing` to make the button orange.
- Hit space or enter to insert current timestamp, and `/` for a null timestamp.
- Save your changes.

### Managing access

Go to Permissions page and set access.
