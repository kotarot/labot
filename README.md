# Labot

* [google-api-php-client](https://github.com/google/google-api-php-client) is required.

## crontab

```
# Slack and Hackey
0 8 * * *    /path/to/labot/slack_post_daily.sh
*/10 * * * * /path/to/labot/hackey_minutely.sh > /dev/null 2>&1

# Mastodon
0 8 * * *   php /path/to/labot/mastodon_post_yahooweather.php; php /path/to/labot/mastodon_post_zemi.php today
0 10 * * *  php /path/to/labot/mastodon_post_hitokoto.php coffee
0 12 * * *  php /path/to/labot/mastodon_post_hitokoto.php lunch
0 14 * * *  php /path/to/labot/mastodon_post_hitokoto.php nap
0 15 * * *  php /path/to/labot/mastodon_post_hitokoto.php oyatsu
0 17 * * *  php /path/to/labot/mastodon_post_hitokoto.php beer
0 21 * * *  php /path/to/labot/mastodon_post_zemi.php tomorrow
0 0 * * *   php /path/to/labot/mastodon_post_hitokoto.php gotobed
*/1 * * * * php /path/to/labot/mastodon_post_train.php

*/1 * * * * /path/to/labot/mastodon_streaming.sh
```
