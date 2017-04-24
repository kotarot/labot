# Labot

* [google-api-php-client](https://github.com/google/google-api-php-client) is required.

## crontab

```
# Slack and Hackey
0 8 * * *    /path/to/labot/slack_post_daily.sh
*/10 * * * * /path/to/labot/hackey_minutely.sh > /dev/null 2>&1
```
