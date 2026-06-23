<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClient;

/**
 * @class 
 */
interface CommentClientInterface
{
    /**
     * Возаращает список комментариев
     * 
     * @return Comment[]
     * @throws CommentClientException
     */
    public function getComments(): array;

    /**
     * Добавить комментарий
     * 
     * @param string $name
     * @param string $text
     * 
     * @return Comment
     * @throws CommentClientException
     */
    public function createComment(string $name, string $text): Comment;

    /**
     * Обновляет поля по идентификатору комментария
     * 
     * @param int $id
     * @param array $changeset
     * 
     * @return Comment
     * @throws CommentClientException
     */
    public function updateComment(int $id, array $changeset): Comment;
}
