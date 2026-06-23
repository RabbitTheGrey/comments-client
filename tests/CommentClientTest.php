<?php

declare(strict_types=1);

namespace RabbitTheGrey\CommentsClientTest;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RabbitTheGrey\CommentsClient\Comment;
use RabbitTheGrey\CommentsClient\CommentClient;
use RabbitTheGrey\CommentsClient\CommentClientException;

class CommentClientTest extends TestCase
{
    private const HTTP_HOST = 'http://example.com';

    /**
     * @var ClientInterface
     */
    private MockObject $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private MockObject $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private MockObject $streamFactory;
    
    /**
     * @var RequestInterface
     */
    private MockObject $mockRequest;

    /**
     * @var ResponseInterface
     */
    private MockObject $mockResponse;

    /**
     * @var StreamInterface
     */
    private MockObject $mockStream;

    /**
     * @var StreamInterface
     */
    private MockObject $mockStreamBody;
    
    private CommentClient $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        
        $this->mockRequest = $this->createMock(RequestInterface::class);
        $this->mockResponse = $this->createMock(ResponseInterface::class);
        $this->mockStream = $this->createMock(StreamInterface::class);
        $this->mockStreamBody = $this->createMock(StreamInterface::class);

        $this->client = new CommentClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );
    }

    #[DataProviderExternal(CommentClientDataProvider::class, 'comments')]
    public function testGetCommentsSuccess(array $responseData): void
    {
        $endpoint = '/comments';

        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('GET', self::HTTP_HOST . $endpoint)
            ->willReturn($this->mockRequest);

        $this->mockRequest->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturn($this->mockRequest);

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($this->mockRequest)
            ->willReturn($this->mockResponse);

        $this->mockResponse->method('getStatusCode')->willReturn(200);
        $this->mockResponse->method('getBody')->willReturn($this->mockStream);
        $this->mockStream->method('getContents')->willReturn(json_encode($responseData));

        $result = $this->client->getComments();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Comment::class, $result);
    }

    #[DataProviderExternal(CommentClientDataProvider::class, 'comment')]
    public function testCreateCommentSuccess(array $payloadData, array $responseData): void
    {
        $endpoint = '/comment';

        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', self::HTTP_HOST . $endpoint)
            ->willReturn($this->mockRequest);

        $this->mockRequest->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturn($this->mockRequest);

        $this->streamFactory->expects($this->once())
            ->method('createStream')
            ->with(json_encode($payloadData))
            ->willReturn($this->mockStreamBody);

        $this->mockRequest->expects($this->once())
            ->method('withBody')
            ->with($this->mockStreamBody)
            ->willReturn($this->mockRequest);

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->mockResponse);

        $this->mockResponse->method('getStatusCode')->willReturn(201);
        $this->mockResponse->method('getBody')->willReturn($this->mockStream);
        $this->mockStream->method('getContents')->willReturn(json_encode($responseData));

        $comment = $this->client->createComment($payloadData['name'], $payloadData['text']);
        
        $this->assertInstanceOf(Comment::class, $comment);
    }

    #[DataProviderExternal(CommentClientDataProvider::class, 'updatedComment')]
    public function testUpdateCommentSuccess(array $payloadData, array $responseData): void
    {
        $endpoint = '/comment/1';

        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('PUT', self::HTTP_HOST . $endpoint)
            ->willReturn($this->mockRequest);

        $this->mockRequest->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json')
            ->willReturn($this->mockRequest);

            $this->streamFactory->expects($this->once())
            ->method('createStream')
            ->with(json_encode($payloadData))
            ->willReturn($this->mockStreamBody);

        $this->mockRequest->expects($this->once())
            ->method('withBody')
            ->with($this->mockStreamBody)
            ->willReturn($this->mockRequest);

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->mockResponse);

        $this->mockResponse->method('getStatusCode')->willReturn(200);
        $this->mockResponse->method('getBody')->willReturn($this->mockStream);
        $this->mockStream->method('getContents')->willReturn(json_encode($responseData));

        $comment = $this->client->updateComment(1, ['text' => 'Updated text', 'invalid_key' => 'some data']);

        $this->assertInstanceOf(Comment::class, $comment);
    }

    public function testUpdateEmptyChangeset(): void
    {
        try {
            $this->client->updateComment(1, ['non_existent_property' => 'value']);
            $this->fail('no exception.');
        } catch (CommentClientException $e) {
            $this->assertSame('empty changeset.', $e->getMessage());
        }
    }

    #[DataProviderExternal(CommentClientDataProvider::class, 'httpError')]
    public function testHttpErrorStatus(array $errorResponseBodyData): void
    {
        $this->requestFactory->method('createRequest')->willReturn($this->mockRequest);
        $this->mockRequest->method('withHeader')->willReturn($this->mockRequest);
        $this->httpClient->method('sendRequest')->willReturn($this->mockResponse);

        $this->mockResponse->method('getStatusCode')->willReturn(500);
        $this->mockResponse->method('getBody')->willReturn($this->mockStream);
        
        $expected = json_encode($errorResponseBodyData);
        $this->mockStream->method('getContents')->willReturn($expected);

        try {
            $this->client->getComments();
            $this->fail('no exception.');
        } catch (CommentClientException $e) {
            $this->assertSame($expected, $e->getMessage());
        }
    }
}
