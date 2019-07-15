# AvatarPay

Процессинговая система Аватар

https://api.processing.i-am-avatar.com

## Конфигурация

В раздел 'components' файла main.php помести конструкцию:
 
```
[
    'components' => [
    // ...
        'AvatarPay' => [
            'class'     => '\iAvatar777\avatarPay\AvatarPay',
            'key'       => '...',
            'secret'    => '...',
        ]    
    // ...
    ]
];
``` 

## Использование

Для вызова функции используй конструкцию:

```
/** @var \iAvatar777\avatarPay\AvatarPay  $provider */
$provider = Yii::$app->AvatarPay;
$provider->call(...);
```