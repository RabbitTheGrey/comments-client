<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClientTest;

final class CommentClientDataProvider
{
    public static function comments(): array
    {
        return [
            'comments list' => [
                [['id' => 1, 'name' => 'Ilya', 'text' => 'comment']]
            ]
        ];
    }

    public static function comment(): array
    {
        return [
            'new comment data' => [
                'payloadData'  => ['name' => 'Ilya', 'text' => 'comment'],
                'responseData' => ['id' => 2, 'name' => 'Ilya', 'text' => 'comment']
            ]
        ];
    }

    public static function updatedComment(): array
    {
        return [
            'filtered changes' => [
                'payloadData'  => ['text' => 'Updated text'],
                'responseData' => ['id' => 1, 'text' => 'Updated text']
            ]
        ];
    }

    public static function httpError(): array
    {
        return [
            '500 server error' => [
                ['error' => 'internal server error']
            ]
        ];
    }
}
