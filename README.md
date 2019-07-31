# Self Signed Token

A simple self-signed token based on a secret string and expiration time. Expiration time and signature are saved as part of the token. The token is easy to check for token expiration time. It is also easy to verify its authenticity by signature.

```php
$signedToken = new SelfSignedToken();
$token = $signedToken->create(); // that's all
```

I use this token to simply check its expiration from the time it was created. And signature based authenticity. All this without queries to the database. Without saving the token itself somewhere else. You can check the token on Web Server level(by Nginx). For example, using the `ngx_http_secure_link_module`

I save the Token ID in the database, and the time of its creation. But I do not query the database if the token is not valid or expired. This is convenient for preventing flooding with requests, and as a simple but reliable protection against DDoS, is relatively.

## Token

`6e000eeabea27aa13a0476d656e5a15e.1560148598.70272e700a46b3040ee53f2b083e3875`

* Token ID - 6e000eeabea27aa13a0476d656e5a15e
* Expiration time - 1560148598 (Unix Timestamp)
* Signature - 70272e700a46b3040ee53f2b083e3875

### Token ID

MD5 Hash randomly generated string. Very random, but not for any cryptographic protection. Just a random string. You can pass the prefix if you think it is not random enough for you. You can pass any string as `id`.

```php
$id = $signedToken->generateId();
// OR
$id = $signedToken->generateId($prefix);

$token = $signedToken->create($id);
$token = $signedToken->create('anyrandomstring');
```

### Expiration time

The time when the token was expired(Unix Timestamp). `$ttl` - Token lifetime in seconds from creation. Default 60 seconds. You can set the time to your taste. 

```php
$signedToken = new SelfSignedToken();
// OR
$signedToken = new SelfSignedToken($ttl);
// OR
$signedToken->setTtl($time);

$token = $signedToken->create();
$created = $signedToken->created(); // get creation time
// OR set any time
$token = $signedToken->create($id, $created);
$token = $signedToken->create(null, $created);
```

### Signature

Simple MD5 Hash string. Generated based on Token ID, Expiration time and Secret phrase. It may be more convenient to use the environment variable. Now it defaults to 'APP_SECRET'

```php
$signedToken = new SelfSignedToken($ttl);
// OR 
$signedToken = new SelfSignedToken($ttl, $secret);
$token = $signedToken->create();
```

#### Bypass
 
`setBypass(true)` - ignore Signature and Expiration time

## Validate

`$ignoreSign` and `$ignoreExpires` boolean

```php
$signedToken->valid($token);
// OR
$signedToken->valid($token, $ignoreSign);
// OR
$signedToken->valid($token, $ignoreSign, $ignoreExpires);
```
