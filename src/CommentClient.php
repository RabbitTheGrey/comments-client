<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function array_key_exists;
use function in_array;

final class CommentClient implements CommentClientInterface
{
    private const HTTP_HOST = 'http://example.com';

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function getComments(): array
    {
        $response = $this->execute('GET', '/comments');

        $comments = [];
        foreach ($response as $comment) {
            $comments[] = new Comment($comment);
        }

        return $comments;
    }

    public function createComment(string $name, string $text): Comment
    {
        $response = $this->execute('POST', '/comment', [
            'name' => $name,
            'text' => $text,
        ]);

        return new Comment($response);
    }

    public function updateComment(int $id, array $changeset): Comment
    {
        $properties = get_class_vars(Comment::class);

        foreach ($changeset as $key => $value) {
            if (!array_key_exists($key, $properties) || $changeset[$key] === 'id') {
                unset($changeset[$key]);
            }
        }

        if (empty($changeset)) {
            throw new CommentClientException('empty changeset.');
        }

        $response = $this->execute('PUT', "/comment/{$id}", $changeset);

        return new Comment($response);
    }

    private function execute(string $method, string $endpoint, array $params = []): array
    {
        try {
            $request = $this->requestFactory
                ->createRequest($method, self::HTTP_HOST . $endpoint)
                ->withHeader('Content-Type', 'application/json')
            ;

            if (in_array($method, ['PUT', 'POST'], true)) {
                $stream = $this->streamFactory->createStream(json_encode($params));
                $request = $request->withBody($stream);
            }

            $response = $this->httpClient->sendRequest($request);
            $body = $response->getBody()->getContents();

            if ($response->getStatusCode() >= 400) {
                throw new CommentClientException($body);
            }

            return json_decode($body, true);

        } catch (ClientExceptionInterface $e) {
            throw new CommentClientException($e->getMessage(), $e->getCode());
        }
    }
}
