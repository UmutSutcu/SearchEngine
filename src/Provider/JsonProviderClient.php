<?php
namespace App\Provider;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JsonProviderClient implements ProviderClientInterface
{
    public function __construct(
        private HttpClientInterface $http,
        private string $url,
    ) {}

    public function name(): string { return 'json'; }

    public function fetch(): iterable
    {
        $r = $this->http->request('GET', $this->url);
        $data = $r->toArray();
        
        foreach ($data['contents'] ?? [] as $row) {
            $m = $row['metrics'] ?? [];
            $type = (string)$row['type']; // "video" | "article"
            $isVideo = $type === 'video';
            yield [
                'external_id'   => $row['id'],
                'title'         => $row['title'],
                'type'          => $isVideo ? 'video' : 'text',
                'tags'          => $row['tags'] ?? [],
                'views'         => $isVideo ? (int)$m['views'] : null,
                'likes'         => $isVideo ? (int)$m['likes'] : null,
                'duration'      => $isVideo ? (string)$m['duration'] : null,
                'reading_time'  => $isVideo ? null : (int)$m['reading_time'],
                'reactions'     => $isVideo ? null : (int)$m['reactions'],
                'published_at'  => $row['published_at'],
                'raw'           => $row,
            ];
        }
    }
}
