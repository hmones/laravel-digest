<h1 align="center">Laravel Digest</h1>

<p align="center">
<a href="https://github.com/hmones/laravel-digest/actions"><img src="https://github.com/hmones/laravel-digest/actions/workflows/build.yml/badge.svg" alt="Build Status"></a>
<a href="https://github.styleci.io/repos/450457021"><img src="https://github.styleci.io/repos/450457021/shield" alt="Style CI"></a>
<a href="https://packagist.org/packages/hmones/laravel-digest"><img src="http://poser.pugx.org/hmones/laravel-digest/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/hmones/laravel-digest"><img src="https://img.shields.io/github/v/release/hmones/laravel-digest" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/hmones/laravel-digest"><img src="http://poser.pugx.org/hmones/laravel-digest/license" alt="License"></a>
</p>

A simple package to create and send digest emails every certain period or when the amount reaches a certain threshold.
Usage Examples:
- Sending a digest email to website administrator every new 100 registrations on the website.
- Sending a daily email with logged error messages on the website.
- Sending users a monthly newsletter that contains all posts issued in the month.

## Installation

Via Composer

```bash
composer require hmones/laravel-digest
```

## Configuration

To publish the package configuration

```bash
php artisan vendor:publish --tag=laravel-digest-config
 ```

The configuration file contains the following parameters:
- `method`: the method that you would like to use to send emails, it takes two values, `queue` or `send`
  - Env variable: `DIGEST_METHOD`
  - Default value: `queue`
- `frequency.enabled` whether you would like to enable sending emails every certain period, if not enabled emails will not be scheduled
  - Env variable: `DIGEST_FREQUENCY_ENABLED`
  - Default value: `true`
- `frequency.daily.time` if frequency is enabled this parameter is used as the time to send the daily digest emails
  - Env variable: `DIGEST_DAILY_TIME`
  - Default value: `00:00`
- `frequency.weekly.time` if frequency is enabled this parameter is used as the time to send the weekly digest emails
  - Env variable: `DIGEST_WEEKLY_TIME`
  - Default value: `00:00`
- `frequency.monthly.time` if frequency is enabled this parameter is used as the time to send the monthly digest emails
  - Env variable: `DIGEST_MONTHLY_TIME`
  - Default value: `00:00`
- `frequency.weekly.day` if frequency is enabled this parameter is used as the day to send the weekly digest emails (1 is Sunday and 7 is Saturday)
  - Env variable: `DIGEST_WEEKLY_DAY`
  - Default value: `1`
- `frequency.monthly.day` if frequency is enabled this parameter is used as the day to send the monthly digest emails
  - Env variable: `DIGEST_MONTHLY_DAY`
  - Default value: `1`
- `frequency.custom` you can set as much custom frequency definitions as you want and the parameter takes a valid cron expression
- `amount.enabled` whether you would like to enable sending emails every certain amount per batch
  - Env variable: `DIGEST_AMOUNT_ENABLED`
  - Default value: `true`
- `amount.threshold` the number of emails after which an digest email should be sent.
  - Env variable: `DIGEST_AMOUNT_THRESHOLD`
  - Default value: `10`

## Usage

To create an email digest, make sure you have the following first:
- A mailable, configured with the sending addresses and email views and subject.
- The mailable should accept an array variable in its constructor, this array variable will contain all the records of data passed to individual emails concatenated and sent automatically by the package to the mailable to compile the views for sending the digest.

Example: Sending a digest email every time 10 new users register on the website with a summary of their names.

1. Adjust the configuration variable in `config\laravel-digest.php` by setting `amount.enabled => true` and `frequency.enabled => false`
2. Create the following mailable
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): Mailable
    {
        return $this->view('userCreated')->subject('10 new users are registered')->to('email@test.com');
    }
}
```
3. Create a view to render the names of the users `resources\userCreated.blade.php`
```html
<html>
<head><title>Sample Email</title></head>
<body>
<h1>The following users have just registered:</h1>
<ol>
  @foreach($data as $record)
  <li>{{$record['name']}}</li>
  @endforeach
</ol>
</body>
</html>
```
4. Create an observer for user creation and add an record to the digest every time a user is created:

```php
<?php

namespace App\Observers;

use App\Mail\UserCreatedMailable;use App\Models\User;use Hmones\LaravelDigest\Facades\Digest;

class UserObserver
{
    public function created(User $user)
    {
        $batchId = 'userCreated';
        $mailable = UserCreatedMailable::class;
        //Data can also be set to null if you don't want to attach any data to the email
        $data = ['name' => $user->name];
        //Frequency can take values such as daily, weekly, monthly, custom or an integer threshold 10, 20 ...etc 
        $frequency = 10;
        
        Digest::add($batchId, $mailable, $data, $frequency);
    }
}
```
5. The package will take care of everything else and send emails once the number of registered users reach 10.


## Change log

Please see the [changelog](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [contributing.md](CONTRIBUTING.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits
- [Haytham Mones][link-author]

## License

Please see the [license file](LICENSE.md) for more information.

[link-author]: https://github.com/hmones
