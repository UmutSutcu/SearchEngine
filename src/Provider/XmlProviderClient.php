<?php
namespace App\Provider;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class XmlProviderClient implements ProviderClientInterface
{
    public function __construct(
        private HttpClientInterface $http,
        private string $url,
    ) {}

    public function name(): string { return 'xml'; }

    public function fetch(): iterable
    {
        $r = $this->http->request('GET', $this->url);
        $xml = new \SimpleXMLElement($r->getContent());

        foreach ($xml->items->item as $it) {
            $type = (string)$it->type; // "video" | "article"
            $isVideo = $type === 'video';

            $cats = [];
            foreach ($it->categories->category as $c) { $cats[] = (string)$c; }

            yield [
                'external_id'   => (string)$it->id,
                'title'         => (string)$it->headline,
                'type'          => $isVideo ? 'video' : 'text',
                'tags'          => $cats,
                'views'         => $isVideo ? (int)$it->stats->views : null,
                'likes'         => $isVideo ? (int)$it->stats->likes : null,
                'duration'      => $isVideo ? (string)$it->stats->duration : null,
                'reading_time'  => $isVideo ? null : (int)$it->stats->reading_time,
                'reactions'     => $isVideo ? null : (int)$it->stats->reactions,
                'published_at'  => (string)$it->publication_date, // "YYYY-MM-DD"
                'raw'           => json_decode(json_encode($it), true),
            ];
        }
    }
}
