<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClient;

final class Comment
{
    public int $id;
    public string $name;
    public string $text;

    public function __construct(array $props)
    {
        foreach ($props as $key => $value) {
            $this->$key = $value;
        }
    }
}
