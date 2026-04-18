<?php

namespace App\Domain\Service;

use App\Domain\Entity\Note;

interface AiSummaryService
{
    /**
     * @param Note[] $notes non-archived notes to summarize
     */
    public function summarize(array $notes): string;
}