<?php

namespace App\Infrastructure\Ai;

use App\Domain\Entity\Note;
use App\Domain\Service\AiSummaryService;
use OpenAI;
use OpenAI\Contracts\ClientContract;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class OpenAiSummaryService implements AiSummaryService
{
    private const string SYSTEM_PROMPT = 'You summarize a user\'s personal notes. Produce a concise 3-5 sentence summary. Do not invent facts.';

    private ClientContract $client;

    public function __construct(
        #[Autowire('%env(OPENAI_API_KEY)%')]
        string                  $apiKey,
        #[Autowire('%env(OPENAI_MODEL)%')]
        private readonly string $model,
        ?ClientContract         $client = null,
    )
    {
        $this->client = $client ?? OpenAI::client($apiKey);
    }

    public function summarize(array $notes): string
    {
        $body = implode("\n", array_map(
            static fn(Note $n) => '- ' . (string)$n->content(),
            $notes,
        ));

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user', 'content' => $body],
            ],
        ]);

        return (string)($response->choices[0]->message->content ?? '');
    }
}