### Подключение ###
`composer require rabbitthegrey/comments-client`

### Использование ###
Возаращает список комментариев
```php
$client->getComments();
```

Добавить комментарий
```php
$newComment = $client->createComment('Ilya', 'comment');
```

```php
$updatedComment = $client->updateComment(1, ['name' => 'ne Ilya', 'text' => 'updated comment']);
```
