# Result

Simple class for handling failures, input errors

```php
use Xtompie\Result\Result;

$rand = rand(0, 1);
$result = $rand%2 == 0 ? Result::ofSuccess($rand) : Result::ofErrorMsg("the number $rand is not even");
if ($result->success()) {
    echo "OK: {$result->value()}";
}
else if ($result->fail()) {
    echo "Error: {$result->errors()->first()->message()}";
}
```

## Requiments

PHP >= 8.0

## Installation

Using [composer](https://getcomposer.org/)

```
composer require xtompie/result
```

## Docs

Result is in state success or fail.
Success optionaly contains value.
Fail optionaly contains errors.
Error can have message, key and space.
Message is for human readable text.
Key is for error idetify for programs.
Space is for idetify property or path of error.

### Creation

```php
Result::ofSuccess(); // success without value
Result::ofValue(mixed $value); // success with value
Result::ofFail(); // fail without errors
Result::ofError(Error $error); // fail with one error
Result::ofErrorMsg(?string $message, ?string $key = null, ?string $space = null); // fail with one error
Result::ofErrors(ErrorCollection $errors); // fail with errors
Result::ofCombine(Result ...$results):
// combined many results, fail when any of results fail
// when fail errors are merged
// when success, first value is used
```

## Usage example


```php
namespace App\User\Application\Service\CreateUser;

use Xtompie\Result\Result;

class CreateUserResult extends Result
{
    public static function ofUserId(strign $userId): static
    {
        return parent::ofValue($userId);
    }

    public function userId(): string
    {
        return $this->value();
    }
}

class CreateUserService
{
    public function __invoke(string $email): Result
    {
        if (strlen($email) === 0) {
            return Result::ofErrorMsg('Email required', 'required', 'email');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Result::ofErrorMsg('Invalid email', 'email', 'email');
        }

        if ($this->dao->exists('user', ['email' => $email])) {
            return Result::ofErrorMsg('Email exists', 'exists', 'email');
        }

        $id = $this->dao->insert('user', ['email' => $email]);

        return Result::ofUserId($id);
    }
}

namespace App\User\UI\Controller;

use App\User\Application\Service\CreateUser\CreateUserService;
use App\User\UI\Request\Request;

class ApiUsersPostController
{
    public function __construct(
        protected CreateUserService $createUserService,
        protected Request $request,
    ) {}

    public function __invoke()
    {
        $result = ($this->createUserService)((string)$this->request->input('email'));
        if ($result->fail()) {
            return [
                'success' => false,
                'error' => [
                    'msg' => $result->errors()->first()?->message(),
                    'key' => $result->errors()->first()?->key(),
                    'space' => $result->errors()->first()?->space(),
                ],
            ];
        }

        return [
            'success' => true,
            'body' => [
                'id' => $result->userId(),
            ],
        ];
    }
}

```
