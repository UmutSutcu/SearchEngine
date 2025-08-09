<?php
namespace App\Service;

use App\Entity\ContentItem;
use App\Enum\ContentType;

final class ScoreCalculator
{
    public function compute(ContentItem $c, \DateTimeImmutable $now = new \DateTimeImmutable()): void
    {
        // Base
        if ($c->getType() === ContentType::VIDEO) {
            $views = max(0, (int)$c->getViews());
            $likes = max(0, (int)$c->getLikes());
            $base = ($views / 1000.0) + ($likes / 100.0);
        } else {
            $rt = max(1, (int)$c->getReadingTime());
            $re = max(0, (int)$c->getReactions());
            $base = $rt + ($re / 50.0);
        }

        // Weight
        $w = $c->getType() === ContentType::VIDEO ? 1.5 : 1.0;

        // Freshness
        $days = $c->getPublishedAt()->diff($now)->days;
        $fresh = 0.0;
        if ($days <= 7)       $fresh = 5.0;
        elseif ($days <= 30)  $fresh = 3.0;
        elseif ($days <= 90)  $fresh = 1.0;

        // Interaction
        if ($c->getType() === ContentType::VIDEO) {
            $v = max(1, (int)$c->getViews());
            $interaction = (max(0, (int)$c->getLikes()) / $v) * 10.0;
        } else {
            $rt = max(1, (int)$c->getReadingTime());
            $interaction = (max(0, (int)$c->getReactions()) / $rt) * 5.0;
        }

        $final = ($base * $w) + $fresh + $interaction;

        $c->setBaseScore($base);
        $c->setFreshnessScore($fresh);
        $c->setInteractionScore($interaction);
        $c->setFinalScore($final);
    }

    /** arama için basit relevance çarpanı */
    public function relevanceBoost(ContentItem $c, string $q): float
    {
        $q = mb_strtolower(trim($q));
        if ($q === '') return 1.0;
        $hay = mb_strtolower($c->getTitle().' '.implode(' ', $c->getTags() ?? []));
        $matches = 0;
        foreach (preg_split('/\s+/', $q) as $tok) {
            if ($tok !== '' && str_contains($hay, $tok)) $matches++;
        }
        return 1.0 + min(0.5, $matches * 0.1);
    }
}
