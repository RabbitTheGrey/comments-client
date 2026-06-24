### Подключение ###
`composer require rabbitthegrey/comments-client`

### Использование ###
```php
$client = new RabbitTheGrey\CommentsClient\CommentClient();
```

Возаращает список комментариев
```php
$client->getComments();
```

Добавить комментарий
```php
$newComment = $client->createComment('Ilya', 'comment');
```

Редактировать комментарий
```php
$updatedComment = $client->updateComment(1, ['name' => 'ne Ilya', 'text' => 'updated comment']);
```
