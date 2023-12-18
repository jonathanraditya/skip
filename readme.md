# Skip Link Shortener

A simple link shortener engine that takes user URL input (usually a long one) and create a new URL redirection rule with a random-short value for better readability and shareability.

## Working principles:
Long URL (https://drive.google.com/aof12324hauidhaisdnbia) -> Short URL (https://link.st/projecturl)

### Short URL structures (from the above example):
`https://USERDOMAIN/RANDOMCHARS`
`USERDOMAIN: link.st`
`RANDOMCHARS: projecturl`

## Additional functionalities
Over the base functionality, I've developed a few functions and features to increase the user experience, such as:
1. User input validation (make sure it's a valid URL).
2. User history retention that assign a random cookie to the current browser.
3. Transferable history retention, to share the current history to the other browser/devices.
4. The ability to change the `RANDOMCHARS` to the character of choice (this command is executed if there is no conflict with the existing rule).

## Setting things up
### The environment variables
```php
$user = "DB_ACCESS_USERNAME";
$password = "DB_ACCESS_USERPASSWORD";
$db = "DB_NAME";
```


