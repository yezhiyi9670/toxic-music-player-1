Remoteplay Cache Lifecycle
==========================

Lyrics and metadata of Remoteplay songs aren't fetched each time they're needed. They're cached for some time to ensure performance and also, preserve songs which are deactivaed from their sourcs for a extended period of time.

The complete lifecycle will be introduced in v127b.

Parameters
----------

`rp_fetch_retry_times=4`: Retry times of metadata fetch.

`rp_fetch_retry_delay=0.1`: Retry delay of metadata fetch.

`rp_accept_user_check`: Whether to accept the user's request of refetching.

`rp_cache_living=93days`: The expected length of the Living Period.

`rp_cache_living_checkrate=0.01`: The possibility of random checks during the Living Period, using AJAX.

`rp_cache_ed=62days`: The max period of the Endangered Period.

`rp_cache_ed_checkrate=[0.05,0.45]`: Linear easing of the possibility of random checks during the Endangered Period, using AJAX.

Fetch Result
------------

### Network Request

A network request can end up with three types of results.

- SUCCESS: Acquired proper data.
- FAIL: The source **indicated that the target data does not exist or cannot be accessed**.
- ERROR: Cannot access to the source.

### Fetch

A fetch comes with a target URL.

First a network request is performed. If the result is ERROR, then a retrial is performed after `retry_delay`, for at most `retry_times` times. Otherwise the fetch returns.

Lifecycle Periods
-----------------

### Creation

A Remoteplay cache is established on the first successful attemp of fetching a song.

The cache file contains the timestamp of the creation.

### Living Period

Living Period is a time when a Remoteplay song function properly and can stay up-to-date.

#### Random Check

Random check occurs when the player switches into the Remoteplay song, at a rate of `living_checkrate`.

#### User Check

A user might trigger a user check on the Debug Code Page if allowed.

#### Expiration Check

Expiration check occurs when the length of the Living Period reaches `living`.

### Renewal

Renewal happens on one of the checks above.

On renewal, the crawler tries to fetch the metadata again. If the result is SUCCESS, then a new Living Period will begin. If the result is FAIL, then the cache goes into Endangered Period. The starting timestamp of the Endangered Period is recorded. Upon this, the crawler will also try to fetch the audio info to determine whether the audio should be marked as missing or not.

### Endangered Period

Endangered Period is a extension of a ordinary lifecycle.

Each audio/out request can determine whether the audio should be marked as missing or not.

Songs during this period will be significantly marked, reminding the user to preserve a copy of it in case it disappears permanently.

### Status Check

This happens on user check or random check during Endangered Period.

The crawler tries to fetch the metadata again. If result is SUCCESS, then a new Living Period will begin, with cache updated. If result is FAIL, then nothing will be done.

### Final Judgement

This hapeens on expiration check during Endangered Period.

The crawler tries to fetch the metadata again. If result is SUCCESS, then a new Living Period will begin, with cache updated. If result is FAIL, the cache will be destroyed.

Legacy Simulation
-----------------

For internally stored songs, the state of being endangered can be marked manually.
