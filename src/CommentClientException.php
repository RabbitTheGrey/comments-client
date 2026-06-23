<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClient;

class CommentClientException extends \Exception
{
    public function __construct(string $message, int $code = 400)
    {
        return parent::__construct($message, $code);
    }
}
